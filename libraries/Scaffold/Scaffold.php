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
	const VERSION = '2.0.8';
	
	# Server status codes
	const NOT_MODIFIED = 304;
	const SERVER_ERROR = 500;
	
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
	public static $output = null;
	
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
	 * Language extensions - properties, directives and functions
	 *
	 * @var array
	 */
	public static $extensions;
	
	/**
	 * If Scaffold encounted an error. You can check this variable to
	 * see if there were any errors when in_production is set to true.
	 *
	 * @var boolean
	 */
	public static $has_error = false;
	
	/**
	 * Parse the CSS. This takes an array of files, options and configs
	 * and parses the CSS, outputing the processed CSS string.
	 *
	 * @param string Path to the file to parse
	 * @param array Configuration options
	 * @param string Options
	 * @param boolean Return the CSS rather than displaying it
	 * @return string The processed css file as a string
	 */
	public static function parse($file,$config = false)
	{
		# Benchmark will do the entire run from start to finish
		Scaffold_Benchmark::start('system');

		try
		{
			# Make sure this file is allowed
			if(substr($file, 0, 4) == "http" OR substr($file, -4, 4) != ".css")
			{
				Scaffold::error("Scaffold cannot the requested file - $file");
			}

			# Setup the cache and other variables/constants
			if($config !== false)
				Scaffold::setup($config);
			
			# Find the file on the server
			$file = Scaffold::find_file($file, false, true);
			
			# The outputted file
			$output = Scaffold_Cache::path() . basename($file);

			# Before anything has happened, but the setup is done
			Scaffold::hook('post_setup');
			
			# Check if we should use the combined cache right now and skip unneeded processing
			if(SCAFFOLD_PRODUCTION === true AND Scaffold_Cache::exists($output) AND Scaffold_Cache::is_fresh($output))
			{
				$output = Scaffold_Cache::open($output);
			}
			
			# Nope, we need to reparse the file.
			else
			{
				# The time to process a single file
				Scaffold_Benchmark::start('system.file.' . basename($file));
				
				# While not in production, we want to to always recache, so we'll fake the time
				$modified = (SCAFFOLD_PRODUCTION) ? Scaffold_Cache::modified($output) : 0;
	
				# If the CSS file has been changed, or the cached version doesn't exist			
				if(!Scaffold_Cache::exists($output) OR $modified < filemtime($file))
				{
					# This will return the parsed CSS file
					$css = Scaffold::process($file);
					
					# Write it
					file_put_contents($output, $css);
					
					# Set its parmissions
					chmod($output, 0777);
					touch($output, time());
				}
				else
				{
					$css = file_get_contents($output);
				}
	
				Scaffold::$output = $css;
			
				/**
				 * Hook to modify what is sent to the browser
				 */
				if(SCAFFOLD_PRODUCTION === false) Scaffold::hook('display');
				
				# The time it's taken to process this file
				Scaffold_Benchmark::stop('system.file.' . basename($file));
			}

			/**
			 * Set the HTTP headers for the request. Scaffold will set
			 * all the headers required to score an A grade on YSlow. This
			 * means your CSS will be sent as quickly as possible to the browser.
			 */
			$lifetime = (SCAFFOLD_PRODUCTION === true) ? $config['cache_lifetime'] : 0;
			
			# Returns an array of headers
			$headers = Scaffold::headers($output,$lifetime);
			
			# We're sending not modified. Nothing should be sent to the browser.
			if($headers['_status'] == self::NOT_MODIFIED)
				Scaffold::$output = '';
			
			# Benchmark will do the entire run from start to finish
			Scaffold_Benchmark::stop('system');
		}
		
		/**
		 * If any errors were encountered
		 */
		catch( Exception $e )
		{
			/** 
			 * The message returned by the error 
			 */
			$message = $e->getMessage();
			
			/**
			 * Add the error header
			 */
			$headers['_status'] = self::SERVER_ERROR;
			
			/** 
			 * Load in the error view
			 */
			if(SCAFFOLD_PRODUCTION === false && $display === true)
			{
				Scaffold::send_headers();
				require Scaffold::find_file('scaffold_error.php','views');
			}
		}

		# Save the log to file
		if($config['enable_log'])
			Scaffold_Log::save();
		
		return self::$output = array
		(
			'status'  => self::$has_error,
		    'content' => self::$output,
		    'headers' => $headers,
		    'log'	  => Scaffold_Log::$log,
		);
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
		 * Define contstants for system paths for easier access.
		 */
		if(!defined('SCAFFOLD_SYSPATH'))
		{
			define('SCAFFOLD_SYSPATH', self::path($config['system']));
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
		 * Tell the cache where to save files and for how long to keep them for
		 */
		Scaffold_Cache::setup(Scaffold::path($config['cache']),$config['cache_lifetime']);
		
		/**
		 * The level at which logged messages will halt processing and be thrown as errors
		 */
		self::$error_threshold = $config['error_threshold'];

		/**
		 * Tell the log where to save it's files. Set it to automatically save the log on exit
		 */
		if($config['enable_log'] === true)
		{
			if(is_writable(SCAFFOLD_SYSPATH.'logs'))
			{
				Scaffold_Log::log_directory(SCAFFOLD_SYSPATH.'logs');	
			}
			else
			{
				Scaffold::error("Logs folder is not writable");
			}
		}

		/**
		 * Load each of the modules
		 */
		foreach(Scaffold::list_files(SCAFFOLD_SYSPATH.'modules') as $module)
		{
			$name = basename($module);
			$module_config = SCAFFOLD_SYSPATH.'config/' . $name . '.php';
			$default_config = $module . '/config.php';
			
			unset($config);
			
			if(file_exists($module_config))
			{
				include $module_config;				
				self::$config[$name] = $config;
			}
			elseif(file_exists($default_config))
			{
				include $default_config;				
				self::$config[$name] = $config;
			}

			self::add_include_path($module);
			
			if( $controller = Scaffold::find_file($name.'.php', false, true) )
			{
				require_once($controller);
				self::$modules[$name] = new $name;
			}
			
			# Load custom functions
			Scaffold::extensions($module);
		}

		# Load the extensions
		Scaffold::extensions(SCAFFOLD_SYSPATH);

		/**
		 * Module Initialization Hook
		 * This hook allows modules to load libraries and create events
		 * before any processing is done at all. 
		 */
		self::hook('initialize');
	}
	
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
		 * Replace custom functions
		 */
		foreach(self::$extensions['functions'] as $name => $values)
		{
			if($found = Scaffold::$css->find_functions($name))
			{
				// Make the list unique or not
				$originals = ($values['unique'] === false) ? array_unique($found[0]) : $found[0];
	
				// Loop through each found instance
				foreach($originals as $key => $value)
				{
					$result = call_user_func_array($values['callback'],explode(',',$found[2][$key]));
	
					// Run the user callback										
					if($result === false)
					{
						Scaffold::error('Invalid Custom Function Syntax - <strong>' . $originals[$key] . '</strong>');
					}
					
					// Just replace the first match if they are unique
					elseif($values['unique'] === true)
					{
						$pos = strpos(Scaffold::$css->string,$originals[$key]);
	
						if($pos !== false)
						{
						    Scaffold::$css->string = substr_replace(Scaffold::$css->string,$result,$pos,strlen($originals[$key]));
						}
					}
					else
					{
						Scaffold::$css->string = str_replace($originals[$key],$result,Scaffold::$css->string);
					}
				}
			}
		}
		
		/**
		 * Replace custom properties
		 */
		foreach(self::$extensions['properties'] as $name => $values)
		{
			if($found = Scaffold::$css->find_property($name))
			{
				$originals = array_unique($found[0]);
	
				foreach($originals as $key => $value)
				{
					$result = call_user_func($values['callback'],$found[2][$key]);
	
					if($result === false)
					{
						Scaffold::error('Invalid Custom Property Syntax - <strong>' . $originals[$key] . '</strong>');
					}
					
					Scaffold::$css->string = str_replace($originals[$key],$result,Scaffold::$css->string);
				}
			}
		}

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
	 * Allows modules to hook into the processing at any point
	 *
	 * @param $method The method to check for in each of the modules
	 * @return boolean
	 */
	public static function hook($method)
	{
		foreach(self::$modules as $module_name => $module)
		{
			if(method_exists($module,$method))
			{				
				call_user_func(array($module_name,$method));
			}
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
	
	/**
	 * Renders the CSS
	 *
	 * @param $output What to display
	 * @return void
	 */
	public static function render($content,$headers,$level = false)
	{
		if ($level AND ini_get('output_handler') !== 'ob_gzhandler' AND (int) ini_get('zlib.output_compression') === 0)
		{
			if ($level < 1 OR $level > 9)
			{
				# Normalize the level to be an integer between 1 and 9. This
				# step must be done to prevent gzencode from triggering an error
				$level = max(1, min($level, 9));
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

		if (isset($compress) AND $level > 0)
		{
			switch ($compress)
			{
				case 'gzip':
					# Compress output using gzip
					$content = gzencode($content, $level);
				break;
				case 'deflate':
					# Compress output using zlib (HTTP deflate)
					$content = gzdeflate($content, $level);
				break;
			}

			# This header must be sent with compressed content to prevent browser caches from breaking
			$output['headers']['Vary'] = 'Accept-Encoding';

			# Send the content encoding header
			$output['headers']['Content-Encoding'] = $compress;
		}
	
		# Send the headers
		Scaffold::send_headers($headers);
	
		echo $content; 
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
					elseif($value === self::SERVER_ERROR)
					{
						header('HTTP/1.1 500 Internal Server Error');
					}
				}
			}
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