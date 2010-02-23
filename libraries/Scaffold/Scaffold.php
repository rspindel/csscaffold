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

class Scaffold
{
	const VERSION = '2.0.8';
	
	/**
	 * If Scaffold has been initialized
	 *
	 * @var boolean
	 */
	public static $init = false;
	
	/**
	 * Production mode?
	 *
	 * @var boolean
	 */
	public static $production = false;
	
	/**
	 * Path to the document root
	 *
	 * @var string
	 */
	public static $root;
	
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
	 * The level of logged message to be thrown as an error. Setting this
	 * to 0 will mean only error-level messages are thrown. However, setting
	 * it to 1 will throw warnings as errors and halt the process.
	 *
	 * @var int
	 */
	private static $error_threshold;

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
	private static $find_file_paths = array();
	
	/**
	 * List of included modules. They are stored with the module name
	 * as the key, and the path to the module as the value. However,
	 * calling the modules method will return just the names of the modules.
	 *
	 * @var array
	 */
	public static $modules;
	
	/**
	 * Language extensions - properties, directives and functions
	 *
	 * @var array
	 */
	public static $extensions = array();
	
	/**
	 * If Scaffold encounted an error. You can check this variable to
	 * see if there were any errors when in_production is set to true.
	 *
	 * @var boolean
	 */
	public static $has_error = false;
	
	/**
	 * Level of PHP compression. By default, this is false.
	 *
	 * @var mixed
	 */
	private static $compression = false;
	
	/**
	 * Logging object
	 *
	 * @var object
	 */
	public static $log;
	
	/**
	 * Caching object
	 *
	 * @var object
	 */
	public static $cache;
	
	/**
	 * Is Scaffold handling errors?
	 *
	 * @var boolean
	 */
	private static $errors = false;
	
	/**
	 * The directory to save processed files to
	 *
	 * @var string
	 */
	public static $output_path;
	
	/**
	 * The lifetime of a processed file
	 *
	 * @var int
	 */
	public static $lifetime;

	/**
	 * Sets the initial variables, checks if we need to process the css
	 * and then sends whichever file to the browser.
	 *
	 * @return void
	 */
	public static function init($config) 
	{
		if(Scaffold::$init === true)
			return;
		
		Scaffold::$init = true;
			
		/**
		 * The processing mode
		 */
		Scaffold::$production = $config['in_production'];
		
		/**
		 * Load the libraries. Do it manually if you don't like this way.
		 */
		if($config['auto_load'])
		{		
			spl_autoload_register(array('Scaffold','auto_load'));
		}
		
		/**
		 * Let Scaffold catch exceptions and errors
		 */
		if($config['errors'] AND Scaffold::$production === false)
		{
			ini_set('display_errors', true);
			error_reporting(E_ALL | E_STRICT);

			# Exceptions
			set_exception_handler(array('Scaffold','exception_handler'));
			
			# Errors
			set_error_handler(array('Scaffold','error_handler'));
			
			# Scaffold is handling errors
			Scaffold::$errors = true;
		}
		
		/**
		 * Scaffold will run this just before it closes. It also
		 * manages fatal errors if you've set Scaffold to handle errors
		 */
		register_shutdown_function(array('Scaffold','shutdown_handler'));
		
		/**
		 * Set timezone, just in case it isn't set. PHP 5.2+ 
		 * throws a tantrum if you try and use time() without
		 * this being set.
		 */
		if(function_exists('date_default_timezone_set'))
		{
			date_default_timezone_set($config['timezone']);
		}

		/**
		 * Define contstants for system paths for easier access.
		 */
		if(!defined('SCAFFOLD_SYSPATH'))
		{
			define('SCAFFOLD_SYSPATH', self::path($config['system']));
		}
		
		/**
		 * Set the amount of PHP gzipping to use.
		 */
		if($config['gzip_compression'] !== false)
		{
			self::$compression = $config['gzip_compression'];
		}
		
		/**
		 * Set the server variable for document root. A lot of 
		 * the utility functions depend on this. Windows servers
		 * don't set this, so we'll add it manually if it isn't set.
		 */
		if($config['document_root'] != '')
		{
			Scaffold::root($config['document_root']);
		}

		/**
		 * Add include paths for finding files
		 */
		Scaffold::add_include_path(SCAFFOLD_SYSPATH,Scaffold::root());
		
		/**
		 * Get the singleton instance of the log
		 */
		Scaffold::$log = Scaffold_Log::instance();
		
		/**
		 * Tell the log where to save it's files.
		 */
		if($config['log'] === true)
		{
			if(is_writable(SCAFFOLD_SYSPATH.'logs'))
			{
				Scaffold::$log->directory(SCAFFOLD_SYSPATH.'logs');	
			}
			else
			{
				Scaffold::error("Logs folder is not writable");
			}
		}
		
		/**
		 * If we're just in development mode, we'll want to always recache
		 * the file. Setting the cache_lifetime to 0 will force Scaffold
		 * to make sure it reparsed the file each time.
		 */
		if(Scaffold::$production === false)
		{
			self::$lifetime = 0;
		}
		else
		{
			self::$lifetime = $config['cache_lifetime'];
		}

		/**
		 * Tell the cache where to save files and for how long to keep them for
		 */
		$cache = Scaffold::path($config['cache']);

		if (!is_dir($cache) OR !is_writable($cache))
		{
			Scaffold::error("Cache path does not exist or is not writable. [".Scaffold::url($cache)."]");
		}

		Scaffold::$cache = new Scaffold_Cache($cache,$config['cache_lifetime']);
		
		/**
		 * The output directory. This where we'll save processed CSS files.
		 */
		if($config['output_path'] == '')
		{
			self::$output_path = $cache;
		}
		else
		{
			self::$output_path = Scaffold::path($config['output_path']);
		}
		
		/**
		 * The level at which logged messages will halt processing and be thrown as errors
		 */
		self::$error_threshold = $config['error_threshold'];

		# Load the modules	
		Scaffold::modules(SCAFFOLD_SYSPATH);

		# Load the extensions
		Scaffold::extensions(SCAFFOLD_SYSPATH);
	}
	
	/**
	 * Resets everything and finishes up.
	 *
	 * @author your name
	 * @param $param
	 * @return void
	 */
	public static function deinit()
	{
		if(Scaffold::$init)
		{
			# Remove everything init created
			Scaffold::$config = Scaffold::$output = NULL;
			
			# Reset storage
			Scaffold::$modules = Scaffold::$extensions = array();
			
			# Reset booleans
			Scaffold::$init = Scaffold::$has_error = FALSE;
		}
	}
	
	/**
	 * Automatically loads Scaffold's classes from the libraries
	 * directory in the default setup.
	 *
	 *     // Loads libraries/my/class/name.php
	 *     Scaffold::auto_load('My_Class_Name');
	 *
	 * @param   string   class name
	 * @author 	Kohana
	 * @return  boolean
	 */
	public static function auto_load($class)
	{
		# Transform the class name into a path
		$file = str_replace('_', '/', strtolower($class)) . '.php';

		if ($path = Scaffold::find_file($file,'libraries'))
		{
			require $path;
			return TRUE;
		}

		return FALSE;
	}

	/******************************************************************************************************
	 * Extension Methods
	 *****************************************************************************************************/
	
	/**
	 * Loads functions inside an extensions folder.
	 *
	 * @author your name
	 * @param $dir
	 * @return void
	 */
	public static function extensions($base = false)
	{
		if($base !== false)
		{
			/**
			 * Loads custom functions
			 */
			foreach(Scaffold::list_files($base.'/extensions/functions') as $dir)
			{
				$phase = str_replace('phase_','',basename($dir));
				
				foreach(Scaffold::list_files($dir) as $file)
				{
					if(is_dir($file))
						continue;
						
					$unique = false;
						
					include $file;
					
					$name = pathinfo($file, PATHINFO_FILENAME);
					
					/**
					 * The name of the function we'll call for this property
					 */
					$callback = 'Scaffold_'.str_replace('-','_',$name);
					
					self::$extensions['functions'][$name] = array
					(
						'unique' => $unique,
						'path' => $file,
						'callback' => $callback,
						'phase' => (int)$phase
					);
				}
			}
			
			/**
			 * Sort the functions by phase
			 */
			if(isset(self::$extensions['functions']))
			{
				uksort(self::$extensions['functions'], array('Scaffold','sort_functions'));
			}

			/**
			 * Loads custom properties
			 */
			foreach(Scaffold::list_files($base.'/extensions/properties') as $file)
			{
				if(is_dir($file))
					continue;
					
				include $file;
				
				$name = pathinfo($file, PATHINFO_FILENAME);
				
				/**
				 * The name of the function we'll call for this property
				 */
				$callback = 'Scaffold_'.str_replace('-','_',$name);
				
				self::$extensions['properties'][$name] = array
				(
					'path' => $file,
					'callback' => $callback,
				);
			}
		}
		
		return self::$extensions;
	}
	
	/**
	 * Sorts functions in the extensions array by phase
	 *
	 * @author your name
	 * @param $a
	 * @param $b
	 * @return void
	 */
	private static function sort_functions($a,$b)
	{
		$phase_a = self::$extensions['functions'][$a]['phase'];
		$phase_b = self::$extensions['functions'][$b]['phase'];
		
		if($phase_a > $phase_b)
		{
			return 1;
		}
		elseif($phase_a < $phase_b)
		{
			return -1;
		}
		else
		{
			return 0;
		}
	}
	
	
	/******************************************************************************************************
	 * Addon Methods
	 *****************************************************************************************************/
	
	/**
	 * Loads the modules and returns the array
	 *
	 * @author your name
	 * @param $path
	 * @return array
	 */
	public static function modules($path = false)
	{
		# Return a single module
		if(isset(Scaffold::$modules[$path]))
		{
			return Scaffold::$modules[$path];
		}
		
		# Or all of them
		elseif(isset(Scaffold::$modules))
		{
			return Scaffold::$modules;
		}

		# Or load them all
		foreach(Scaffold::list_files($path.'modules') as $module)
		{
			$config = array();
			$name = basename($module);
			
			# This will allow us to find files inside the module folder
			self::add_include_path($module);
			
			if( $controller = Scaffold::find_file($name.'.php') )
			{
				# Module_name/Module_name.php
				require_once($controller);
				
				# Load the config from /config/Module_name.php
				$module_config = SCAFFOLD_SYSPATH.'config/' . $name . '.php';
				
				if(file_exists($module_config))
				{
					include $module_config;				
				}
				
				# Create the instance
				self::$modules[$name] = new $name($config);
			}
			
			# Load custom functions
			Scaffold::extensions($module);
		}
	}
	

	/******************************************************************************************************
	 * Display Methods
	 *****************************************************************************************************/
	
	/**
	 * Renders the CSS
	 *
	 * @param $output What to display
	 * @return void
	 */
	public static function render($content,$headers)
	{
		# If it's not modified, we shouldn't be sending anything to the browser
		if($headers['_status'] == 304)
		{
			$content = false;
		}
		
		if($content !== false)
		{
			if (self::$compression AND ini_get('output_handler') !== 'ob_gzhandler' AND (int) ini_get('zlib.output_compression') === 0)
			{
				if (self::$compression < 1 OR self::$compression > 9)
				{
					# Normalize the level to be an integer between 1 and 9. This
					# step must be done to prevent gzencode from triggering an error
					self::$compression = max(1, min(self::$compression, 9));
				}
	
				if (stripos(@$_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== FALSE)
				{
					$compress = 'gzip';
				}
				elseif (stripos(@$_SERVER['HTTP_ACCEPT_ENCODING'], 'deflate') !== FALSE)
				{
					$compress = 'deflate';
				}
			}
	
			if (isset($compress) AND self::$compression > 0)
			{
				switch ($compress)
				{
					case 'gzip':
						# Compress output using gzip
						$content = gzencode($content, self::$compression);
					break;
					case 'deflate':
						# Compress output using zlib (HTTP deflate)
						$content = gzdeflate($content, self::$compression);
					break;
				}
	
				# This header must be sent with compressed content to prevent browser caches from breaking
				$output['headers']['Vary'] = 'Accept-Encoding';
	
				# Send the content encoding header
				$output['headers']['Content-Encoding'] = $compress;
			}
		}
	
		# Send the headers
		Scaffold::send_headers($headers);
	
		echo $content; 
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
	public static function view( $view, $render = false )
	{
		# Find the view file
		$view = self::find_file($view . '.php', 'views', true);
		
		# Display the view
		if ($render === true)
		{
			include $view;
			return;
		}
		
		# Return the view
		else
		{
			ob_start();
			echo file_get_contents($view);
			return ob_get_clean();
		}
	}

	
	/******************************************************************************************************
	 * Header Methods
	 *****************************************************************************************************/
	
	/**
	 * Sets the HTTP headers for a particular file
	 *
	 * @param $param
	 * @return return type
	 */
	public static function headers($file,$lifetime)
	{
		$file = Scaffold::find_file($file);

		$length = strlen(file_get_contents($file));
		$modified = filemtime($file);

		/**
		 * Set the expires headers
		 */
		$now = $expires = time();

		// Set the expiration timestamp
		$expires += $lifetime;

		$headers['Last-Modified'] 	= gmdate('D, d M Y H:i:s T', $now);
		$headers['Expires'] 		= gmdate('D, d M Y H:i:s T', $expires);
		$headers['Cache-Control'] 	= 'max-age='.$lifetime;
		$headers['ETag'] 			= Scaffold::etag($file);
		$headers['Content-Type'] 	= 'text/css';
		$headers['_status']			= '';
		
		/**
		 * Content Length
		 * Sending Content-Length in CGI can result in unexpected behavior
		 */
		if(stripos(PHP_SAPI, 'cgi') === FALSE)
		{
			$headers['Content-Length'] = $length;
		}
		
		/**
		 * Set the expiration headers
		 */
		if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE']))
		{
			if (($strpos = strpos($_SERVER['HTTP_IF_MODIFIED_SINCE'], ';')) !== FALSE)
			{
				// IE6 and perhaps other IE versions send length too, compensate here
				$mod_time = substr($_SERVER['HTTP_IF_MODIFIED_SINCE'], 0, $strpos);
			}
			else
			{
				$mod_time = $_SERVER['HTTP_IF_MODIFIED_SINCE'];
			}

			$mod_time = strtotime($mod_time);
			$mod_time_diff = $mod_time + $lifetime - time();

			if ($mod_time_diff > 0)
			{
				// Modify some of the headers
				$headers['Last-Modified'] 	= gmdate('D, d M Y H:i:s T', $mod_time);
				$headers['Expires'] 		= gmdate('D, d M Y H:i:s T', time() + $mod_time_diff);
				$headers['Cache-Control']	= 'max-age='.$mod_time_diff;
				$headers['_status'] 		= self::NOT_MODIFIED;
			}
		}
		
		return $headers;
	}
	
	/**
	 * Sends all of the stored headers to the browser
	 *
	 * @return void
	 */
	private static function send_headers($headers)
	{
		if(!headers_sent())
		{
			$headers = array_unique($headers);

			foreach($headers as $name => $value)
			{
				if($name != '_status')
				{
					header($name . ':' . $value);
				}
				else
				{
					if($value === 304)
					{
						header('Status: 304 Not Modified', TRUE, self::NOT_MODIFIED);
					}
				}
			}
		}
	}
	
	/**
	 * Creates a hash of a file to be used as an etag
	 *
	 * @param $file Path to the file
	 * @return string
	 */
	public static function etag($file)
	{
		return md5(serialize(array(strlen(file_get_contents($file)),filemtime($file),$file)));
	}
	

	/******************************************************************************************************
	 * Message Methods
	 *****************************************************************************************************/

	/**
	 * Displays an error and halts the parsing.
	 *	
	 * @param $message
	 * @return void
	 */
	public static function error($message)
	{
		/**
		 * Log the message before we throw the error
		 */
		Scaffold::$log->add($message,0);
		
		/**
		 * Useful variable to let other objects know there was an error with the parsing
		 */
		self::$has_error = true;
		
		/**
		 * Server error status
		 */
		if(!headers_sent())
			header('Content-Type: text/html;', TRUE, 500);
		
		/**
		 * This will be caught in the parse method
		 */
		throw new Exception($message);
	}
	
	/**
	 * PHP error handler, converts all errors into ErrorExceptions. This handler
	 * respects error_reporting settings.
	 *
	 * @author 	Kohana
	 * @throws  ErrorException
	 * @return  TRUE
	 */
	public static function error_handler($code, $error, $file = NULL, $line = NULL)
	{
		if (error_reporting() & $code)
		{
			# Convert the error into an ErrorException
			throw new ErrorException($error, $code, 0, $file, $line);
		}

		# Do not execute the PHP error handler
		return TRUE;
	}

	/**
	 * Inline exception handler, displays the error message, source of the
	 * exception, and the stack trace of the error.
	 *
	 * @author 	Kohana
	 * @param   object   exception object
	 * @return  boolean
	 */
	public static function exception_handler(Exception $e)
	{
		try
		{
			# Exception text information
			$type    = get_class($e);
			$code    = $e->getCode();
			$message = $e->getMessage();
			$file    = str_replace(SCAFFOLD_SYSPATH,'',$e->getFile());
			$line    = $e->getLine();
			
			if($e instanceof ErrorException)
			{
				$php_errors = array(
					E_ERROR              => 'Fatal Error',
					E_USER_ERROR         => 'User Error',
					E_PARSE              => 'Parse Error',
					E_WARNING            => 'Warning',
					E_USER_WARNING       => 'User Warning',
					E_STRICT             => 'Strict',
					E_NOTICE             => 'Notice',
					E_RECOVERABLE_ERROR  => 'Recoverable Error',
				);
				
				
				if(isset($php_errors[$code]))
				{
					// Use the human-readable error name
					$code = $php_errors[$code];
				}
			}
			
			if(Scaffold::$has_error)
			{
				$code = 'Compiler Error';
			}

			# Error view html
			ob_start();
			include Scaffold::find_file('scaffold/error.php', 'views');
			echo ob_get_clean();

			return true;
		}
		catch (Exception $e)
		{
			# Clean the output buffer if one exists
			ob_get_level() and ob_clean();

			# Display the exception text
			echo $e->getMessage();

			exit(1);
		}
	}
	
	/**
	 * Catches errors that are not caught by the error handler, such as E_PARSE.
	 *
	 * @uses    Scaffold::exception_handler
	 * @return  void
	 */
	public static function shutdown_handler()
	{
		if(!Scaffold::$init)
			return;
	
		if ($error = error_get_last() AND (error_reporting() & $error['type']))
		{
			# If an output buffer exists, clear it
			ob_get_level() and ob_clean();

			# Fake an exception for nice debugging
			Scaffold::exception_handler(new ErrorException($error['message'], $error['type'], 0, $error['file'], $error['line']));

			exit(1);
		}
	}

	/******************************************************************************************************
	 * Path Methods
	 *****************************************************************************************************/

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
			self::$include_paths[] = Scaffold::path($path);
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
	 * Takes a relative path, gets the full server path, removes
	 * the www root path, leaving only the url path to the file/folder
	 *
	 * @param $relative_path
	 */
	public static function url($path) 
	{
		return str_replace( Scaffold::root(), DIRECTORY_SEPARATOR, realpath($path) );
	}
	
	/**
	 * Cleans up a path so that it's in a format we can consistently use.
	 *
	 * @param $path
	 * @return string The complete server path
	 */
	public static function path($path)
	{
		return realpath(str_replace('\\', '/', $path)) . '/';
	}
	
	/**
	 * Gets or sets the document root
	 *
	 * @return string
	 */
	public static function root($path = false)
	{
		if($path !== false)
		{
			return self::$root = Scaffold::path($path);
		}
		
		if(!isset(self::$root))
		{
			# Try and get it for IIS or servers where it isn't set automatically
			if(!isset($_SERVER['DOCUMENT_ROOT']))
			{
				if(isset($_SERVER['SERVER_SOFTWARE']) && 0 === strpos($_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS/'))
				{
					$path_length = strlen($_SERVER['PATH_TRANSLATED']) - strlen($_SERVER['SCRIPT_NAME']);
					$path = substr($_SERVER['PATH_TRANSLATED'],0,path_length);   
				    $_SERVER['DOCUMENT_ROOT'] = rtrim($path, '\\');
				    
				    if ($unsetPathInfo) unset($_SERVER['PATH_INFO']);
				}
				else
				{
					Scaffold::error("Can't determine the document root");
				}
			}
			
			self::$root = $_SERVER['DOCUMENT_ROOT'];
		}
		
		return self::$root;
	}
	
	/******************************************************************************************************
	 * File and Directory Methods
	 *****************************************************************************************************/
	
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
		$search = $directory.DIRECTORY_SEPARATOR.$filename;
		
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
				self::error("Cannot find the file: " . str_replace($_SERVER['DOCUMENT_ROOT'], DIRECTORY_SEPARATOR, $search));
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
			$path = rtrim($path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

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
						
						$files[] = $item = str_replace('\\', DIRECTORY_SEPARATOR, $item);

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
	
	/******************************************************************************************************
	 * Utility Methods
	 *****************************************************************************************************/
	
	/**
	 * Removes inline comments
	 *
	 * @return return type
	 */
	public static function remove_inline_comments($css)
	{
		 return preg_replace('#(\s|$)//.*$#Umsi', '', $css);
	}
}