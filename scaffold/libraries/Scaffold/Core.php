<?php

/**
 * Controller
 *
 * This file handles the caching, logging, config, flags, options etc.
 * 
 * @package CSScaffold
 * @author Anthony Short
 */
class Scaffold_Core
{
	/**
	 * The config settings
	 */
	public static $config;
	
	/**
	 * Include paths
	 *
	 * @var array
	 */
	public static $include_paths = array();

	/**
	 * Logs
	 *
	 * @var array
	 */
	public static $log = array();
	
	/**
	 * The found file paths
	 *
	 * @var array
	 */
	private static $find_file_paths = null;
		
	/**
	 * Log Levels
	 *
	 * @var array
	 */
	private static $log_levels = array
	(
		'error',
		'warn',
		'info',
		'debug',
	);
	
	/**
	 * The log directory
	 *
	 * @var string
	 */
	public static $log_directory;
	
	/**
	 * The log threshold
	 *
	 * @var int
	 */
	private static $threshold = 2;
	
	/**
	 * The level of logged message to display as errors.
	 * 0 will only display error logs, 1 will display
	 * error logs and warning logs etc.
	 *
	 * @var int
	 */
	private static $error_level = 0;
	
	/**
	 * Displays an error and halts the parsing.
	 *	
	 * @param $message
	 * @return void
	 */
	public static function error($message)
	{
		self::log($message,0);
		self::log_save();

		if (!headers_sent())
		{
			header('HTTP/1.1 500 Internal Server Error');
		}

		include self::find_file('scaffold_error.php', 'views', true);
		exit;
	}

	/**
	 * Logs a message
	 *
	 * @param $message
	 * @param $level The severity of the log message
	 * @return void
	 */
	public static function log($message,$level = 4)
	{
		if ($level <= self::$threshold)
		{
			self::$log[] = array(date('Y-m-d H:i:s P'), $level, $message);
		}	
	}

	/**
	 * Sets the logging threshold
	 *
	 * @param $level
	 * @return void
	 */
	public static function log_threshold($level)
	{
		self::$threshold = $level;
	}

	/**
	 * Save all currently logged messages to a file.
	 *
	 * @return  void
	 */
	public static function log_save()
	{
		if (empty(self::$log) OR self::$threshold < 1)
			return;

		$filename = self::log_directory().date('Y-m-d').'.log.php';

		if (!is_file($filename))
		{
			touch($filename);
			chmod($filename, 0644);
		}

		// Messages to write
		$messages = array();
		$log = self::$log;

		do
		{
			list ($date, $type, $text) = array_shift($log);
			$messages[] = $date.' --- '.self::$log_levels[$type].': '.$text;
		}
		while (!empty($log));

		file_put_contents($filename, implode(PHP_EOL, $messages).PHP_EOL.PHP_EOL, FILE_APPEND);
	}

	/**
	 * Get or set the logging directory.
	 *
	 * @param   string  new log directory
	 * @return  string
	 */
	public static function log_directory($dir = NULL)
	{
		if (!empty($dir))
		{
			// Get the directory path
			$dir = Scaffold_Utils::fix_path($dir);

			if (is_dir($dir) AND is_writable($dir))
			{
				// Change the log directory
				self::$log_directory = $dir;
			}
			else
			{
				self::error("Can't write to log directory - {$dir}");
			}
		}
		
		if(isset(self::$log_directory))
		{
			return self::$log_directory;
		}
		else
		{
			echo "No log directory set";
			exit;
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

		# Prepare for loop
		$keys = explode('.', $keys);

		do 
		{
			// Get the next key
			$key = array_shift($keys);

			if (isset($array[$key]))
			{
				if (is_array($array[$key]) AND ! empty($keys))
				{
					# Dig down to prepare the next loop
					$array = $array[$key];
				}
				else
				{
					# Requested key was found
					return $array[$key];
				}
			}
			else
			{
				# Requested key is not set
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
			# Copy the array
			$array_copy = $array->getArrayCopy();

			# Is an object
			$array_object = TRUE;
		}
		else
		{
			if ( ! is_array($array))
			{
				# Must always be an array
				$array = (array) $array;
			}

			# Copy is a reference to the array
			$array_copy =& $array;
		}

		if (empty($keys))
			return $array;

		# Create keys
		$keys = explode('.', $keys);

		# Create reference to the array
		$row =& $array_copy;

		for ($i = 0, $end = count($keys) - 1; $i <= $end; $i++)
		{
			# Get the current key
			$key = $keys[$i];

			if ( ! isset($row[$key]))
			{
				if (isset($keys[$i + 1]))
				{
					# Make the value an array
					$row[$key] = array();
				}
				else
				{
					# Add the fill key
					$row[$key] = $fill;
				}
			}
			elseif (isset($keys[$i + 1]))
			{
				# Make the value an array
				$row[$key] = (array) $row[$key];
			}

			# Go down a level, creating a new row reference
			$row =& $row[$key];
		}

		if (isset($array_object))
		{
			# Swap the array back in
			$array->exchangeArray($array_copy);
		}
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
	 * Sets a config item, if allowed.
	 *
	 * @param   string   config key string
	 * @param   string   config value
	 * @return  boolean
	 */
	public static function config_set($key, $value = "")
	{
		if(is_array($key))
		{
			foreach($key as $k => $v)
			{
				self::config_set($k,$v);
			}
			
			return true;
		}
		
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
		
		return true;
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
		self::$internal_cache['output'] = ob_get_clean();
		
		if($return)
		{
			return self::$internal_cache['output'];
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
			{
				self::add_include_path($inc);
			}
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
}