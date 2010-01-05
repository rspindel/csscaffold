<?php

/**
 * CSScaffold
 *
 * CSScaffold is a CSS compiler and preprocessor that allows you to extend
 * the CSS language easily. You can add your own properities, rules and at-rules
 * and abstract the language as much as you want.
 *
 * Requires PHP 5.1.2
 * Tested on PHP 5.3.0
 *
 * @package CSScaffold
 * @author Anthony Short <anthonyshort@me.com>
 * @copyright 2009 Anthony Short. All rights reserved.
 * @license http://opensource.org/licenses/bsd-license.php  New BSD License
 * @link https://github.com/anthonyshort/csscaffold/master
 */

class Scaffold extends Scaffold_Utils
{
	const VERSION = '2.0.0';
	
	/**
	 * The configuration for Scaffold and all of it's modules.
	 * The config for Scaffold itself should just be inside the
	 * config array, and module configs should be inside an array
	 * with the key as the name of the module. 
	 *
	 * @var array
	 */
	public static $config;
	
	/**
	 * Include paths
	 *
	 * These are used for finding files on the system. Rather than
	 * using PHP's built-in include paths, we just store the paths
	 * in this array and use the find_file function to locate it.
	 *
	 * @var array
	 */
	private static $include_paths = array();
	
	/**
	 * Any files that are found with find_file are stored here so that
	 * any further requestes for the files are just given the path
	 * from this array, rather than searching for the file again.
	 *
	 * @var array
	 */
	private static $find_file_paths;	

	/**
	 * The current file being processed. Scaffold stores the directory,
	 * name and url of the currently processed file. This is used by 
	 * modules who want information about the current file.
	 *
	 * @var array
	 */
	public static $current = array();
	
	/**
	 * The current CSS string being processed. This is refreshed for each
	 * file that is parsed, but gives modules a way to determine what
	 * Scaffold is currently working with.
	 *
	 * @var string
	 */
	public static $css;
	
	/**
	 * List of included modules. They are stored with the module name
	 * as the key, and the path to the module as the value. However,
	 * calling the modules method will return just the names of the modules.
	 *
	 * @var array
	 */
	public static $modules;

	/**
	 * Flags allow Scaffold to create cache variants based on particular
	 * parameters. This could be the browser, the time etc. 
	 *
	 * @var array
	 */
	public static $flags = array();

	/**
	 * Options are used by modules to check if the user wants a paricular
	 * action to occur. They don't affect the cache, like flags do, so
	 * modules shouldn't modify the CSS string based on options. They
	 * can be used to modify the output or to perform some secondary
	 * action, like validating the CSS.
	 *
	 * @var array
	 */
	public static $options = array();
	
	/**
	 * If Scaffold encounted an error. You can check this variable to
	 * see if there were any errors when in_production is set to true.
	 *
	 * @var boolean
	 */
	public static $has_error = false;
	
	/**
	 * Stores the headers for sending to the browser.
	 *
	 * @var array
	 */
	private static $headers = array();
	
	/**
	 * The current cache object
	 *
	 * @var object
	 */
	private static $cache;
	
	/**
	 * Parse the CSS. This takes an array of files, options and configs
	 * and parses the CSS, outputing the processed CSS string.
	 *
	 * The required configuration options:
	 *
	 * 'in_production' 		- Whether Scaffold is in a production environment.
	 * 'cache_lifetime' 	- The time, in seconds, that the temporary cache files will last.
	 * 'log_threshold' 		- The minimum level for messages to be logged.
	 * 'error_threshold' 	- The minimum level for messages to be thrown as errors
	 * 'document_root' 		- The file path to the document root.
	 * 'system' 			- The path to the system folder.
	 * 'cache' 				- The path to the cache folder.
	 * 'disable' 			- An array of module names that will be disabled and not loaded.
	 *
	 * @param array List of files
	 * @param array Configuration options
	 * @param string Options
	 * @param boolean Return the CSS rather than displaying it
	 * @return string The processed css file as a string
	 */
	public static function parse( $files, $config, $options = array(), $return = false )
	{
		self::setup($config);

		$files = array_unique($files);

		self::$options = $options;
		
		/** 
		 * We create a folder inside the cache based on this particular
		 * request of files. We can then used this folder inside for caching
		 * further files within this request. Each request of files will have
		 * it's own folder inside the cache.
		 */
		$cache_folder = self::$config['cache'] . md5(serialize($files)) . '/';
		
		if(!is_dir($cache_folder))
		{
			mkdir($cache_folder);
			chmod($cache_folder, 0777);
		}
		
		self::$cache = $cache = new Scaffold_Cache(
			$cache_folder,
			self::$config['cache_lifetime'],
			self::$config['in_production']
		);

		/**
		 * We'll try and load the already combined and processed
		 * CSS file from the cache before we do anything else. 
		 * This particular cache file will only exist for a limited
		 * amount of time, as set in the config.
		 *
		 * When the time is up, this cache file is removed and Scaffold
		 * will continue as normal and reprocess the file.
		 *
		 * This only occurs in production mode to save unnecessary processing
		 * as Scaffold will only check the files every hour or so for changes,
		 * and even when it does, it might not need to reprocess each and every
		 * file that the user requested if they haven't actually changed.
		 */
		if(self::$config['in_production'] === true)
		{
			$output = $cache->temp('output',false);

			if($output !== null)
			{
				return self::output($cache->find($output),$return);
			}
		}

		# Get the flags from each of the loaded modules.
		$flags = self::flags();
		
		# Combined CSS filename
		$combined = md5(serialize(array($files,$flags))) . '.css';
	
		/**
		 * We loop through each of the files the user is requesting, make
		 * checks to make sure it's able to be parsed and then check the
		 * cache to see if it's already been processed.
		 *
		 * We compare the cache file to the original file to see if any 
		 * changes have been made. If so, or the cache file doesn't exist
		 * at all, we'll reprocess and recache the file.
		 *
		 * If any of the files needs to be reprocessed, we'll need to recache
		 * the combined CSS file as well. So we remove it and then rewrite it.
		 */
		foreach($files as $file)
		{
			# If it's a url
			if( substr($file, 0, 4) == "http" )
				Scaffold_Log::log('Scaffold cannot parse CSS files sent as URLs - ' . $file,0);

			# Find the CSS file
			$request = self::find_file($file, false, true);

			# Find the name of the we need to create in the cache directory.
			$cached_file = md5(serialize(array($request,$flags))) . '.css';
			
			if (!Scaffold_Utils::is_css($file))
				Scaffold_Log::log("Requested file isn't a css file: $file",0);

			# Try and load it from the cache
			$css = $cache->fetch($cached_file,filemtime($request));
			
			if(!isset($css))
			{				
				# Parse the CSS string
				$css = self::parse_file($request);

				# Write the css file to the cache
				$cache->write($css,$cached_file);

				# We'll need to recache the combined css too
				$cache->remove($combined);
			}

			$join[] = $css;
		}
		
		# If any of the files has changed we need to recache the combined
		if($cache->fetch($combined) === null)
		{
			$cache->write(implode('',$join),$combined);
		}

		$cache->write($combined,'output');

		return self::output($cache->find($combined),$return);
	}

	/**
	 * Sets the initial variables, checks if we need to process the css
	 * and then sends whichever file to the browser.
	 *
	 * @return void
	 */
	public static function setup($config) 
	{
		self::$config =& $config;

		# Set the errors to display
		if($config['in_production'] === false)
		{	
			ini_set('display_errors', true);
			error_reporting(E_ALL & ~E_STRICT);
		}
		else
		{
			ini_set('display_errors', false);
			error_reporting(0);
		}
		
		# Get the full paths
		$config['system'] = Scaffold_Utils::fix_path($config['system']);
		$config['cache']  = Scaffold_Utils::fix_path($config['cache']);
		
		# Prepare the logger
		Scaffold_Log::setup(
			$config['log_threshold'],
			$config['error_threshold'],
			$config['system'].'logs/'
		);

		# Set the current cache path
		$cache = new Scaffold_Cache( $config['cache'], $config['cache_lifetime'], $config['in_production'] );
		
		# We'll try and load everything we can from the cache
		if( $config['in_production'] === true )
		{
			self::$modules 			= $cache->temp('modules');
			self::$include_paths 	= $cache->temp('include_paths');
		}

		if(empty(self::$include_paths))
		{
			self::add_include_path(
				$config['system'], 
				$config['system'].'modules/', 
				$config['document_root']
			);
		}
		
		# Load the configs for the modules
		foreach(self::list_files('config') as $file)
		{
			include $file;
		}

		if(empty(self::$modules))
		{
			self::modules($config['disable']);
			$cache->write(self::$modules,'modules');
			$cache->write(self::$include_paths,'include_paths');
		}
		else
		{	
			foreach(self::$modules as $module)
				require_once $module;
		}
		
		return true;
	}

	/**
	 * Displays an error and halts the parsing.
	 *	
	 * @param $message
	 * @return void
	 */
	public static function error($message)
	{
		Scaffold_Log::log($message,0);
		self::$has_error = true;
	}

	/**
	 * Sends the headers
	 *
	 * @return boolean
	 */
	private static function send_headers()
	{
		if(!headers_sent())
		{
			self::$headers = array_unique(self::$headers);

			foreach(self::$headers as $name => $value)
			{
				if($name[0] != '_')
					header($name . ':' . $value);
				else
					header($value);
			}
			
			return true;
		}
	}
	
	/**
	 * Adds a new HTTP header for sending later.
	 *
	 * @author your name
	 * @param $name
	 * @param $value
	 * @return boolean
	 */
	private static function header($name,$value)
	{
		return self::$headers[$name] = $value;
	}

	/**
	 * Takes a relative path, gets the full server path, removes
	 * the www root path, leaving only the url path to the file/folder
	 *
	 * @author Anthony Short
	 * @param $relative_path
	 */
	public static function url_path($path) 
	{
		return self::reduce_double_slashes(str_replace( $_SERVER['DOCUMENT_ROOT'], '/', realpath($path) ));
	}
	
	/**
	 * Sets a cache flag
	 *
	 * @param 	$name	The name of the flag to set
	 * @return 	void
	 */
	public static function flag_set($name)
	{
		return self::$flags[] = $name;
	}
	
	/**
	 * Checks if a flag is set
	 *
	 * @param $flag
	 * @return boolean
	 */
	public static function flag($flag)
	{
		return (in_array($flag,self::$flags)) ? true : false;
	}
	
	/**
	 * Gets the flags from each of the modules
	 *
	 * @param $param
	 * @return $array The array of flags
	 */
	public static function flags()
	{
		if(isset(self::$flags))
			return self::$flags;

		foreach(self::modules() as $module)
			call_user_func(array($module,'flag'));

		return (isset(self::$flags)) ? self::$flags : false;
	}

	/**
	 * Get all include paths.
	 *
	 * @return  array
	 */
	public static function include_paths()
	{
		return self::$include_paths;
	}
	
	/**
	 * Adds a path to the include paths list
	 *
	 * @param 	$path 	The server path to add
	 * @return 	void
	 */
	public static function add_include_path($path)
	{
		if(func_num_args() > 1)
		{
			$args = func_get_args();

			foreach($args as $inc)
				self::add_include_path($inc);
		}
		else
		{
			self::$include_paths[] = Scaffold_Utils::fix_path($path);
		}
		
		self::$include_paths = array_unique(self::$include_paths);
	}
	
	/**
	 * Removes an include path
	 *
	 * @param	$path 	The server path to remove
	 * @return 	void
	 */
	public static function remove_include_path($path)
	{
		if(in_array($path, self::$include_paths))
		{
			unset(self::$include_paths[array_search($path, self::$include_paths)]);
		}
	}
	
	/**
	 * Checks to see if an option is set
	 *
	 * @param $name
	 * @return boolean
	 */
	public static function option($name)
	{
		return isset(self::$options[$name]);
	}

	/**
	 * Loads modules
	 *
	 * @return Array The names of the loaded addons
	 */
	public static function modules($disabled = array())
	{
		# If the modules have already been loaded
		if(isset(self::$modules))
			return array_keys(self::$modules);
		
		# Get each of the folders inside the Plugins and Modules directories
		$modules = self::list_files('modules');

		foreach($modules as $module)
		{			
			$name = basename($module);
			
			if(in_array($name, $disabled))
				continue;
			
			# Add this module folder to the include paths
			self::add_include_path($module);

			# The config file for the plugin (Optional)
			$config_file = $module.'/config.php';
			
			if(!isset(self::$config[$module]))
			{
				if(file_exists($config_file))
				{
					include $config_file;
					
					foreach($config as $key => $value)
						self::$config[$name][$key] = $value;
					
					unset($config);
				}
			}
			
			# Include the addon controller
			if( $controller = self::find_file($name.'.php', false, true) )
			{
				require_once($controller);
				self::$modules[$name] = $controller;
			}
		}
		
		return array_keys(self::$modules);
	}
	
	/**
	 * Allows modules to hook into the processing at any point
	 *
	 * @param $hook
	 * @param $params
	 * @return boolean
	 */
	private static function hook($name,&$params='')
	{
		foreach(self::modules() as $module)
		{
			if(method_exists($module,$name))
			{
				$params = call_user_func(array($module,$name),$params);
			}
		}
	}

	/**
	 * Parses the single CSS file
	 *
	 * @param $file 	The file to the parsed
	 * @return $css 	string
	 */
	public static function parse_file($file)
	{
		self::add_include_path(dirname($file));
		self::set_current($file);

		self::$css = file_get_contents($file);
		$css =& self::$css;
		
		if(class_exists('Import'))
			$css = Import::parse($css);

		/**
		 * Import Process Hook
		 */
		self::hook('import_process',$css);

		if(class_exists('Constants'))
			$css = Constants::parse($css);
		
		/**
		 * Pre-process Hook
		 */
		self::hook('pre_process',$css);
		
		if(class_exists('Layout'))
			$css = Layout::parse($css);
			
		/**
		 * Process Hook
		 */
		self::hook('process',$css);
		
		if(class_exists('Mixins'))
			$css = Mixins::parse($css);

		if(class_exists('Constants'))
			$css = Constants::replace($css);
						
		if(class_exists('Iteration'))
			$css = Iteration::parse($css);
		
		if(class_exists('Expression'))
			$css = Expression::parse($css);
		
		if(class_exists('NestedSelectors'))
			$css = NestedSelectors::parse($css);
			
		/**
		 * Post-process Hook
		 */
		self::hook('post_process',$css);

		if(class_exists('Absolute_Urls'))
			$css = Absolute_Urls::rewrite($css);

		if(class_exists('Minify'))
			$css = Minify::compress($css);
		
		/**
		 * Formatting Hook
		 */
		self::hook('formatting_process',$css);

		self::remove_include_path(dirname($file));
		
		return $css;
	}
	
	/**
	 * Output the CSS to the browser
	 *
	 * @return void
	 */
	public static function output($file,$return = false)
	{		
		$css 		= file_get_contents($file);
		$modified 	= (int) filemtime($file);
		
		/**
		 * The current working file is now the compiled
		 * CSS file from the cache
		 */

		self::set_current($file);
		
		/**
		 * Output Hook
		 * Modules can use this hook to alter what is displayed to the browser
		 * by loading views or however else they want to.
		 */

		if(self::$config['in_production'] === false)
		{				
			foreach(self::modules() as $module)
				call_user_func(array($module,'output'), $css);
		}
		
		/**
		 * Set the HTTP headers for the request. Scaffold will set
		 * all the headers required to score a A grade on YSlow. This
		 * means your CSS will be sent as quickly as possible to the browser.
		 */

		if(self::$config['cache_lifetime'] != false)
		{
			self::header('Expires',gmdate('D, d M Y H:i:s', $_SERVER['REQUEST_TIME'] + self::$config['cache_lifetime']) . ' GMT');
			self::header('Cache-Control','max-age='.self::$config['cache_lifetime'].', public');
		}
		else
		{
			// Far future expires header
			self::header('Expires',gmdate('D, d M Y H:i:s', $_SERVER['REQUEST_TIME'] + 315360000) . ' GMT');
		}

		$protocol 		= isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0';
		$modified_since = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : 0;
		$size 			= filesize($file);	
		$etag			= md5(serialize(array($file,$modified,$size)));
		$last_modified	= gmdate('D, d M Y H:i:s', $modified) .' GMT';	
		
		self::header('Content-Type','text/css');
		self::header('Last-Modified',$last_modified);
		self::header('Content-Length',$size);
		self::header('ETag',$etag);

		if(isset($modified_since) && $modified <= strtotime($modified_since))
		{
			self::header('_responseCode',"{$protocol} 304 Not Modified");
		}

		/**
		 * Finally, we either return or output the CSS
		 */
		self::send_headers();
		Scaffold_Log::save();

		if($return === false)
			echo $css;
		else
			return array(
			    'error'   => self::$has_error,
			    'content' => $css,
			    'headers' => self::$headers,
			    'flags'   => self::$flags,
			);
	}
	
	/**
	 * Sets the currently active file information
	 *
	 * @param $file
	 * @return void
	 */
	private static function set_current($file)
	{
		self::$current = array
		(
			'file' => $file,
			'path' => dirname($file) . '/',
			'url'  => self::url_path(dirname($file))
		);
	}
	
	/**
	 * Loads a view file
	 *
	 * @param 	string	The name of the view
	 * @param	boolean	Render the view immediately
	 * @param	boolean Return the contents of the view
	 * @return	void	If the view is rendered
	 * @return	string	The contents of the view
	 */
	public static function load_view( $view, $render = false, $return = false )
	{
		# Find the view file
		$view = self::find_file($view, 'views', true);
		
		# Display the view
		if ($render === true)
		{
			include $view;
			return;
		}

		# Buffering on
		ob_start();
		$view = file_get_contents($view);
		echo $view;
		
		# Fetch the output and close the buffer
		$output = ob_get_clean();
		
		if($return)
			return $output;
	}
	
	/**
	 * Find a resource file in a given directory. Files will be located according
	 * to the order of the include paths.
	 *
	 * @throws  error  	 if file is required and not found
	 * @param   string   filename to look for
	 * @param   string   directory to search in
	 * @param   boolean  file required
	 * @return  string   if the file is found
	 * @return  FALSE    if the file is not found
	 */
	public static function find_file($filename, $directory = '', $required = FALSE)
	{		
		# Search path
		$search = $directory.'/'.$filename;
		
		if(file_exists($filename))
		{
			return self::$find_file_paths[$filename] = $filename;
		}
		elseif(file_exists($search))
		{
			return self::$find_file_paths[$search] = realpath($search);
		}
		
		if (isset(self::$find_file_paths[$search]))
			return self::$find_file_paths[$search];

		# Load include paths
		$paths = self::include_paths();

		# Nothing found, yet
		$found = NULL;

		if(in_array($directory, $paths))
		{
			if (is_file($directory.$filename))
			{
				# A matching file has been found
				$found = $search;
			}
		}
		else
		{
			foreach ($paths as $path)
			{
				if (is_file($path.$search))
				{
					# A matching file has been found
					$found = realpath($path.$search);

					# Stop searching
					break;
				}
				elseif (is_file(realpath($path.$search)))
				{
					# A matching file has been found
					$found = realpath($path.$search);

					# Stop searching
					break;
				}
			}
		}

		if ($found === NULL)
		{
			if ($required === TRUE)
			{
				# If the file is required, throw an exception
				self::error("Cannot find the file: " . str_replace($_SERVER['DOCUMENT_ROOT'], '/', $search));
			}
			else
			{
				# Nothing was found, return FALSE
				$found = FALSE;
			}
		}

		return self::$find_file_paths[$search] = $found;
	}

	/**
	 * Lists all files and directories in a resource path.
	 *
	 * @param   string   directory to search
	 * @param   boolean  list all files to the maximum depth?
	 * @param   string   full path to search (used for recursion, *never* set this manually)
	 * @return  array    filenames and directories
	 */
	public static function list_files($directory, $recursive = FALSE, $path = FALSE)
	{
		$files = array();

		if ($path === FALSE)
		{
			$paths = array_reverse(self::include_paths());

			foreach ($paths as $path)
			{
				// Recursively get and merge all files
				$files = array_merge($files, self::list_files($directory, $recursive, $path.$directory));
			}
		}
		else
		{
			$path = rtrim($path, '/').'/';

			if (is_readable($path))
			{
				$items = (array) glob($path.'*');
				
				if ( ! empty($items))
				{
					foreach ($items as $index => $item)
					{
						$name = pathinfo($item, PATHINFO_BASENAME);
						
						if(substr($name, 0, 1) == '.' || substr($name, 0, 1) == '-')
						{
							continue;
						}
						
						$files[] = $item = str_replace('\\', '/', $item);

						// Handle recursion
						if (is_dir($item) AND $recursive == TRUE)
						{
							// Filename should only be the basename
							$item = pathinfo($item, PATHINFO_BASENAME);

							// Append sub-directory search
							$files = array_merge($files, self::list_files($directory, TRUE, $path.$item));
						}
					}
				}
			}
		}

		return $files;
	}
}