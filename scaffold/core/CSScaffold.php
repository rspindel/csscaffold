<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * CSScaffold
 *
 * Handles all of the inner workings of the framework and juicy goodness.
 * This is where the metaphorical cogs of the system reside. 
 *
 * @package default
 * @author Anthony Short
 **/
final class CSScaffold 
{	 
	/**
	 * Holds the array of plugin objects
	 *
	 * @var array
	 */ 
	private static $plugins;
	
	/**
	 * Holds the array of module objects
	 *
	 * @var array
	 */ 
	private static $modules;
	
	/**
	 * What plugins have been loaded (Just their name)
	 *
	 * @var array
	 */ 
	private static $loaded = array();
	
	/**
	 * The configuration settings
	 */
	private static $configuration;
	
	/**
	 * Stores the user agent string
	 *
	 * @var string
	 */
	public static $user_agent;
	
	/**
	* The location of the cache file
	*
	* @var string
	**/
	private static $cached_file; 
	
	/**
	* The modified time of the cached file
	*
	* @var string
	**/
	private static $cached_mod_time;
	
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
	 * Sets the initial variables, checks if we need to process the css
	 * and then sends whichever file to the browser.
	 *
	 * @return void
	 * @author Anthony Short
	 **/
	public static function setup($url_params) 
	{
		# Load the config
		self::config_load(SYSPATH.'/config.php');
			
		# Recache is off by default
		$recache = false;
		
		# Set the user agent
		self::$user_agent = ( ! empty($_SERVER['HTTP_USER_AGENT']) ? trim($_SERVER['HTTP_USER_AGENT']) : '');
		
		# Define Scaffold error constant
		define('E_SCAFFOLD', 42);
		
		# Set error handler
		set_error_handler(array('CSScaffold', 'exception_handler'));
		
		# Set exception handler
		set_exception_handler(array('CSScaffold', 'exception_handler'));

		# Get rid of those pesky slashes
		$requested_file	= trim_slashes($url_params['request']);
		
		# Add our requested file var to the array
		$request['requested_file'] = $requested_file;
		
		# Full server path to the requested file
		$request['requested_file_path'] = join_path(DOCROOT,$requested_file);
		
		# Path to the file, relative to the css directory		
		$request['relative_file'] = substr($requested_file, strlen(CSSURL));
		
		# Path to the directory containing the file, relative to the css directory		
		$request['relative_dir'] = pathinfo($request['relative_file'], PATHINFO_DIRNAME);
		
		# If they've put a param in the url, consider it set to 'true'
		foreach($url_params as $key => $value)
		{
			if($value == "")
			{
				$url_params[$key] = true;
			}
		}
		
		# If the file doesn't exist
		if(!file_exists($request['requested_file_path']))
			throw new Scaffold_Exception("core.doesnt_exist", $request['requested_file']); 

		# or if it's not a css file
		if (!is_css($requested_file))
			throw new Scaffold_Exception("core.not_css", $requested_file);
		
		# or if the requested file wasn't from the css directory
		if(!substr(pathinfo($request['requested_file_path'], PATHINFO_DIRNAME), 0, strlen(CSSPATH)))
			throw new Scaffold_Exception("core.outside_css_directory");
		
		# Make sure the files/folders are writeable
		if (!is_dir(CACHEPATH) || !is_writable(CACHEPATH))
			throw new Scaffold_Exception("core.missing_cache", CACHEPATH);
		
		# Send it off to the config
		self::config_set($request);
		self::config_set($url_params);
					
		# Get the modified time of the CSS file
		self::config_set('requested_mod_time', filemtime(self::config('requested_file_path')));
			
		# Set the recache to true if needed		
		if(self::config('always_recache') OR isset($url_params['recache']))
			$recache = true;
		
		# Set it back to false if it's locked
		if(self::config('cache_lock') === true)
			$recache = false;
	
		# Prepare the cache, and tell it if we want to recache
		self::cache_set($recache);
		
		# Load the modules
		self::$modules = self::load_addons(read_dir(SYSPATH . "/modules"));
		
		# Load the plugins
		self::$plugins = self::load_addons(read_dir(SYSPATH . "/plugins"));
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
		$locale = self::config('language');

		if (!isset(self::$internal_cache['language'][$locale][$group]))
		{
			// Messages for this group
			$messages = array();

			include join_path(SYSPATH, 'language/', $locale, $group . '.php');

			// Merge in configuration
			if ( ! empty($lang) AND is_array($lang))
			{
				foreach ($lang as $k => $v)
				{
					$messages[$k] = $v;
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
	* Write to the set cache
	*
	* @return void
	* @author Anthony Short
	**/
	private static function cache_write($data)
	{	   	
	   	# Make sure the cache exists
		$cache_info = pathinfo(self::$cached_file);
		
		# Make the cache mimic the css directory
		if ($cache_info['dirname'] . "/" != CACHEPATH)
		{
			$path = CACHEPATH;
			$dirs = explode('/', self::config('relative_dir'));
						
			foreach ($dirs as $dir)
			{
				$path = join_path($path, $dir);
				
				if (!is_dir($path)) { mkdir($path, 0777); }
			}
		}	
		
		# Put it in the cache
		file_put_contents(self::$cached_file, $data, 0777);
		
		# Set its properties
		chmod(self::$cached_file, 0777);
		touch(self::$cached_file, time());
		
		# Set the config file mod time
		self::config_set('cached_mod_time', time());
	}

	/**
	* Set the cache file which will be used for this process
	*
	* @return boolean
	* @author Anthony Short
	**/
	private static function cache_set($recache = FALSE)
	{
		$checksum = "";
		
		if(self::$flags != null)
		{
			$checksum = "-" . implode("_", array_keys(self::$flags));
		}
		
		# Determine the name of the cache file
		$cached_file = join_path(CACHEPATH,preg_replace('#(.+)(\.css)$#i', "$1{$checksum}$2", self::config('relative_file')));
		
		# Save it
		self::$cached_file = $cached_file;
		
		# Check to see if we should delete the cache file
		if($recache === true && file_exists($cached_file))
		{
			# Empty out the cache
			self::cache_clear();
		}
		
		# When was the cache last modified
		if(file_exists(self::$cached_file))
		{
			$cached_mod_time =  (int) filemtime(self::$cached_file);
		}
		else
		{
			$cached_mod_time = 0;
		}
		
		self::config_set('cached_mod_time', $cached_mod_time);

		return TRUE;
	}
	
	/**
	* Empty the entire cache, removing every cached css file.
	*
	* @return void
	* @author Anthony Short
	**/
	private static function cache_clear($path = CACHEPATH)
	{	
		$f = read_dir($path);

		foreach($f as $file)
		{
			if(is_dir($file))
			{
				self::cache_clear($file);
				rmdir($file);
			}
			elseif(substr($file, -3) == 'css')
			{
				unlink($file);
			}
		}
	}

	/**
	 * Loads a config file into the global configuration
	 *
	 * @author Anthony Short
	 * @param $path
	 * @return boolean
	 */
	private function config_load($path, $sub_array = "")
	{
		require($path);

		# If the config file doesn't contain an array
		if(!isset($config) || !is_array($config))
			throw new Scaffold_Exception("core.missing_array", array($path));
		
		# Set the config values in our core config
		if($sub_array == "")
		{
			self::$configuration = $config;
		}
		else
		{
			self::$configuration[$sub_array] = $config;
		}
		
		# Remove the config array
		unset($config);
		
		return true;
	}

	/**
	 * Get a config item or group.
	 *
	 * @param   string  item name
	 * @param	string	group name
	 * @return  mixed
	 */
	public static function config($key, $group = "")
	{
		# If we're looking for a group
		if ($group != "")
		{
			if(isset(self::$configuration[$group]))
			{
				return self::$configuration[$group][$key];
			}
			else
			{
				false;
			}
		}
		
		# Otherwise return the normal item
		else
		{
			if(isset(self::$configuration[$key]))
			{
				return self::$configuration[$key];
			}
			else
			{
				return false;
			}
		}
	}

	/**
	 * Sets a configuration item, if allowed.
	 *
	 * @param   string   config key string
	 * @param   string   config value
	 * @return  boolean
	 */
	public static function config_set($key, $value = '')
	{		
		// Used for recursion
		$conf =& self::$configuration;
		
		# If they're both arrays
		if(is_array($key) && is_array($value))
		{
			foreach ($key as $k => $name)
			{
				$conf[$name] = $value[$k];
			}
		}
		
		# If we only gave it an array
		elseif(is_array($key) && $value == '')
		{
			foreach($key as $name => $val)
			{
				$conf[$name] = $val;
			}
		}
		
		# Otherwise, do it normally
		else
		{
			$conf[$key] = $value;
		}

		return true;
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
					$language_file = $plugin_folder . '/language/' . self::config('language') . ".php";
					
					# Load the config					
					if(file_exists($config))
					{
						self::config_load($config, $plugin_class);
					}
					
					# Load the language file
					if(file_exists($language_file))
					{
						require $language_file;
						self::$internal_cache['language'][self::config('language')][$plugin_class] = $lang;
						unset($lang);
					}
										
					if(class_exists($plugin_class))
					{				
						# Initialize the plugin
						$plugins[$plugin_class] = new $plugin_class();
						
						# Set the member paths
						$plugins[$plugin_class]->path['support'] 	= join_path($plugin_folder, 'support');
						$plugins[$plugin_class]->path['libraries'] 	= $library_path;
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
	public static function parse_css()
	{						
		# If the cache is stale or doesn't exist
		if (self::config('cached_mod_time') < self::config('requested_mod_time'))
		{
			# Start the timer
			Benchmark::start("parse_css");
			
			# Load the CSS file in the object
			CSS::load(file_get_contents(self::config('requested_file_path')));
			
			# Import CSS files
			Import::parse();
			
			# Import the mixins in the plugin folders
			$plugin_folders = read_dir(join_path(SYSPATH, 'plugins'));
			$module_folders = read_dir(join_path(SYSPATH, 'modules'));

			Mixins::import_mixins(array_merge($plugin_folders, $module_folders));
														
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
			
			# Replace the constants
			Constants::replace();
			
			# Parse @for loops
			For_loops::parse();
			
			# Compress it before parsing
			CSS::compress(CSS::$css);
			
			foreach(self::$plugins as $plugin)
			{
				$plugin->process();
			}
			
			# Parse the mixins
			Mixins::parse();
			
			# Find missing constants
			Constants::replace();
			
			# Compress it before parsing
			CSS::compress(CSS::$css);
			
			foreach(self::$plugins as $plugin)
			{
				$plugin->post_process();
			}
			
			# Parse the expressions
			Expression::parse();
			
			# Parse the nested selectors
			NestedSelectors::parse();
			
			# Add the extra string we've been storing
			CSS::$css .= CSS::$append;
			
			foreach(self::$plugins as $plugin)
			{
				$plugin->formatting_process();
			}
			
			# Stop the timer...
			Benchmark::stop("parse_css");
			
			if (self::config('show_header') === TRUE)
			{		
				CSS::$css  = "/* Processed by CSScaffold on ". gmdate('r') . " in ".Benchmark::get("parse_css", "time")." seconds */\n\n" . CSS::$css;
			}
			
			# Write the css file to the cache
			self::cache_write(CSS::$css);
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
		# Set the default headers
		header('Content-Type: text/css');
		header("Vary: User-Agent, Accept");
			
		if (
			isset($_SERVER['HTTP_IF_MODIFIED_SINCE'], $_SERVER['SERVER_PROTOCOL']) && 
			self::config('cached_mod_time') <= strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'])
		)
		{
			header("{$_SERVER['SERVER_PROTOCOL']} 304 Not Modified");
			exit;
		}
		else
		{			
			header('Last-Modified: '. gmdate('D, d M Y H:i:s', self::config('cached_mod_time')) .' GMT');
			echo file_get_contents(self::$cached_file);
			exit;
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
			if ($PHP_ERROR AND (error_reporting() & $exception) === 0)
				return;
				
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
			
			// Remove the DOCROOT from the path, as a security precaution
			$file = str_replace('\\', '/', realpath($file));
			$file = preg_replace('|^'.preg_quote(DOCROOT).'|', '', $file);

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
			
			require(SYSPATH . '/views/scaffold_error_page.php');

			# Turn off error reporting
			error_reporting(0);
			exit;
		}
		catch(Exception $e)
		{
			die('Fatal Error: '.$e->getMessage().' File: '.$e->getFile().' Line: '.$e->getLine());
		}
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
				$temp .= self::lang('core.error_file_line', preg_replace('!^'.preg_quote(DOCROOT).'!', '', $entry['file']), $entry['line']);
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
						$arg = preg_replace('!^'.preg_quote(DOCROOT).'!', '', $arg);
					}

					$temp .= $sep.htmlspecialchars((string)$arg, ENT_QUOTES, 'UTF-8');

					// Change separator to a comma
					$sep = ', ';
				}
			}

			$temp .= ' )</pre></li>';

			$output[] = $temp;
		}

		return '<ul class="backtrace">'.implode("\n", $output).'</ul>';
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
	protected $code = E_SCAFFOLD;

	/**
	 * Set exception message.
	 *
	 * @param  string  i18n language key for the message
	 * @param  array   addition line parameters
	 */
	public function __construct($error)
	{
		$args = array_slice(func_get_args(), 1);

		# Fetch the error message
		$message = CSScaffold::lang($error, $args);
		
		if ($message === $error OR empty($message))
		{
			# Unable to locate the message for the error
			$message = 'Unknown Exception: '.$error;
		}

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

/**
 * Scaffold_User_Exception
 *
 * Captures errors and displays them.
 * 
 * @author Anthony Short
 */
class Scaffold_User_Exception extends Scaffold_Exception
{
	/**
	 * Set exception message.
	 *
	 * @param $title string The name of the exception
	 * @param  array   addition line parameters
	 */
	public function __construct($title, $message)
	{
		Exception::__construct($message);

		$this->code = $title;
	}
}