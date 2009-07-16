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
	 * The file that was requested
	 *
	 * @var array
	 **/
	 public static $flags = array();
	 
	/**
	 * Holds the array of plugin objects
	 *
	 * @var array
	 **/ 
	 public static $plugins;
	 
	 /**
	 * What plugins have been loaded (Just their name)
	 *
	 * @var array
	 **/ 
	 public static $loaded = array();
	 
	/**
	 * Sets the initial variables, checks if we need to process the css
	 * and then sends whichever file to the browser.
	 *
	 * @return void
	 * @author Anthony Short
	 **/
	public static function run($requested_file, $recache = TRUE) 
	{
		$requested_file = trim_slashes($requested_file);
		
		# Easy access to file/directory info
		# dirname = path to the directory containing the file
		# basename = name of the file
		# extension = extension of the file
		# filename = name of the file, minus the extension
		$request = pathinfo($requested_file );
		
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
		
		# Otherwise, we're all good. Let's get started!
		else
		{
			# Start the timer
			Benchmark::start("system");
		
			# Parse the user agent
			User_agent::setup();
	
			# Send it off to the config
			Config::set($request);
			
			# Get the modified time of the CSS file
			Config::set('requested_mod_time', filemtime(Config::get('server_path')));
			
			# Load the plugins and flags
			self::load_plugins();
	
			# Send the flags to the cache and get it ready
			Cache::set(self::$flags, $recache);
			
			# Parse the css
			self::parse_css();
			
			# Stop the timer...
			Benchmark::stop("system");
	
			# Send it to the browser
			self::output_css();
		}
	}
	
	/**
	 * Loads the Plugins
	 *
	 * @return boolean
	 * @author Anthony Short
	 **/
	private static function load_plugins()
	{	
		# Load each of the plugins
		$plugin_files = read_dir(BASEPATH . "/plugins");
		$plugins = array();
		
		foreach($plugin_files as $plugin)
		{
			include($plugin);
			
			$plugin_class = pathinfo($plugin, PATHINFO_FILENAME);
									
			if ( class_exists($plugin_class) )
			{				
				# Initialize the plugin
				$plugins[$plugin_class] = new $plugin_class();
				
				# Set the flags
				self::$flags = array_merge(self::$flags, $plugins[$plugin_class]->flags);
				
				# Add the plugin to the loaded array
				self::$loaded[] = $plugin_class;
			}
		}
		
		self::$plugins = $plugins;
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
			# Load the CSS file
			$css = file_get_contents(Config::get('server_path'));
										
			# Parse our css through the plugins
			foreach(self::$plugins as $plugin)
			{
				Benchmark::start( get_class($plugin) ."_import" );
				$css = $plugin->import_process($css);
				Benchmark::stop( get_class($plugin) ."_import" );
			}
							
			foreach(self::$plugins as $plugin)
			{
				Benchmark::start( get_class($plugin) ."_preprocess" );
				$css = $plugin->pre_process($css);
				Benchmark::stop( get_class($plugin) ."_preprocess" );
			}
			
			# This HAS to happen AFTER they are set, but 
			# before they are used. Rather than creating another
			# process just for this, I'll do it manually.
			if (class_exists('Constants'))
			{
				$css = Constants::replace($css);
			}
			
			foreach(self::$plugins as $plugin)
			{
				Benchmark::start( get_class($plugin) ."_process" );
				$css = $plugin->process($css);
				Benchmark::stop( get_class($plugin) ."_process" );
			}
			
			foreach(self::$plugins as $plugin)
			{
				Benchmark::start( get_class($plugin) ."_postprocess" );
				$css = $plugin->post_process($css);
				Benchmark::stop( get_class($plugin) ."_postprocess" );
			}
			
			foreach(self::$plugins as $plugin)
			{
				Benchmark::start( get_class($plugin) ."_formatting" );
				$css = $plugin->formatting_process($css);
				Benchmark::stop( get_class($plugin) ."_formatting" );
			}
			
			# Write the css file to the cache
			Cache::write($css);
		} 
	}
		
	/**
	 * Output the CSS to the browser
	 *
	 * @return void
	 * @author Anthony Short
	 **/
	private static function output_css()
	{		
		if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'], $_SERVER['SERVER_PROTOCOL']) && Config::get('cached_mod_time') <= strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']))
		{
			header("{$_SERVER['SERVER_PROTOCOL']} 304 Not Modified");
			exit;
		}
		else
		{			
			$css = file_get_contents(Cache::$cached_file);
			
			if (Config::get('show_header') === TRUE)
			{		
				$css  = "/* Processed and cached by CSScaffold on ". gmdate('r') . "*/\n\n" . $css;
			}
			
			if (Config::get('debug') === true)
			{
				self::debug($css);
			}

			header('Content-Type: text/css');
			header("Vary: User-Agent, Accept");
			header('Last-Modified: '. gmdate('D, d M Y H:i:s', Config::get('cached_mod_time')) .' GMT');
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
			FB::log("With flags ".join(", ", array_keys(self::$flags)));
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