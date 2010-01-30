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
	 * CSS object for each processing phase. As Scaffold loops
	 * through the files, it creates an new CSS object in this member 
	 * variable. It is through this variable that modules can access
	 * the current CSS string being processed
	 *
	 * @var object
	 */
	public static $css;
	
	/**
	 * The level of logged message to be thrown as an error. Setting this
	 * to 0 will mean only error-level messages are thrown. However, setting
	 * it to 1 will throw warnings as errors and halt the process.
	 *
	 * @var int
	 */
	private static $error_threshold;

	/**
	 * The final, combined output of the CSS.
	 *
	 * @var Mixed
	 */
	public static $output;
	
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
	public static $options;
	
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
	private static $headers;
	
	/**
	 * Parse the CSS. This takes an array of files, options and configs
	 * and parses the CSS, outputing the processed CSS string.
	 *
	 * @param array List of files
	 * @param array Configuration options
	 * @param string Options
	 * @param boolean Return the CSS rather than displaying it
	 * @return string The processed css file as a string
	 */
	public static function parse( $files, $config, $options = array(), $display = false )
	{
		Scaffold::setup($config);
		self::$options = $options;
		$css = false;
		
		# Get the flags from each of the loaded modules.
		$flags = (self::$flags === false) ? array() : self::flags();
		
		# The final, combined CSS file in the cache
		$combined = md5(serialize(array($files,$flags))) . '.css';
		
		/**
		 * Check if we should use the combined cache right now and skip unneeded processing
		 */
		if(SCAFFOLD_PRODUCTION AND Scaffold_Cache::exists($combined) AND Scaffold_Cache::is_fresh($combined))
		{
			Scaffold::$output = Scaffold_Cache::open($combined);
		}
		
		if(Scaffold::$output === null)
		{
			foreach($files as $file)
			{
				if(substr($file, 0, 4) == "http" OR substr($file, -4, 4) != ".css")
				{
					Scaffold::error('Scaffold cannot the requested file - ' . $file);
				}
				
				/**
				 * If there are flags, we'll include them in the filename
				 */
				if(!empty($flags))
				{
					$cached_file = dirname($file) . '/' . pathinfo($file, PATHINFO_FILENAME) . '_' . implode('_', $flags) . '.css';
				}
				else
				{
					$cached_file = $file;
				}

				$request = Scaffold::find_file($file, false, true);
				
				/**
				 * While not in production, we want to to always recache, so we'll fake the time
				 */
				$modified = (SCAFFOLD_PRODUCTION) ? Scaffold_Cache::modified($cached_file) : 0;
	
				/**
				 * If the CSS file has been changed, or the cached version doesn't exist
				 */			
				if(!Scaffold_Cache::exists($cached_file) OR $modified < filemtime($request))
				{
					Scaffold_Cache::write( Scaffold::process($request), $cached_file );
					Scaffold_Cache::remove($combined);
				}
	
				$css .= Scaffold_Cache::open($cached_file);
			}

			Scaffold::$output = $css;

			/**
			 * If any of the files have changed we need to recache the combined
			 */
			if(!Scaffold_Cache::exists($combined))
			{
				Scaffold_Cache::write(self::$output,$combined);
			}
		
			/**
			 * Hook to modify what is sent to the browser
			 */
			if(!SCAFFOLD_PRODUCTION) Scaffold::hook('display');
		}
		
		/**
		 * Set the HTTP headers for the request. Scaffold will set
		 * all the headers required to score an A grade on YSlow. This
		 * means your CSS will be sent as quickly as possible to the browser.
		 */
		Scaffold::set_headers(Scaffold_Cache::find($combined),Scaffold_Cache::modified($combined),$config['cache_lifetime']);
		
		/**
		 * Save the logs and exit 
		 */
		Scaffold_Event::run('system.shutdown');
	}

	/**
	 * Sets the initial variables, checks if we need to process the css
	 * and then sends whichever file to the browser.
	 *
	 * @return void
	 */
	public static function setup($config) 
	{
		/**
		 * Choose whether to show or hide errors
		 */
		if(SCAFFOLD_PRODUCTION === false)
		{	
			ini_set('display_errors', true);
			error_reporting(E_ALL & ~E_STRICT);
		}
		else
		{
			ini_set('display_errors', false);
			error_reporting(0);
		}
		
		/**
		 * Define contstants for system paths for easier access.
		 */
		if(!defined('SCAFFOLD_SYSPATH'))
		{
			define('SCAFFOLD_SYSPATH', self::fix_path($config['system']));
			define('SCAFFOLD_DOCROOT', $config['document_root']);
		}
		
		/**
		 * Add include paths for finding files
		 */
		Scaffold::add_include_path(SCAFFOLD_SYSPATH,SCAFFOLD_DOCROOT);
	
		/**
		 * Tell the cache where to save files and for how long to keep them for
		 */
		Scaffold_Cache::setup( Scaffold::fix_path($config['cache']), $config['cache_lifetime'] );
		
		/**
		 * The level at which logged messages will halt processing and be thrown as errors
		 */
		self::$error_threshold = $config['error_threshold'];
		
		/**
		 * Disabling flags allows for quicker processing
		 */
		if($config['disable_flags'] === true)
		{
			self::$flags = false;
		}
		
		/**
		 * Tell the log where to save it's files. Set it to automatically save the log on exit
		 */
		if($config['enable_log'] === true)
		{
			Scaffold_Log::log_directory(SCAFFOLD_SYSPATH.'logs');			
			Scaffold_Event::add('system.shutdown', array('Scaffold_Log','save'));
		}

		/**
		 * Load each of the modules
		 */
		foreach(Scaffold::list_files(SCAFFOLD_SYSPATH.'modules') as $module)
		{
			$name = basename($module);
			$module_config = SCAFFOLD_SYSPATH.'config/' . $name . '.php';
			
			if(file_exists($module_config))
			{
				unset($config);
				include $module_config;				
				self::$config[$name] = $config;
			}
			
			self::add_include_path($module);
			
			if( $controller = Scaffold::find_file($name.'.php', false, true) )
			{
				require_once($controller);
				self::$modules[$name] = new $name;
			}
		}

		Scaffold_Event::add('system.shutdown', array('Scaffold','shutdown'));
	}
	
	/**
	 * Parses the single CSS file
	 *
	 * @param $file 	The file to the parsed
	 * @return $css 	string
	 */
	public static function process($file)
	{
		/**
		 * This allows Scaffold to find files in the directory of the CSS file
		 */
		Scaffold::add_include_path($file);

		/** 
		 * We create a new CSS object for each file. This object
		 * allows modules to easily manipulate the CSS string.
		 * Note:Inline comments are stripped when the file is loaded.
		 */
		Scaffold::$css = new Scaffold_CSS($file);
		
		/**
		 * Module Initialization Hook
		 * This hook allows modules to load libraries and create events
		 * before any processing is done at all. 
		 */
		self::hook('initialize');

		/**
		 * Import Process Hook
		 * This hook is for doing any type of importing/including in the CSS
		 */
		self::hook('import_process');
		
		/**
		 * Pre-process Hook
		 * There shouldn't be any heavy processing of the string here. Just pulling
		 * out @ rules, constants and other bits and pieces.
		 */
		self::hook('pre_process');
			
		/**
		 * Process Hook
		 * The main process. None of the processes should conflict in any of the modules
		 */
		self::hook('process');
			
		/**
		 * Post-process Hook
		 * After any non-standard CSS has been processed and removed. This is where
		 * the nested selectors are parsed. It's not perfectly standard CSS yet, but
		 * there shouldn't be an Scaffold syntax left at all.
		 */
		self::hook('post_process');

		/**
		 * Formatting Hook
		 * Stylise the string, rewriting urls and other parts of the string. No heavy processing.
		 */
		self::hook('formatting_process');
		
		/**
		 * Clean up the include paths
		 */
		self::remove_include_path($file);

		return (string)Scaffold::$css;
	}

	/**
	 * Sets the HTTP headers for a particular file
	 *
	 * @param $param
	 * @return return type
	 */
	private static function set_headers($file,$modified,$lifetime)
	{
		self::$headers = array();

		$protocol 		= isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0';
		$modified_since = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : 0;
		
		if(self::$output !== null)
		{
			self::header('Content-Length',strlen(self::$output));
		}

		if($modified <= strtotime($modified_since))
		{
			self::header('_responseCode',"{$protocol} 304 Not Modified");
		}
		
		if($lifetime > 0)
		{	
			# There's a fixed time on the cache
			self::header('Expires',gmdate('D, d M Y H:i:s', $modified + $lifetime) . ' GMT');
			self::header('Cache-Control','max-age='.$lifetime.', public');
		}
		else
		{
			# Set it far in the future
			self::header('Expires',gmdate('D, d M Y H:i:s', $_SERVER['REQUEST_TIME'] + 315360000) . ' GMT');
		}
		
		self::header('Last-Modified',gmdate('D, d M Y H:i:s', $modified) .' GMT');
		self::header('ETag', md5(serialize(array($file,$modified))) );
		self::header('Content-Type','text/css');
		self::header('Vary','Accept-Encoding');
	}
	
	/**
	 * Allows modules to hook into the processing at any point
	 *
	 * @param $method The method to check for in each of the modules
	 * @return boolean
	 */
	private static function hook($method)
	{
		foreach(self::$modules as $module_name => $module)
		{
			if(method_exists($module,$method))
			{
				Scaffold_Event::add('system.' . $method, array($module_name,$method));
			}
		}
		
		Scaffold_Event::run('system.' . $method);
	}

	/**
	 * Cleans up Scaffold, saves the log and exits.
	 *
	 * @return void
	 */
	public function shutdown($display = true)
	{
		if($display === true)
		{
			# Send the headers
			if(!headers_sent())
			{
				self::$headers = array_unique(self::$headers);
	
				foreach(self::$headers as $name => $value)
				{
					if($name[0] != '_')
					{
						header($name . ':' . $value);
					}
					else
					{
						header($value);
					}
				}
			}
		
			echo self::$output;
			exit;
		}
		else
		{
			self::$output = array(
			    'error'   => self::$has_error,
			    'content' => self::$output,
			    'headers' => self::$headers,
			    'flags'   => self::$flags,
			);
			
			exit;
		}
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

		self::header('_responseCode','HTTP/1.1 500 Internal Server Error');
		
		if(!SCAFFOLD_PRODUCTION)
		{
			include self::find_file('scaffold_error.php', 'views', true);
		}

		self::shutdown(false);
	}
	
	/**
	 * Uses the logging class to log a message
	 *
	 * @author your name
	 * @param $message
	 * @return void
	 */
	public static function log($message,$level)
	{
		if ($level <= self::$error_threshold)
		{
			self::error($message);
		}
		else
		{
			Scaffold_Log::log($message,$level);
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
		if(!empty(self::$flags))
			return self::$flags;
			
		self::hook('flag');

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
	
		if(is_file($path))
		{
			$path = dirname($path);
		}
	
		if(!in_array($path,self::$include_paths))
		{
			self::$include_paths[] = Scaffold_Utils::fix_path($path);
		}
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
			if(is_dir($directory))
			{
				$files = array_merge($files, self::list_files($directory, $recursive, $directory));
			}
			else
			{
				foreach (array_reverse(self::include_paths()) as $path)
				{
					$files = array_merge($files, self::list_files($directory, $recursive, $path.$directory));
				}
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