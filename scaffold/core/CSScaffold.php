<?php defined('BASEPATH') OR die('No direct access allowed.');

/**
 * CSScaffold
 *
 * Handles all of the inner workings of the framework and juicy goodness.
 * This is where the metaphorical cogs of the system reside. 
 *
 * @package default
 * @author Anthony Short
 **/
class CSScaffold {
	 
	/**
	 * Holds the array of plugin objects
	 *
	 * @var array
	 **/ 
	 public static $plugins;
	 
	 
	/**
	 * Holds the array of module objects
	 *
	 * @var array
	 **/ 
	 public static $modules;
	 
	 /**
	 * What plugins have been loaded (Just their name)
	 *
	 * @var array
	 **/ 
	 public static $loaded = array();
	 
	/**
	 * The browser of the user
	 *
	 * @var string
	 */
	public static $browser = "Other";
	
	/**
	 * The version of the browser
	 *
	 * @var string
	 */
	public static $version = null;
	 
	/**
	 * Sets the initial variables, checks if we need to process the css
	 * and then sends whichever file to the browser.
	 *
	 * @return void
	 * @author Anthony Short
	 **/
	public static function run($url_params) 
	{
		# Get rid of those pesky slashes
		$requested_file	= trim_slashes($url_params['request']);
		
		# If they've put a param in the url, consider it set to 'true'
		foreach($url_params as $key => $value)
		{
			if($value == "")
			{
				$url_params[$key] = true;
			}
		}
		
		$recache = (isset($url_params['recache'])) ? true : false;
		
		# Easy access to file/directory info
		# dirname = path to the directory containing the file
		# basename = name of the file
		# extension = extension of the file
		# filename = name of the file, minus the extension
		$request = pathinfo($requested_file);
		
		# Add our requested file var to the array
		$request['requested_file'] = $requested_file;
		
		# Full server path to the requested file
		$request['server_path'] = join_path(DOCROOT,$requested_file);
		
		# Path to the file, relative to the css directory		
		$request['relative_file'] = substr($requested_file, strlen(URLPATH));
		
		# Path to the directory containing the file, relative to the css directory		
		$request['relative_dir'] = pathinfo($request['relative_file'], PATHINFO_DIRNAME);		

		FB::log($request);
		
		# If the file doesn't exist
		if(!file_exists($request['server_path']))
		{
			stop("Can't seem to find your css file - " . $request['server_path'] . ". Check your paths in the config"); 
		}

		# or if it's not a css file
		elseif (!is_css($requested_file))
		{
			stop("Error: Request file isn't a css file");
		}
		
		# or if the requested file wasn't from the css directory
		elseif(substr($request['dirname'], 0, strlen(URLPATH)) != URLPATH)
		{
			stop('Error: The file wasn\'t requested from the css directory. Check your css path in your config, or the path to the css file you just requested');
		}
		
		elseif(isset($url_params['raw']))
		{
			self::output_raw($request['server_path']);
		}
		
		# Otherwise, we're all good. Let's get started!
		else
		{
			# Start the timer
			Benchmark::start("system");
	
			# Send it off to the config
			Config::set($request);
			Config::set($url_params);
						
			# Get the modified time of the CSS file
			Config::set('requested_mod_time', filemtime(Config::get('server_path')));
			
			# Load the plugins and flags
			self::$modules = self::load_addons(read_dir(BASEPATH . "/modules"));
			self::$plugins = self::load_addons(read_dir(BASEPATH . "/plugins"));

			# Prepare the cache, and tell it if we want to recache
			Cache::set($recache);
			
			# Parse the css
			self::parse_css();
			
			# Stop the timer...
			Benchmark::stop("system");
	
			# Send it to the browser
			self::output_css();
		}
	}

	/**
	 * Shows the raw CSS file
	 *
	 * @author Anthony Short
	 * @param $css
	 * @return null
	 */
	public function output_raw($css)
	{
		self::set_headers();
		echo(file_get_contents($css));
		exit;
	}
		
	/**
	 * Sets the headers
	 *
	 * @author Anthony Short
	 * @return null
	 */
	private function set_headers($last_mod = "")
	{
		header('Content-Type: text/css');
		header("Vary: User-Agent, Accept");
		
		if($last_mod != "")
		{
			header('Last-Modified: '. gmdate('D, d M Y H:i:s', $last_mod) .' GMT');
		}
	}
		
	/**
	 * Loads the Plugins
	 *
	 * @return boolean
	 * @author Anthony Short
	 **/
	private static function load_addons($folders)
	{			
		$plugins = array();
		
		foreach($folders as $plugin_folder)
		{
			$plugin_files = read_dir($plugin_folder);
			
			foreach($plugin_files as $plugin_file)
			{
				$library_path = join_path($plugin_folder, "libraries");
				
				# Include the libraries
				if($libraries = read_dir($library_path))
				{
					foreach($libraries as $library)
					{
						require_once($library);
					}
				}
				
				if(extension($plugin_file) == "php" && pathinfo($plugin_file, PATHINFO_FILENAME) != "config")
				{					
					require_once($plugin_file);
					
					$plugin_class = pathinfo($plugin_file, PATHINFO_FILENAME);
					
					$config = join_path($plugin_folder, 'config.php');
					
					if(file_exists($config))
					{
						Config::load($config, $plugin_class);
					}
										
					if(class_exists($plugin_class))
					{				
						# Initialize the plugin
						$plugins[$plugin_class] = new $plugin_class();
						
						# Set the member paths
						$plugins[$plugin_class]->set_paths($plugin_folder);
					}
					
					# Add the plugin to the loaded array
					self::$loaded[] = $plugin_class;
				}			
			}
		}
		
		return $plugins;
	}
	
	/**
	 * Parse the CSS
	 *
	 * @return string - The processes css file as a string
	 * @author Anthony Short
	 **/
	private static function parse_css()
	{						
		# If the cache is stale or doesn't exist
		if (Config::get('cached_mod_time') < Config::get('requested_mod_time'))
		{
			# Load the CSS file in the object
			CSS::load(file_get_contents(Config::get('server_path')));
			
			# Import CSS files
			Import::parse();
			
			# Import the mixins in the plugin folders
			Mixins::import_mixins();
														
			# Parse our css through the plugins
			foreach(self::$plugins as $plugin)
			{
				$plugin->import_process();
			}
			
			# Compress it before parsing
			CSS::compress(CSS::$css);

			# Parse the constants
			Constants::parse();

			foreach(self::$plugins as $plugin)
			{
				$plugin->pre_process();
			}
			
			# This HAS to happen AFTER they are set, but 
			# before they are used.
			Constants::replace();
			
			foreach(self::$plugins as $plugin)
			{
				$plugin->process();
			}
			
			# Parse the mixins
			Mixins::parse();
			
			foreach(self::$plugins as $plugin)
			{
				$plugin->post_process();
			}
			
			# Parse the expressions
			Expression::parse();
			
			# Parse the nested selectors
			NestedSelectors::parse();
			
			foreach(self::$plugins as $plugin)
			{
				$plugin->formatting_process();
			}
			
			if (Config::get('show_header') === TRUE)
			{		
				CSS::$css  = "/* Processed and cached by CSScaffold on ". gmdate('r') . "*/\n\n" . CSS::$css;
			}
			
			# Write the css file to the cache
			Cache::write(CSS::$css);
		} 
	}
		
	/**
	 * Output the CSS to the browser
	 *
	 * @return void
	 * @author Anthony Short
	 **/
	private function output_css()
	{		
		if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'], $_SERVER['SERVER_PROTOCOL']) && Config::get('cached_mod_time') <= strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']))
		{
			header("{$_SERVER['SERVER_PROTOCOL']} 304 Not Modified");
			exit;
		}
		else
		{			
			$css = file_get_contents(Cache::$cached_file);
			
			if (Config::get('debug') === true)
			{
				self::debug($css);
			}
			
			self::set_headers(Config::get('cached_mod_time'));
			echo $css;
			exit();
		}
	}
	
	/**
	* Spits out information about the processes to Firebug
	* Mainly for plugin development. Turn it on in the config
	*
	* @author Anthony Short
	* @param $css
	* @return null
	*/
	private static function debug($css)
	{
		FB::group('Output Information');
			FB::log("Filesize - " . $filesize = round(strlen($css) / 1024 , 2) . "kB");
			FB::log("Processed in ".Benchmark::get("system", "time")." seconds");
			FB::log("Rendered as ".User_agent::$browser." ".User_agent::$version);
			FB::log("With flags ".join(", ", array_keys(Cache::$flags)));
			FB::log("Memory usage " . readable_size(Benchmark::memory_usage()));
		FB::groupEnd();

		FB::group('Config');
			FB::log(Config::$configuration);
		FB::groupEnd();
		
		FB::group('Constants');
			FB::log(Constants::$constants);
		FB::groupEnd();
		
		FB::group('Mixins');
			FB::log(Mixins::$mixins);
		FB::groupEnd();
		
		FB::group('Plugins');
			FB::log(self::$plugins);
		FB::groupEnd();
	
		FB::group('Benchmark');
		foreach (self::$loaded as $key => $value)
		{
			FB::group($value);
				FB::log("Import(".Benchmark::get($value . '_import', "time")." secs)");
				FB::log("Pre-process(".Benchmark::get($value . '_preprocess', "time")." secs)");
				FB::log("Process(".Benchmark::get($value . '_process', "time")." secs)");
				FB::log("Post-process(".Benchmark::get($value . '_postprocess', "time")." secs)");
			FB::groupEnd();
		}
		FB::groupEnd();
	}
		 
}