<?php defined('BASEPATH') OR die('No direct access allowed.');

/**
 * CSScaffold (aka, the controller)
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
	 public static $loaded;

	/**
	 * Sets the initial variables, checks if we need to process the css
	 * and then sends whichever file to the browser.
	 *
	 * @return void
	 * @author Anthony Short
	 **/
	public static function setup($requested_file, $recache = TRUE) 
	{
		// Setup the core
		Core::setup();
		
		// Start the timer
		Benchmark::start("system");
		
		// Determine some paths
		$requested_file_name 	= basename($requested_file);
		$requested_dir 			= preg_replace('#/[^/]*$#', '', $requested_file);
		$relative_file 			= trim_slashes(substr($requested_file, strlen(URLPATH)));
		$relative_dir 			= (strpos($relative_file, '/') === false) ? '' : preg_replace("/\/[^\/]*$/", '', $relative_file);

		// Set our config values
		Core::config_set(
			array(
				'requested_file', 
				'requested_file_name',
				'requested_dir',
				'relative_file',
				'relative_dir'
			),
			array(
				$requested_file,
				$requested_file_name,
				$requested_dir,
				$relative_file,
				$relative_dir
			)
		);
		
		// Load the plugins and flags
		self::load_plugins();
		
		// Get the modified time of the CSS file
		Core::config_set('requested_mod_time', filemtime(CSSPATH . Core::config('relative_file')));
		
		// Send the flags to the cache and get it ready
		Core::set_cache(self::$flags, $recache);
	}
				
	/**
	 * Loads the CSS
	 *
	 * @return string - The unprocessed css file as a string
	 * @author Anthony Short
	 **/
	private static function load_css()
	{		
		if (substr(Core::config('requested_file'), -4) != '.css')
		{
			error("Error: Request file isn't a css file");
			exit;
		}
		
		elseif( substr(Core::config('requested_file'), 0, strlen(URLPATH))  != URLPATH)
		{
			error("Error: The file wasn't requested from the css directory");
			exit;
		}
		
		elseif(!file_exists(CSSPATH . "/" . Core::config('relative_file')))
		{
			error("Error: The requested CSS file ". CSSPATH . "/" . Core::config('relative_file') . " doesn't exist");
			exit;
		}
		
		return file_get_contents(CSSPATH . Core::config('relative_file'));
	}
	
	/**
	 * Loads the Plugins
	 *
	 * @return boolean
	 * @author Anthony Short
	 **/
	public static function load_plugins()
	{	
		// Load each of the plugins
		foreach(read_dir(BASEPATH . "/plugins") as $plugin)
		{
			include($plugin);
			
			$pathinfo = pathinfo($plugin);
			
			$plugin_class = str_replace('plugin.','',$pathinfo['filename']);
									
			if ( class_exists($plugin_class) )
			{				
				// Initialize the plugin
				$plugins[$plugin_class] = new $plugin_class($flags);
				
				// Set the flags
				self::$flags = array_merge(self::$flags, $plugins[$plugin_class]->flags);
				
				// Update the config
				Core::config_set($plugin_class,$plugins[$plugin_class]->settings);
				
				// Add the plugin to the loaded array
				self::$loaded[] = $plugin_class;
				
				// Clean up		
				unset($settings, $flags);
			}
		}
		
		self::$plugins = $plugins;
		
		return TRUE;
	}
	
	/**
	 * Parse the CSS
	 *
	 * @return string - The processes css file as a string
	 * @author Anthony Short
	 **/
	public static function parse_css()
	{
		// If the cache is stale or doesn't exist
		if (Core::config('cached_mod_time') < Core::config('requested_mod_time'))
		{	
			// Load the CSS file
			$css = self::load_css();
			
			// Parse our css through the plugins
			foreach(self::$plugins as $plugin)
			{
				Benchmark::start( get_class($plugin) ."_import" );
				$css = $plugin->import($css);
				Benchmark::stop( get_class($plugin) ."_import" );
			}
			
			foreach(self::$plugins as $plugin)
			{
				Benchmark::start( get_class($plugin) ."_preprocess" );
				$css = $plugin->pre_process($css);
				Benchmark::stop( get_class($plugin) ."_preprocess" );
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

			// Write the css file to the cache
			Core::write_cache($css, time());
		} 
	}
		
	/**
	 * Output the CSS to the browser
	 *
	 * @return void
	 * @author Anthony Short
	 **/
	public static function output_css()
	{		
		// Stop the timer...
		Benchmark::stop("system");

		if 
		(
			isset($_SERVER['HTTP_IF_MODIFIED_SINCE'], $_SERVER['SERVER_PROTOCOL']) && 
			Core::config('cached_mod_time') <= strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'])
		)
		{
			header("{$_SERVER['SERVER_PROTOCOL']} 304 Not Modified");
			exit();
		}
		else
		{	
			// Get the css from the cache		
			$css = file_get_contents(Core::$cached_file);
			
			if (Core::config('show_header') === TRUE)
			{
				$css = self::generate_info($css);
			}

			header('Content-Type: text/css');
			header("Vary: User-Agent, Accept");
			header('Last-Modified: '. gmdate('D, d M Y H:i:s', Core::config('cached_mod_time')) .' GMT');
			echo $css;
			exit();
		}
	}
	
	/**
	* Generates the css information string to place at the top of the file. Super messy.
	*
	* @author Anthony Short
	* @param $css
	* @return string
	* @todo Clean this up
	*/
	private function generate_info($css)
	{
		$filesize = round(strlen($css) / 1024 , 2);
		
		$header  = "/* Processed and cached by CSScaffold on ".gmdate('r'). "\n";
		$header .= "\n\tCached filesize is " . $filesize . " kilobytes. ";
		$header	.= "\n\tProcessed in ".Benchmark::get("system", "time")." seconds.";
		$header .= "\n\tRendered as " . Core::user_agent('browser') . " ".  Core::user_agent('version');
		$header .= "\n\tWith flags " . join(", ", array_keys(self::$flags));
		$header .= "\n\tWith plugins\n\t\t ";
		foreach (self::$loaded as $key => $value)
		{
			$header .= "\n\t\t" . $value . "\n\t\t\t Import(".Benchmark::get($value . '_import', "time")." secs) \n\t\t\t Pre-process(".Benchmark::get($value . '_preprocess', "time")." secs) \n\t\t\t Process(".Benchmark::get($value . '_process', "time")." secs) \n\t\t\t Post-process(".Benchmark::get($value . '_postprocess', "time")." secs)\n"; 
		}
		$header .= "\n*/\n";
		
		$css = $header . $css;
		
		return $css;
	}
		 
} // end CSScaffold