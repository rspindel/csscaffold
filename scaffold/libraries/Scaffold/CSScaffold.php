<?php

/**
 * CSScaffold
 *
 * Handles all of the inner workings of the framework and juicy goodness.
 * This is where the metaphorical cogs of the system reside. 
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

class CSScaffold extends Scaffold_Core
{
	/**
	 * CSScaffold Version. Two point oh!
	 */
	const VERSION = '2.0.0';

	/**
	 * The current file being processed
	 *
	 * @var array
	 */
	public static $current = array();
	
	/**
	 * Lists of included modules
	 *
	 * @var array
	 */
	public static $modules = null;

	/**
	 * Stores the flags
	 *
	 * @var array
	 */
	public static $flags = array();

	/**
	 * Options
	 *
	 * @var array
	 */
	public static $options = array();
	
	/**
	 * If Scaffold encounted an error
	 *
	 * @var boolean
	 */
	public static $has_error = false;
	
	/**
	 * Stores the headers for sending to the browser
	 *
	 * @var array
	 */
	private static $headers = array();

	/**
	 * Sets the initial variables, checks if we need to process the css
	 * and then sends whichever file to the browser.
	 *
	 * @return void
	 */
	public static function setup( $config ) 
	{
		self::$config =& $config;

		# Set the errors to display
		if($config['in_production'] === false)
		{	
			ini_set('display_errors', TRUE);
			error_reporting(E_ALL & ~E_STRICT);
		}
		else
		{
			error_reporting(0);
		}
		
		# Get the full paths
		$config['system'] = Scaffold_Utils::fix_path($config['system']);
		$config['cache'] = Scaffold_Utils::fix_path($config['cache']);
		
		# Prepare the logger
		self::log_threshold( $config['log_threshold'] );
		self::log_directory( $config['system'] . 'logs/' );

		# Set the current cache path
		$cache = new Scaffold_Cache( $config['cache'], $config['cache_lifetime'], $config['in_production'] );
		
		# We'll try and load everything we can from the cache
		if( $config['in_production'] === true )
		{
			self::$modules 			= $cache->temp('modules.txt');
			self::$include_paths 	= $cache->temp('include_paths.txt');
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
			$cache->write(self::$modules,'scaffold_modules.txt');
			$cache->write(self::$include_paths,'scaffold_include_paths.txt');
		}
		else
		{	
			foreach(self::$modules as $module)
				require_once $module;
		}
		
		return true;
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
				header($name . ':' . $value);
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
		return Scaffold_Utils::reduce_double_slashes(str_replace( $_SERVER['DOCUMENT_ROOT'], '/', realpath($path) ));
	}

	/**
	 * Parse the CSS
	 *
	 * @param array List of files
	 * @param string Base directory for files
	 * @param string Alterations to the config set in the setup.
	 * @return string - The processes css file as a string
	 */
	public static function parse( $files, $options = array(), $return = false )
	{
		# Get rid of duplicate file requests
		$files = array_unique($files);
		
		# Options
		self::$options = $options;
		
		# Cache
		$cache_folder = self::config('cache') . md5(serialize($files)) . '/';
		
		if(!is_dir($cache_folder))
		{
			mkdir($cache_folder);
			chmod($cache_folder, 0777);
		}
		
		$cache = new Scaffold_Cache($cache_folder,self::config('cache_lifetime'),self::config('in_production'));

		# Get the cached files
		if(self::config('in_production') === true)
		{
			$output = $cache->temp('scaffold_output');
			
			if(isset($output))
			{
				return self::output($cache->find($output),$return);
			}
		}
		
		# Flags
		$flags = self::flags();
		
		# Combined CSS file
		$combined = md5(serialize(array($files,$flags))) . '.css';
	
		# Loop through each file and test them for changes before combining.
		foreach($files as $file)
		{
			# If it's a url
			if( substr($file, 0, 4) == "http")
				CSScaffold::error('Scaffold cannot parse CSS files sent as URLs - ' . $file);

			# Find the CSS file
			$request = self::find_file($file, false, true);

			# Find the name of the we need to create in the cache directory.
			$cached_file = md5(serialize(array($request,$flags))) . '.css';
			
			if (!Scaffold_Utils::is_css($file))
				self::error("Requested file isn't a css file: $file" );

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
		if( $cache->fetch($combined) === null )
		{
			$cache->write(implode('',$join),$combined);
		}

		$cache->write($combined,'scaffold_output');

		return self::output($cache->find($combined),$return);
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
		{
			call_user_func(array($module,'flag'));
		}
		
		if(isset(self::$flags))
		{
			return self::$flags;
		}

		return false;
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
			{
				continue;
			}
			
			# Add this module folder to the include paths
			self::add_include_path($module);

			# The config file for the plugin (Optional)
			$config_file = $module.'/config.php';
			
			if(!isset(self::$config[$module]))
			{
				# If there is a config file
				if(file_exists($config_file))
				{
					include $config_file;
					
					foreach($config as $key => $value)
					{
						self::$config[$name][$key] = $value;
					}
					
					unset($config);
				}
			}
			
			# Include the addon controller
			if( $controller = self::find_file($name.'.php', false, true) )
			{
				require_once($controller);
				
				# It's loaded
				self::$modules[$name] = $controller;
			}
		}
		
		return array_keys(self::$modules);
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

		# Handy info for some modules
		$dir = Scaffold_Utils::fix_path(dirname($file));
		
		self::$current = array
		(
			'file' => $file,
			'path' => $dir,
			'url' => self::url_path($dir)
		);
		
		$css = file_get_contents($file);

		if(class_exists('Import'))
			$css = Import::parse($css);
		
		/* --------------------------------------------------------
		
		Import Process Hook
		
		---------------------------------------------------------- */
													
		foreach(self::modules() as $module)
		{
			$css = call_user_func( array($module,'import_process'), $css);
		}

		if(class_exists('Constants'))
			$css = Constants::parse($css);

		/* --------------------------------------------------------
		
		Pre-process Hook
		
		---------------------------------------------------------- */

		foreach(self::modules() as $module)
		{
			$css = call_user_func( array($module,'pre_process'), $css);
		}
		
		if(class_exists('Layout'))
			$css = Layout::parse($css);
			
		/* --------------------------------------------------------
		
		Process Hook
		
		---------------------------------------------------------- */
		
		foreach(self::modules() as $module)
		{
			$css = call_user_func( array($module,'process'), $css);
		}
		
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
			
		/* --------------------------------------------------------
		
		Post-process Hook
		
		---------------------------------------------------------- */
			
		foreach(self::modules() as $module)
		{
			$css = call_user_func( array($module,'post_process'), $css);
		}

		if(class_exists('Absolute_Urls'))
			$css = Absolute_Urls::rewrite($css);

		if(class_exists('Minify'))
			$css = Minify::compress($css);
		
		/* --------------------------------------------------------
		
		Formatting Hook
		
		---------------------------------------------------------- */
		
		foreach(self::modules() as $module)
		{
			$css = call_user_func(array($module,'formatting_process'), $css);
		}

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
		
		/* --------------------------------------------------------
		
		Output Hook
		
		---------------------------------------------------------- */

		if( self::config('in_production') === false )
		{				
			foreach(self::modules() as $module)
			{
				call_user_func(array($module,'output'), $css);
			}
		}

		# We want the CSS, baby!
		if($return === false)
		{
			self::header('Content-Type','text/css');
			self::header('Last-Modified',gmdate('D, d M Y H:i:s', $modified) .' GMT');

			if
			(
				isset( $_SERVER['HTTP_IF_MODIFIED_SINCE'], $_SERVER['SERVER_PROTOCOL'] ) AND 
				$modified <= strtotime( $_SERVER['HTTP_IF_MODIFIED_SINCE'] )
			)
			{
				header("{$_SERVER['SERVER_PROTOCOL']} 304 Not Modified");
				exit;
			}

			self::send_headers();
		}
		
		self::log_save();
		
		# Hooray!
		if($return)
		{
			return $css;
		}
		else
		{
			echo $css;
		}
	}

}