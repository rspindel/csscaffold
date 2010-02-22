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
	 * The final output
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
	private static $find_file_paths = array();
	
	/**
	 * List of included modules. They are stored with the module name
	 * as the key, and the path to the module as the value. However,
	 * calling the modules method will return just the names of the modules.
	 *
	 * @var array
	 */
	public static $modules = array();
	
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
	 * Sets the initial variables, checks if we need to process the css
	 * and then sends whichever file to the browser.
	 *
	 * @return void
	 */
	public static function init($config) 
	{
		if(Scaffold::$init === true)
			return;

		/**
		 * Choose whether to show or hide errors
		 */
		if($config['display_errors'] === true)
		{	
			ini_set('display_errors', true);
			error_reporting(E_ALL | E_STRICT);
		}
		else
		{
			ini_set('display_errors', false);
			error_reporting(0);
		}

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
		 * If we're just in development mode, we'll want to always recache
		 * the file. Setting the cache_lifetime to 0 will force Scaffold
		 * to make sure it reparsed the file each time.
		 */
		if(SCAFFOLD_PRODUCTION === false)
		{
			$config['cache_lifetime'] = 0;
		}

		/**
		 * Tell the cache where to save files and for how long to keep them for
		 */
		Scaffold_Cache::setup(Scaffold::path($config['cache']),$config['cache_lifetime']);
		
		/**
		 * The level at which logged messages will halt processing and be thrown as errors
		 */
		self::$error_threshold = $config['error_threshold'];

		/**
		 * Get the singleton instance of the log
		 */
		Scaffold::$log = Scaffold_Log::instance();
		
		/**
		 * Tell the log where to save it's files.
		 */
		if($config['enable_log'] === true)
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

		# Load the modules
		Scaffold::modules(SCAFFOLD_SYSPATH);

		# Load the extensions
		Scaffold::extensions(SCAFFOLD_SYSPATH);
		
		# Scaffold is now ready to be used
		Scaffold::$init = true;
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
		// Transform the class name into a path
		$file = str_replace('_', '/', strtolower($class));

		if ($path = Scaffold::find_file('libraries', $file))
		{
			// Load the class file
			require $path;

			// Class has been found
			return TRUE;
		}

		// Class is not in the filesystem
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
	private function sort_functions($a,$b)
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
	 * Module Methods
	 *****************************************************************************************************/
	
	/**
	 * Loads the modules and returns the array
	 *
	 * @author your name
	 * @param $path
	 * @return array
	 */
	public static function modules($path)
	{
		foreach(Scaffold::list_files($path.'modules') as $module)
		{
			$name = basename($module);
			
			if( $controller = Scaffold::find_file($name.'.php', false, true) )
			{
				require_once($controller);
				self::$modules[$name] = new $name;
				
				$module_config = SCAFFOLD_SYSPATH.'config/' . $name . '.php';
				$default_config = $module . '/config.php';
				
				unset($config);
				
				if(file_exists($module_config))
				{
					include $module_config;				
				}
				elseif(file_exists($default_config))
				{
					include $default_config;				
				}
				
				self::$modules[$name]->config = $config; 
	
				self::add_include_path($module);
				
				self::$modules[$name]->init();
			}
			
			# Load custom functions
			Scaffold::extensions($module);
		}
	}

	/**
	 * Allows modules to hook into the processing at any point
	 *
	 * @param $method The method to check for in each of the modules
	 * @return boolean
	 */
	public static function hook($method,&$data = null)
	{
		foreach(self::$modules as $module_name => $module)
		{
			if(method_exists($module,$method))
			{
				self::$modules[$module_name]->$method($data);
			}
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
		if($headers['_status'] == self::NOT_MODIFIED)
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
	private static function headers($file,$lifetime)
	{		
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
		$headers['ETag'] 			= md5(serialize(array($length,$modified,$file)));
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
					if($value === self::NOT_MODIFIED)
					{
						header('Status: 304 Not Modified', TRUE, self::NOT_MODIFIED);
					}
				}
			}
		}
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
		Scaffold_Log::log($message,0);
		
		/**
		 * Useful variable to let other objects know there was an error with the parsing
		 */
		self::$has_error = true;
		
		/**
		 * This will be caught in the parse method
		 */
		throw new Exception($message);
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
	 * PHP error handler, converts all errors into ErrorExceptions. This handler
	 * respects error_reporting settings.
	 *
	 * @throws  ErrorException
	 * @author 	Kohana
	 * @return  TRUE
	 */
	public static function error_handler($code, $error, $file = NULL, $line = NULL)
	{
		if (error_reporting() & $code)
		{
			// This error is not suppressed by current error reporting settings
			// Convert the error into an ErrorException
			throw new ErrorException($error, $code, 0, $file, $line);
		}

		// Do not execute the PHP error handler
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
			// Get the exception information
			$type    = get_class($e);
			$code    = $e->getCode();
			$message = $e->getMessage();
			$file    = $e->getFile();
			$line    = $e->getLine();

			// Create a text version of the exception
			$error = Kohana::exception_text($e);

			if (is_object(Kohana::$log))
			{
				// Add this exception to the log
				Kohana::$log->add(Kohana::ERROR, $error);
			}

			if (Kohana::$is_cli)
			{
				// Just display the text of the exception
				echo "\n{$error}\n";

				return TRUE;
			}

			// Get the exception backtrace
			$trace = $e->getTrace();

			if ($e instanceof ErrorException)
			{
				if (isset(Kohana::$php_errors[$code]))
				{
					// Use the human-readable error name
					$code = Kohana::$php_errors[$code];
				}

				if (version_compare(PHP_VERSION, '5.3', '<'))
				{
					// Workaround for a bug in ErrorException::getTrace() that exists in
					// all PHP 5.2 versions. @see http://bugs.php.net/bug.php?id=45895
					for ($i = count($trace) - 1; $i > 0; --$i)
					{
						if (isset($trace[$i - 1]['args']))
						{
							// Re-position the args
							$trace[$i]['args'] = $trace[$i - 1]['args'];

							// Remove the args
							unset($trace[$i - 1]['args']);
						}
					}
				}
			}

			if ( ! headers_sent())
			{
				// Make sure the proper content type is sent with a 500 status
				header('Content-Type: text/html; charset='.Kohana::$charset, TRUE, 500);
			}

			// Start an output buffer
			ob_start();

			// Include the exception HTML
			include Kohana::find_file('views', 'kohana/error');

			// Display the contents of the output buffer
			echo ob_get_clean();

			return TRUE;
		}
		catch (Exception $e)
		{
			// Clean the output buffer if one exists
			ob_get_level() and ob_clean();

			// Display the exception text
			echo Kohana::exception_text($e), "\n";

			// Exit with an error status
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
		return self::reduce_double_slashes(str_replace( Scaffold::root(), DIRECTORY_SEPARATOR, realpath($path) ));
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