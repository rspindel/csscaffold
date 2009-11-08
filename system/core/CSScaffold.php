<?php
/**
 * Class CSScaffold
 * @package CSScaffold
 */

require 'core/Utils.php';
require 'core/Benchmark.php';
require 'core/Module.php';
require 'core/CSS.php';

/**
 * CSScaffold
 *
 * Handles all of the inner workings of the framework and juicy goodness.
 * This is where the metaphorical cogs of the system reside. 
 *
 * Requires PHP 5.1.0.
 * Tested on PHP 5.2.
 *
 * @package CSScaffold
 * @author Anthony Short <anthonyshort@me.com>
 * @copyright 2009 Anthony Short. All rights reserved.
 * @license http://opensource.org/licenses/bsd-license.php  New BSD License
 * @link https://github.com/anthonyshort/csscaffold/master
 */
class CSScaffold 
{
	/**
	 * CSScaffold Version
	 */
	const VERSION = '1.5.0';

	/**
	 * The config settings
	 */
	private static $config;
	
	/**
	 * The location of the cache file
	 *
	 * @var string
	 */
	private static $cached_file; 
	
	/**
	 * Internal cache
	 */
	private static $internal_cache;
		
	/**
	 * Stores the flags
	 *
	 * @var array
	 */
	public static $flags;
	
	/**
	 * Include paths
	 *
	 * @var array
	 */
	private static $include_paths;
	
	/**
	 * Successfully loaded modules
	 *
	 * @var array
	 */
	private static $loaded_modules;
	 
	/**
	 * Sets the initial variables, checks if we need to process the css
	 * and then sends whichever file to the browser.
	 *
	 * @return void
	 * @author Anthony Short
	 **/
	public static function run($get, $config = array(), $path = array()) 
	{
		static $run;

		# This function can only be run once
		if ($run === TRUE)
			return;
		
		# The default options
		$default_config = array
		(
			'debug' 				=> false,
			'in_production' 		=> false,
			'force_recache' 		=> false,
			'show_header' 			=> true,
			'auto_include_mixins' 	=> true,
			'override_import' 		=> false,
			'absolute_urls' 		=> false,
			'use_css_constants' 	=> false,
			'minify_css' 			=> true,
			'constants' 			=> array(),
			'disabled_plugins' 		=> array()		
		);
		
		# Merge them with our set options
		$config = array_merge($default_config, $config);
		
		# The default paths
		$default_paths = array
		(
			'document_root' 		=> $_SERVER['DOCUMENT_ROOT'],
			'css' 					=> '../',
			'system' 				=> 'system',
			'cache' 				=> 'cache'
		);
		
		# Merge them with our set options
		$path = array_merge($default_paths, $path);
		
		# Set the options and paths in the config
		self::config_set('core', $config);

		# Set the paths in the config	
		self::config_set('core.path.docroot', fix_path($path['document_root']));
		self::config_set('core.path.system', fix_path($path['system']));
		self::config_set('core.path.cache', fix_path($path['cache']));
		self::config_set('core.path.css', fix_path($path['css']));
		self::config_set('core.url.css', str_replace(self::config('core.path.docroot'), '/', self::config('core.path.css')));
		self::config_set('core.url.system', str_replace(self::config('core.path.docroot'), '/', self::config('core.path.system')));
		
		# Load the include paths
		self::include_paths(TRUE);

		# Change into the system directory
		chdir(self::config('core.path.system'));
		
		# If we want to debug (turn on errors and FirePHP)
		if($config['debug'])
		{
			require 'vendor/FirePHPCore/fb.php';
			require 'vendor/FirePHPCore/FirePHP.class.php';
	
			# Set the error reporting level.
			error_reporting(E_ALL & ~E_STRICT);
			
			# Set error handler
			set_error_handler(array('CSScaffold', 'exception_handler'));
		
			# Set exception handler
			set_exception_handler(array('CSScaffold', 'exception_handler'));
			
			# Turn on FirePHP
			FB::setEnabled(true);
		}
		else
		{
			# Turn off errors
			error_reporting(0);
		}
		
		# Parse the $_GET['request'] and set it in the config
		self::config_set('core.request', self::parse_request($get['request']));
					
		# Get the modified time of the CSS file
		self::config_set('core.request.mod_time', filemtime(self::config('core.request.path')));

		# Tell CSScaffold where to cache and tell if we want to recache
		self::cache_set(self::config('core.path.cache'));
	
		# Set it back to false if it's locked
		if( $config['in_production'] AND file_exists(self::$cached_file) )
		{
			$recache = false;
		}
			
		# If we need to recache
		elseif( $config['force_recache'] OR isset($get['recache']) OR self::config('core.cache.mod_time') <= self::config('core.request.mod_time') )
		{
			$recache = true;
			self::cache_clear();
		}
		
		# Load the modules
		self::load_modules($config['disabled_plugins']);
		
		# Work in the same directory as the requested CSS file
		chdir(dirname(self::config('core.request.path')));
		
		# Create a new CSS object
		CSS::load(self::config('core.request.path'));
		
		# Parse it
		if($recache) self::parse_css();
		
		# Output it
		self::output_css(CSS::$css);
		
		# Setup is complete, prevent it from being run again
		$run = TRUE;
	}
	
	private static function parse_request($path)
	{
		# Get rid of those pesky slashes
		$requested_file	= trim_slashes($path);
		
		# Remove anything after .css - like /typography/
		$requested_file = preg_replace('/\.css(.*)$/', '.css', $requested_file);
		
		# Remove the start of the url if it exists (http://www.example.com)
		$requested_file = preg_replace('/https?\:\/\/[^\/]+/i', '', $requested_file);
		
		# Add our requested file var to the array
		$request['file'] = $requested_file;
		
		# Path to the file, relative to the css directory
		$request['relative_file'] = ltrim(str_replace(self::config('core.url.css'), '/', $requested_file), '/');

		# Path to the directory containing the file, relative to the css directory		
		$request['relative_dir'] = pathinfo($request['relative_file'], PATHINFO_DIRNAME);

		# Find the server path to the requested file
		if(file_exists(self::config('core.path.docroot').$requested_file))
		{
			# The request is sent with the absolute path most of the time
			$request['path'] = self::config('core.path.docroot').$requested_file;
		}
		else
		{
			# Otherwise we'll try to find it inside the CSS directory
			$request['path'] = self::find_file($request['relative_dir'] . '/', basename($requested_file, '.css'), FALSE, 'css');
		}
		
		# If the file doesn't exist
		if(!file_exists($request['path']))
			throw new Scaffold_Exception("Requested CSS file doesn't exist:" . $request['file']); 

		# or if it's not a css file
		if (!is_css($requested_file))
			throw new Scaffold_Exception("Requested file isn't a css file: $requested_file" );
		
		# or if the requested file wasn't from the css directory
		if(!substr(pathinfo($request['path'], PATHINFO_DIRNAME), 0, strlen(self::config('core.path.css'))))
			throw new Scaffold_Exception("Requested file wasn't within the CSS directory");
		
		return $request;
	}
	
	/**
	 * Displays nice backtrace information.
	 * @see http://php.net/debug_backtrace
	 *
	 * @param   array   backtrace generated by an exception or debug_backtrace
	 * @return  string
	 */
	public static function backtrace($trace)
	{
		if ( ! is_array($trace))
			return;

		// Final output
		$output = array();

		foreach ($trace as $entry)
		{
			$temp = '<li>';
			$temp .= '<pre>';
			
			if (isset($entry['file']))
			{
				$file = preg_replace('!^'.preg_quote(self::config('core.path.docroot')).'!', '', $entry['file']);
				$line = (string)$entry['line'];

				$temp .= "<tt>{$file}<strong>[{$line}]:</strong></tt>";
			}

			if (isset($entry['class']))
			{
				// Add class and call type
				$temp .= $entry['class'].$entry['type'];
			}

			// Add function
			$temp .= $entry['function'].'( ';

			// Add function args
			if (isset($entry['args']) AND is_array($entry['args']))
			{
				// Separator starts as nothing
				$sep = '';

				while ($arg = array_shift($entry['args']))
				{
					if (is_string($arg) AND is_file($arg))
					{
						// Remove docroot from filename
						$arg = preg_replace('!^'.preg_quote(self::config('core.path.docroot')).'!', '', $arg);
					}

					//$temp .= $sep.htmlspecialchars((string)$arg, ENT_QUOTES, 'UTF-8');

					// Change separator to a comma
					$sep = ', ';
				}
			}

			$temp .= ' )</pre></li>';

			$output[] = $temp;
		}

		return '<ul class="backtrace">'.implode("\n", $output).'</ul>';
	}
	
	/**
	 * Empty the entire cache, removing every cached css file.
	 *
	 * @return void
	 * @author Anthony Short
	 */
	private static function cache_clear($path = "")
	{
		if($path == "")
			$path = self::config('core.path.cache');
			
		$path .= "/";

		foreach(scandir($path) as $file)
		{
			if($file[0] == ".")
			{
				continue;
			}
			elseif(is_dir($path.$file))
			{
				self::cache_clear($path.$file);
				rmdir($path.$file);
			}
			elseif(file_exists($path.$file))
			{
				unlink($path.$file);
			}
		}
	}
	
	/**
	 * Set the cache file which will be used for this process
	 *
	 * @return boolean
	 * @author Anthony Short
	 */
	private static function cache_set($path)
	{
		$checksum = "";
		$cached_mod_time = 0;
		
		# Make sure the files/folders are writeable
		if (!is_dir($path))
			throw new Scaffold_Exception("Cache path does not exist.");
			
		if (!is_writable($path))
			throw new Scaffold_Exception("Cache path is not writable.");
		
		if(self::$flags != null)
		{
			$checksum = "-" . implode("_", array_keys(self::$flags));
		}

		# Determine the name of the cache file
		self::$cached_file = join_path($path,preg_replace('#(.+)(\.css)$#i', "$1{$checksum}$2", self::config('core.request.relative_file')));

		if(file_exists(self::$cached_file))
		{
			# When was the cache last modified
			$cached_mod_time =  (int) filemtime(self::$cached_file);
		}
		
		self::config_set('core.cache.mod_time', $cached_mod_time);
	}

	/**
	 * Write to the set cache
	 *
	 * @return void
	 * @author Anthony Short
	 */
	public static function cache_write($data,$target)
	{
		# Create the cache file
		self::cache_create(dirname($target));
		
		# Put it in the cache
		file_put_contents($target, $data);
		
		# Set its parmissions
		chmod($target, 0777);
		touch($target, time());
	}
	
	/**
	 * Create the cache file directory
	 */
	public static function cache_create($path)
	{
		# Create the directory to write the file to	
		if(!is_dir($path)) 
		{ 
			mkdir($path); 
			chmod($path, 0777); 
		}
	}

	/**
	 * Get a config item or group.
	 *
	 * @param   string   item name
	 * @param   boolean  force a forward slash (/) at the end of the item
	 * @param   boolean  is the item required?
	 * @return  mixed
	 */
	public static function config($key, $slash = FALSE, $required = FALSE)
	{
		// Get the group name from the key
		$group = explode('.', $key, 2);
		$group = $group[0];

		if ( ! isset(self::$config[$group]) && $group != "core")
		{
			// Load the config group
			self::$config[$group] = self::config_load($group, $required);
		}

		// Get the value of the key string
		$value = self::key_string(self::$config, $key);

		if ($slash === TRUE AND is_string($value) AND $value !== '')
		{
			// Force the value to end with "/"
			$value = rtrim($value, '/').'/';
		}

		return $value;
	}

	/**
	 * Clears a config group from the cached config.
	 *
	 * @param   string  config group
	 * @return  void
	 */
	public static function config_clear($group)
	{
		// Remove the group from config
		unset(self::$config[$group], self::$internal_cache['config'][$group]);
	}

	/**
	 * Load a config file.
	 *
	 * @param   string   config filename, without extension
	 * @param   boolean  is the file required?
	 * @return  array
	 */
	public static function config_load($name, $required = TRUE)
	{
		if (isset(self::$internal_cache['config'][$name]))
			return self::$internal_cache['config'][$name];

		// Load matching configs
		$config = array();

		if ($files = self::find_file('config', $name, $required))
		{
			foreach ($files as $file)
			{
				require $file;

				if (isset($config) AND is_array($config))
				{
					// Merge in config
					$config = array_merge($config, $config);
				}
			}
		}

		return self::$internal_cache['config'][$name] = $config;
	}
	
	/**
	 * Sets a config item, if allowed.
	 *
	 * @param   string   config key string
	 * @param   string   config value
	 * @return  boolean
	 */
	public static function config_set($key, $value)
	{
		// Do this to make sure that the config array is already loaded
		self::config($key);

		// Convert dot-noted key string to an array
		$keys = explode('.', $key);

		// Used for recursion
		$conf =& self::$config;
		$last = count($keys) - 1;

		foreach ($keys as $i => $k)
		{
			if ($i === $last)
			{
				$conf[$k] = $value;
			}
			else
			{
				$conf =& $conf[$k];
			}
		}
		
		if ($key === 'core.modules')
		{
			// Reprocess the include paths
			self::include_paths(TRUE);
		}

		return TRUE;
	}

	/**
	 * Find a resource file in a given directory. Files will be located according
	 * to the order of the include paths. config and i18n files will be
	 * returned in reverse order.
	 *
	 * @throws  Kohana_Exception  if file is required and not found
	 * @param   string   directory to search in
	 * @param   string   filename to look for (without extension)
	 * @param   boolean  file required
	 * @param   string   file extension
	 * @return  array    if the type is config, i18n or l10n
	 * @return  string   if the file is found
	 * @return  FALSE    if the file is not found
	 */
	public static function find_file($directory, $filename, $required = FALSE, $ext = FALSE)
	{
		// NOTE: This test MUST be not be a strict comparison (===), or empty
		// extensions will be allowed!
		if ($ext == '')
		{
			// Use the default extension
			$ext = '.php';
		}
		else
		{
			// Add a period before the extension
			$ext = '.'.$ext;
		}

		// Search path
		$search = $directory.'/'.$filename.$ext;
		
		if (isset(self::$internal_cache['find_file_paths'][$search]))
			return self::$internal_cache['find_file_paths'][$search];

		// Load include paths
		$paths = self::$include_paths;

		// Nothing found, yet
		$found = NULL;

		if ($directory === 'config')
		{
			// Search in reverse, for merging
			$paths = array_reverse($paths);

			foreach ($paths as $path)
			{
				if (is_file($path.$search))
				{
					// A matching file has been found
					$found[] = $path.$search;
				}
			}
		}
		elseif(in_array($directory, $paths))
		{
			if (is_file($directory.$filename.$ext))
			{
				// A matching file has been found
				$found = $path.$search;

				// Stop searching
				break;
			}
		}
		else
		{
			foreach ($paths as $path)
			{
				if (is_file($path.$search))
				{
					// A matching file has been found
					$found = $path.$search;

					// Stop searching
					break;
				}
			}
		}

		if ($found === NULL)
		{
			if ($required === TRUE)
			{
				// If the file is required, throw an exception
				throw new Scaffold_Exception("Cannot locate the resource: " . $directory . $filename . $ext);
			}
			else
			{
				// Nothing was found, return FALSE
				$found = FALSE;
			}
		}

		return self::$internal_cache['find_file_paths'][$search] = $found;
	}

	/**
	 * Sets a cache flag
	 *
	 * @author Anthony Short
	 * @param $flag_name
	 * @return null
	 */
	public static function flag($flag_name)
	{
		self::$flags[$flag_name] = true;
	}

	/**
	 * Get all include paths. APPPATH is the first path, followed by module
	 * paths in the order they are configured, follow by the self::config('core.path.system').
	 *
	 * @param   boolean  re-process the include paths
	 * @return  array
	 */
	public static function include_paths($process = FALSE)
	{
		if ($process === TRUE)
		{
			// Add APPPATH as the first path
			self::$include_paths = array
			(
				self::config('core.path.css'),
				self::config('core.path.system') . 'modules/'
			);
			
			# Find the modules and plugins installed	
			$modules = self::list_files('modules', FALSE, self::config('core.path.system') . '/modules');
			
			foreach ($modules as $path)
			{
				$path = str_replace('\\', '/', realpath($path));
				
				if (is_dir($path))
				{
					// Add a valid path
					self::$include_paths[] = $path.'/';
				}
			}

			# Add self::config('core.path.system') as the last path
			self::$include_paths[] = self::config('core.path.system');
			self::$include_paths[] = SCAFFOLD_DIR.'/';
		}

		return self::$include_paths;
	}

	/**
	 * Returns the value of a key, defined by a 'dot-noted' string, from an array.
	 *
	 * @param   array   array to search
	 * @param   string  dot-noted string: foo.bar.baz
	 * @return  string  if the key is found
	 * @return  void    if the key is not found
	 */
	public static function key_string($array, $keys)
	{
		if (empty($array))
			return NULL;

		// Prepare for loop
		$keys = explode('.', $keys);

		do 
		{
			// Get the next key
			$key = array_shift($keys);

			if (isset($array[$key]))
			{
				if (is_array($array[$key]) AND ! empty($keys))
				{
					// Dig down to prepare the next loop
					$array = $array[$key];
				}
				else
				{
					// Requested key was found
					return $array[$key];
				}
			}
			else
			{
				// Requested key is not set
				break;
			}
		}
		while ( ! empty($keys));

		return NULL;
	}

	/**
	 * Sets values in an array by using a 'dot-noted' string.
	 *
	 * @param   array   array to set keys in (reference)
	 * @param   string  dot-noted string: foo.bar.baz
	 * @return  mixed   fill value for the key
	 * @return  void
	 */
	public static function key_string_set( & $array, $keys, $fill = NULL)
	{
		if (is_object($array) AND ($array instanceof ArrayObject))
		{
			// Copy the array
			$array_copy = $array->getArrayCopy();

			// Is an object
			$array_object = TRUE;
		}
		else
		{
			if ( ! is_array($array))
			{
				// Must always be an array
				$array = (array) $array;
			}

			// Copy is a reference to the array
			$array_copy =& $array;
		}

		if (empty($keys))
			return $array;

		// Create keys
		$keys = explode('.', $keys);

		// Create reference to the array
		$row =& $array_copy;

		for ($i = 0, $end = count($keys) - 1; $i <= $end; $i++)
		{
			// Get the current key
			$key = $keys[$i];

			if ( ! isset($row[$key]))
			{
				if (isset($keys[$i + 1]))
				{
					// Make the value an array
					$row[$key] = array();
				}
				else
				{
					// Add the fill key
					$row[$key] = $fill;
				}
			}
			elseif (isset($keys[$i + 1]))
			{
				// Make the value an array
				$row[$key] = (array) $row[$key];
			}

			// Go down a level, creating a new row reference
			$row =& $row[$key];
		}

		if (isset($array_object))
		{
			// Swap the array back in
			$array->exchangeArray($array_copy);
		}
	}
	
	/**
	 * Fetch a language item.
	 *
	 * @param   string  language key to fetch
	 * @param   array   additional information to insert into the line
	 * @return  string  i18n language string, or the requested key if the i18n item is not found
	 */
	public static function lang($key, $args = NULL)
	{
		# Extract the main group from the key
		$keys = explode('.', $key, 2);
		$group = $keys[0];

		// Get locale name
		$locale = self::config('core.language');

		if (!isset(self::$internal_cache['language'][$locale][$group]))
		{
			// Messages for this group
			$messages = array();

			if ($files = self::find_file("language", "$locale/$group"))
			{
				foreach ($files as $file)
				{
					include $file;

					// Merge in config
					if ( ! empty($lang) AND is_array($lang))
					{
						foreach ($lang as $k => $v)
						{
							$messages[$k] = $v;
						}
					}
				}
			}
					
			self::$internal_cache['language'][$locale][$group] = $messages;
		}
	
		if(isset($keys[1]))
		{
			# Get the line from cache
			$line = self::$internal_cache['language'][$locale][$group][$keys[1]];
		}
		else
		{
			$line = self::$internal_cache['language'][$locale][$group];
		}

		if ($line === NULL)
		{
			# Return the key string as fallback
			return $key;
		}
		
		# Add extra text to the message
		if (is_string($line) AND func_num_args() > 1)
		{
			$args = array_slice(func_get_args(), 1);

			# Add the arguments into the line
			$line = vsprintf($line, is_array($args[0]) ? $args[0] : $args);
		}

		return $line;
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

	/**
	 * Loads modules and plugins
	 *
	 * @param $addons An array of addon names
	 * @param $directory The directory to look for these addons in
	 * @return void
	 */
	private static function load_modules($disabled = array())
	{
		# Get each of the folders inside the Plugins and Modules directories
		$modules = self::list_files('modules');
		
		foreach($modules as $module)
		{
			$name = basename($module);
			
			if(in_array($name, $disabled))
			{
				continue;
			}
			
			# The addon folder
			$folder = $module;
					
			# The controller for the plugin (Optional)
			$controller = join_path($folder,$name.'.php');

			# The config file for the plugin (Optional)
			$config_file = $folder.'/config.php';
			
			# Set the paths in the config
			self::config_set("$name.support", join_path($folder,'support'));
			self::config_set("$name.libraries", join_path($folder,'libraries'));

			# Include the addon controller
			if(file_exists($controller))
			{
				require_once($controller);
				
				# Any flags this module sets
				call_user_func(array($name,'flag'));
				
				# It's loaded
				self::$loaded_modules[] = $name;
			}
			
			# If there is a config file
			if(file_exists($config_file))
			{
				include $config_file;
				
				foreach($config as $key => $value)
				{
					self::config_set($name.'.'.$key, $value);
				}
				
				unset($config);
			}
		}
	}
	
	/**
	 * Loads a view file and returns it
	 */
	public static function load_view($view)
	{
		if ($view == '')
				return;
		
		# Find the view file
		$view = self::find_file('views/', $view, true);
	
		# Buffering on
		ob_start();
	
		# Views are straight HTML pages with embedded PHP, so importing them
		# this way insures that $this can be accessed as if the user was in
		# the controller, which gives the easiest access to libraries in views
		try
		{
			include $view;
		}
		catch (Exception $e)
		{
			ob_end_clean();
			throw $e;
		}
	
		# Fetch the output and close the buffer
		return ob_get_clean();
	}

	/**
	 * Output the CSS to the browser
	 *
	 * @return void
	 * @author Anthony Short
	 */
	public static function output_css()
	{	
		if (
			isset($_SERVER['HTTP_IF_MODIFIED_SINCE'], $_SERVER['SERVER_PROTOCOL']) && 
			self::config('core.cache.mod_time') <= strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'])
		)
		{
			header("{$_SERVER['SERVER_PROTOCOL']} 304 Not Modified");
			exit;
		}
		else
		{
			# Set the default headers
			header('Content-Type: text/css');
			header("Vary: User-Agent, Accept");
			header('Last-Modified: '. gmdate('D, d M Y H:i:s', self::config('core.cache.mod_time')) .' GMT');

			echo file_get_contents(self::$cached_file);
			exit;
		}
	}
	
	/**
	 * Parse the CSS
	 *
	 * @return string - The processes css file as a string
	 * @author Anthony Short
	 */
	public static function parse_css()
	{								
		# Start the timer
		Benchmark::start("parse_css");
		
		# Compress it before parsing
		CSS::compress(CSS::$css);
		
		# Import CSS files
		if(class_exists('Import'))
			Import::parse();
		
		if(self::config('core.auto_include_mixins') === true && class_exists('Mixins'))
		{
			# Import the mixins in the plugin/module folders
			Mixins::import_mixins('framework/mixins');
		}
													
		# Parse our css through the plugins
		foreach(self::$loaded_modules as $module)
		{
			call_user_func(array($module,'import_process'));
		}
		
		# Compress it before parsing
		CSS::compress(CSS::$css);

		# Parse the constants
		if(class_exists('Constants'))
			Constants::parse();

		foreach(self::$loaded_modules as $module)
		{
			call_user_func(array($module,'pre_process'));
		}
		
		# Parse the @grid
		if(class_exists('Layout'))
			Layout::parse_grid();
		
		# Replace the constants
		if(class_exists('Constants'))
			Constants::replace();
		
		# Parse @for loops
		if(class_exists('Iteration'))
			Iteration::parse();
		
		foreach(self::$loaded_modules as $module)
		{
			call_user_func(array($module,'process'));
		}
		
		# Compress it before parsing
		CSS::compress(CSS::$css);
		
		# Parse the mixins
		if(class_exists('Mixins'))
			Mixins::parse();
		
		# Find missing constants
		if(class_exists('Constants'))
			Constants::replace();
		
		# Compress it before parsing
		CSS::compress(CSS::$css);
		
		foreach(self::$loaded_modules as $module)
		{
			call_user_func(array($module,'post_process'));
		}
		
		# Parse the expressions
		if(class_exists('Expression'))
			Expression::parse();
		
		# Parse the nested selectors
		if(class_exists('NestedSelectors'))
			NestedSelectors::parse();
		
		# Convert all url()'s to absolute paths if required
		if(self::config('core.absolute_urls') === true)
		{
			CSS::convert_to_absolute_urls();
		}
		
		# Replaces url()'s that start with ~ to lead to the CSS directory
		CSS::replace_css_urls();
		
		# Add the extra string we've been storing
		CSS::$css .= CSS::$append;
		
		# If they want to minify it
		if(self::config('core.minify_css') === true && class_exists('Minify'))
		{
			Minify::compress();
		}
		
		# Otherwise, we'll make it pretty
		else
		{
			CSS::pretty();
		}
		
		# Formatting hook
		foreach(self::$loaded_modules as $module)
		{
			call_user_func(array($module,'formatting_process'));
		}
		
		# Validate the CSS
		if(class_exists('Validate'))
			Validate::check();
		
		# Stop the timer...
		Benchmark::stop("parse_css");
		
		if (self::config('core.show_header') === TRUE)
		{		
			CSS::$css  = "/* Processed by CSScaffold on ". gmdate('r') . " in ".Benchmark::get("parse_css", "time")." seconds */\n\n" . CSS::$css;
		}

		# Write the css file to the cache
		self::cache_write(CSS::$css,self::$cached_file);

		# Output process hook for plugins to display views.
		# Doesn't run in production mode.
		if(self::config('core.in_production') === false)
		{				
			foreach(self::$loaded_modules as $module)
			{
				call_user_func(array($module,'output'));
			}
		}
	}

	/**
	 * Handles Exceptions
	 *
	 * @param   integer|object  exception object or error code
	 * @param   string          error message
	 * @param   string          filename
	 * @param   integer         line number
	 * @return  void
	 */
	public static function exception_handler($exception, $message = NULL, $file = NULL, $line = NULL)
	{
		try
		{
			# PHP errors have 5 args, always
			$PHP_ERROR = (func_num_args() === 5);
	
			# Test to see if errors should be displayed
			if ($PHP_ERROR AND error_reporting() === 0)
				die;
				
			# Error handling will use exactly 5 args, every time
			if ($PHP_ERROR)
			{
				$code     = $exception;
				$type     = 'PHP Error';
			}
			else
			{
				$code     = $exception->getCode();
				$type     = get_class($exception);
				$message  = $exception->getMessage();
				$file     = $exception->getFile();
				$line     = $exception->getLine();
			}

			if(is_numeric($code))
			{
				$codes = self::lang('errors');
	
				if (!empty($codes[$code]))
				{
					list($level, $error, $description) = $codes[$code];
				}
				else
				{
					$level = 1;
					$error = $PHP_ERROR ? 'Unknown Error' : get_class($exception);
					$description = '';
				}
			}
			else
			{
				// Custom error message, this will never be logged
				$level = 5;
				$error = $code;
				$description = '';
			}
			
			// Remove the self::config('core.path.docroot') from the path, as a security precaution
			$file = str_replace('\\', '/', realpath($file));
			$file = preg_replace('|^'.preg_quote(self::config('core.path.docroot')).'|', '', $file);

			if($PHP_ERROR)
			{
				$description = 'An error has occurred which has stopped Scaffold';
	
				if (!headers_sent())
				{
					# Send the 500 header
					header('HTTP/1.1 500 Internal Server Error');
				}
			}
			else
			{
				if (method_exists($exception, 'sendHeaders') AND !headers_sent())
				{
					# Send the headers if they have not already been sent
					$exception->sendHeaders();
				}
			}
			
			if ($line != FALSE)
			{
				// Remove the first entry of debug_backtrace(), it is the exception_handler call
				$trace = $PHP_ERROR ? array_slice(debug_backtrace(), 1) : $exception->getTrace();

				// Beautify backtrace
				$trace = self::backtrace($trace);
				
			}
			
			# Log to FirePHP
			FB::log($error . "-" . $message);
			
			require self::config('core.path.system') . 'views/Scaffold_Exception.php';

			# Turn off error reporting
			error_reporting(0);
			exit;
		}
		catch(Exception $e)
		{
			die('Fatal Error: '.$e->getMessage().' File: '.$e->getFile().' Line: '.$e->getLine());
		}
	}

}

/**
 * Creates a generic exception.
 */
class Scaffold_Exception extends Exception 
{
	# Header
	protected $header = FALSE;

	# Scaffold error code
	protected $code = 42;

	/**
	 * Set exception message.
	 *
	 * @param  string  i18n language key for the message
	 * @param  array   addition line parameters
	 */
	public function __construct($message)
	{
		# Sets $this->message the proper way
		parent::__construct($message);
	}

	/**
	 * Magic method for converting an object to a string.
	 *
	 * @return  string  i18n message
	 */
	public function __toString()
	{
		return (string) $this->message;
	}

	/**
	 * Sends an Internal Server Error header.
	 *
	 * @return  void
	 */
	public function sendHeaders()
	{
		// Send the 500 header
		header('HTTP/1.1 500 Internal Server Error');
	}
}