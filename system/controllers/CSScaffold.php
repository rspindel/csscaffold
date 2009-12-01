<?php
/**
 * Class CSScaffold
 * @package CSScaffold
 */

/**
 * CSScaffold
 *
 * Handles all of the inner workings of the framework and juicy goodness.
 * This is where the metaphorical cogs of the system reside. 
 *
 * Requires PHP 5.0.0
 * Tested on PHP 5.3.0
 *
 * @package CSScaffold
 * @author Anthony Short <anthonyshort@me.com>
 * @copyright 2009 Anthony Short. All rights reserved.
 * @license http://opensource.org/licenses/bsd-license.php  New BSD License
 * @link https://github.com/anthonyshort/csscaffold/master
 */
class CSScaffold extends Scaffold_Controller
{
	/**
	 * CSScaffold Version
	 */
	const VERSION = '1.5.0';

	/**
	 * Successfully loaded modules
	 *
	 * @var array
	 */
	private static $loaded_modules;
	
	/**
	 * Internal cache
	 */
	private static $internal_cache;
	
	/**
	 * Default config options
	 */
	private static $default = array
	(
		'debug' 				=> false,
		'in_production' 		=> false,
		'force_recache' 		=> false,
		'absolute_urls' 		=> false,
		'use_css_constants' 	=> false,
		'minify_css' 			=> true,
		'constants' 			=> array(),
		'disabled_plugins' 		=> array(),
		'path'					=> array
		(
			'document_root' 	=> '',
			'css' 				=> '../',
			'system' 			=> 'system',
			'cache' 			=> 'cache'
		)
	);
	 
	/**
	 * Sets the initial variables, checks if we need to process the css
	 * and then sends whichever file to the browser.
	 *
	 * @return void
	 * @author Anthony Short
	 **/
	public static function setup( $config = array() ) 
	{		
		# Merge them with our set options
		$config = array_merge(self::$default, $config);
		
		# If we want to debug (turn on errors and FirePHP)
		if($config['debug'])
		{	
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
			FB::setEnabled(false);
		}
		
		# Set the options and paths in the config
		self::config_set('core', $config);
		
		# Add document root to the include paths
		self::add_include_path( $config['path']['document_root'] );
		
		$cache = Utils::fix_path( $config['path']['cache'] );
		
		# Make sure the files/folders are writeable
		if (!is_dir($cache))
			throw new Scaffold_Exception("Cache path does not exist.");
			
		if (!is_writable($cache))
			throw new Scaffold_Exception("Cache path is not writable.");

		# Set the paths in the config	
		self::config_set(
			array
			(
				'core.path.docroot' => 	Utils::fix_path( $config['path']['document_root']),
				'core.path.system'	=> 	Utils::fix_path( $config['path']['system']),
				'core.path.cache'	=> 	$cache,
				'core.path.css'		=>	Utils::fix_path( $config['path']['css']),
				'core.url.css'		=>	Utils::urlpath( self::config('core.path.css')),
				'core.url.system'	=> 	Utils::urlpath( self::config('core.path.system'))
			)
		);
		
		# Add include paths
		self::add_include_path( self::config('core.path.css') );
		self::add_include_path( self::config('core.path.system') );
		self::add_include_path( self::config('core.path.system') . 'modules/' );
		
		# Load the modules
		self::load_modules( $config['disabled_plugins'] );
		
		# Log to Firebug
		FB::log( self::config('core') );
	}

	private static function parse_request($path)
	{
		# Get rid of those pesky slashes
		$requested_file	= Utils::trim_slashes($path);
		
		# Remove anything after .css - like /typography/
		$requested_file = preg_replace('/\.css(.*)$/', '.css', $requested_file);
		
		# Remove the start of the url if it exists (http://www.example.com)
		$requested_file = preg_replace('/https?\:\/\/[^\/]+/i', '', $requested_file);
		
		# Add our requested file var to the array
		$request['file'] = $requested_file;
		
		# Path to the file, relative to the css directory
		$request['relative_file'] = Utils::trim_slashes(str_replace( Utils::trim_slashes(self::config('core.url.css')), '', $requested_file));

		# Path to the directory containing the file, relative to the css directory		
		$request['relative_dir'] = pathinfo($request['relative_file'], PATHINFO_DIRNAME);

		# Otherwise we'll try to find it inside the CSS directory
		$request['path'] = self::find_file($requested_file, false, true);
		
		# Set the directory
		$request['directory'] = dirname($request['path']);
		
		# If the file doesn't exist
		if(!file_exists($request['path']))
			throw new Scaffold_Exception("Requested CSS file doesn't exist:" . $request['file']); 

		# or if it's not a css file
		if (!Utils::is_css($requested_file))
			throw new Scaffold_Exception("Requested file isn't a css file: $requested_file" );
		
		return $request;
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
			$controller = Utils::join_path($folder,$name.'.php');

			# The config file for the plugin (Optional)
			$config_file = $folder.'/config.php';
			
			# Set the paths in the config
			self::config_set("$name.support", Utils::join_path($folder,'support'));
			self::config_set("$name.libraries", Utils::join_path($folder,'libraries'));

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
	 * Output the CSS to the browser
	 *
	 * @return void
	 * @author Anthony Short
	 */
	public static function output()
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
	public static function run($files, $output = false, $force_recache = false)
	{
		# Go through each of the files, test them individually for changes, and 
		# add them to the output list for combining.
		foreach(explode(',',$files) as $file)
		{
			# Parse the request and set it in the config
			$request = self::parse_request($file);

			# Default cache mod time
			$cached_mod_time = 0;
			
			# Determine the name of the cache file
			$cached_file = Utils::join_path( self::config('core.path.cache'), $request['relative_file'] );
			
			if(file_exists($cached_file))
			{
				# When was the cache last modified
				$cached_mod_time =  (int) filemtime($cached_file);
			}

			# See if we should reparse and recache
			if
			(
				(
					# We've set it force recache in the config
					self::config('core.force_recache') OR 
					
					# We set it to force recache in the URL
					$force_recache OR
					
					# The cache is stale
					$cached_mod_time <= filemtime($request['path'])
				)

				OR 
				
				(
					# We're not in production
					self::config('in_production') === true
					
					AND 
					
					# The cache doesn't exist
					!file_exists($cached_file)
				)
			)
			{
				# sRemove the cached file 
				if(file_exists($cached_file)) unlink($cached_file);
				
				# Find the file, so we can use it
				$file = self::find_file($file, false, true);
				
				# Parse the CSS string
				$css = self::parse( $file, $output );
				
				# Write the css file to the cache
				self::cache_write( $css, $cached_file );	
			}
			
			# Add to output list
			self::$output[] = $cached_file;
			
			Utils::stop(self::$output);
		}	
	}
	
	/**
	 * Parses the single CSS file
	 *
	 * @author Anthony Short
	 * @param $file The file to the parsed
	 * @param $output The output type. Used in the output functions in the modules to display custom views.
	 * @return void
	 */
	public static function parse($file, $output)
	{			
		# Load it into the CSS object
		$css = new Scaffold_CSS( $file );
		
		/* --------------------------------------------------------
		
		Import Process Hook
		
		---------------------------------------------------------- */
		
		# Compress it before parsing
		$css->compress();
		
		# Import CSS files
		if(class_exists('Import'))
			Import::parse();
			
		Utils::stop($css);
													
		# Parse our css through the plugins
		foreach(self::$loaded_modules as $module)
		{
			call_user_func(array($module,'import_process'));
		}
		
		# Compress it before parsing
		$css->compress();

		# Parse the constants
		if(class_exists('Constants'))
			Constants::parse();

		/* --------------------------------------------------------
		
		Pre-process Hook
		
		---------------------------------------------------------- */

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
			
		/* --------------------------------------------------------
		
		Process Hook
		
		---------------------------------------------------------- */
		
		foreach(self::$loaded_modules as $module)
		{
			call_user_func(array($module,'process'));
		}
		
		# Parse the mixins
		if(class_exists('Mixins'))
			Mixins::parse();
		
		# Find missing constants
		if(class_exists('Constants'))
			Constants::replace();
			
		/* --------------------------------------------------------
		
		Post-process Hook
		
		---------------------------------------------------------- */
		
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
			
		/* --------------------------------------------------------
		
		Finishing Up
		
		---------------------------------------------------------- */
		
		# Convert all url()'s to absolute paths if required
		if(self::config('core.absolute_urls') === true)
		{
			$css->convert_to_absolute_urls();
		}
		
		# Replaces url()'s that start with ~ to lead to the CSS directory
		$css->replace_css_urls();
		
		# If they want to minify it
		if(self::config('core.minify_css') === true && class_exists('Minify'))
		{
			Minify::compress();
		}
		
		# Otherwise, we'll make it pretty
		else
		{
			$css->pretty();
		}
		
		# Formatting hook
		foreach(self::$loaded_modules as $module)
		{
			call_user_func(array($module,'formatting_process'));
		}

		# Output process hook for plugins to display views.
		# Doesn't run in production mode.
		if( self::config('core.in_production') === false )
		{				
			foreach(self::$loaded_modules as $module)
			{
				call_user_func(array($module,'output'));
			}
		}
		
		return $css->get_css_string();
	}
}