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
	const VERSION = '2.0.0';

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
	
	private static $output = array();
	 
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
		
		# Set the options and paths in the config
		self::config_set('core', $config);
		
		# Add document root to the include paths
		self::add_include_path( $config['path']['document_root'] );
		
		$cache = Scaffold_Utils::fix_path( $config['path']['cache'] );
		
		# Make sure the files/folders are writeable
		if (!is_dir($cache))
			throw new Exception("Cache path does not exist.");
			
		if (!is_writable($cache))
			throw new Exception("Cache path is not writable.");
			
		# Define path constants
		if(!defined('SCAFFOLD_DOCROOT'))
		{
			define('SCAFFOLD_DOCROOT', Scaffold_Utils::fix_path( $config['path']['document_root']) );
			define('SCAFFOLD_SYSPATH', Scaffold_Utils::fix_path( $config['path']['system']) );
			define('SCAFFOLD_MODULES', SCAFFOLD_SYSPATH . 'modules/');
		}
		
		# Set the current cache path
		self::cache_set($cache);

		# Add include paths
		self::add_include_path( SCAFFOLD_SYSPATH, SCAFFOLD_MODULES, SCAFFOLD_DOCROOT );

		# Load the modules
		self::load_modules( $config['disabled_plugins'] );
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
			$controller = Scaffold_Utils::join_path($folder,$name.'.php');

			# The config file for the plugin (Optional)
			$config_file = $folder.'/config.php';

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
	public static function output($mode)
	{		
		# Output process hook for plugins to display views.
		# Doesn't run in production mode.
		if( self::config('core.in_production') === false )
		{				
			foreach(self::$loaded_modules as $module)
			{
				call_user_func(array($module,'output'), $mode);
			}
		}
				
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
			header('Last-Modified: '. gmdate('D, d M Y H:i:s', self::config('core.cache.mod_time')) .' GMT');

			foreach(self::$output as $file)
			{
				echo file_get_contents( $file ) . "\n\n";
			}			
			
			exit;
		}
	}
	
	/**
	 * Parse the CSS
	 *
	 * @return string - The processes css file as a string
	 * @author Anthony Short
	 */
	public static function run($files, $dir = false)
	{
		# Go through each of the files, test them individually for changes, and 
		# add them to the output list for combining.
		foreach(explode(',',$files) as $file)
		{
			if($dir !== false)
			{
				$file = Scaffold_Utils::join_path($dir, $file);
			}

			# Otherwise we'll try to find it inside the CSS directory
			$request = self::find_file($file, false, true);
			
			# Find the name of the we need to create in the cache directory.
			$cached_file =  self::$cache_path . md5($request) . ".css";
	
			# or if it's not a css file
			if (!Scaffold_Utils::is_css($file))
				throw new Exception("Requested file isn't a css file: $file" );

			# Default cache mod time
			$cached_mod_time = 0;
			
			if(file_exists($cached_file))
			{
				# When was the cache last modified
				$cached_mod_time =  (int) filemtime($cached_file);
			}

			# See if we should reparse and recache
			if
			(
				# We're not in production
				self::config('core.in_production') === false
				
				OR
				
				(
					# We are in production
					self::config('core.in_production') === true
					
					AND 
					
					(
						# The cache doesn't exist
						!file_exists($cached_file)
						
						OR
						
						# The cache is stale
						$cached_mod_time <= filemtime($request)				
					)
				)

			)
			{
				# Remove the cached file 
				if(file_exists($cached_file)) unlink($cached_file);
				
				# Parse the CSS string
				$css = self::parse( $request );

				# Write the css file to the cache
				self::cache_write( "/* $file */\n" . $css, $cached_file );
			}
			
			# Add to output list
			self::$output[] = $cached_file;
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
	public static function parse($file)
	{
		self::add_include_path(dirname($file));
		
		# Load it into the CSS object
		if(file_exists($file))
		{	
			$css = file_get_contents($file);
		}
		
		# Otherwise it's just a string
		else
		{
			$css = $file;
		}

		/* --------------------------------------------------------
		
		Import Process Hook
		
		---------------------------------------------------------- */
		
		# Compress it before parsing
		$css = Scaffold_CSS::compress($css);

		# Import CSS files
		if(class_exists('Import'))
			$css = Import::parse($css);
													
		# Parse our css through the plugins
		foreach(self::$loaded_modules as $module)
		{
			$css = call_user_func( array($module,'import_process'), $css);
		}

		# Compress it before parsing
		$css = Scaffold_CSS::compress($css);

		# Parse the constants
		if(class_exists('Constants'))
			$css = Constants::parse($css);
			
		# Set the global constants from the config
		foreach(self::config('core.constants') as $key => $value)
		{
			Constants::set($key, $value);
		}

		/* --------------------------------------------------------
		
		Pre-process Hook
		
		---------------------------------------------------------- */

		foreach(self::$loaded_modules as $module)
		{
			$css = call_user_func( array($module,'pre_process'), $css);
		}
		
		# Parse the @grid
		if(class_exists('Layout'))
			$css = Layout::parse_grid($css);
		
		# Replace the constants
		if(class_exists('Constants'))
			$css = Constants::replace($css);
		
		# Parse @for loops
		if(class_exists('Iteration'))
			$css = Iteration::parse($css);	
			
		/* --------------------------------------------------------
		
		Process Hook
		
		---------------------------------------------------------- */
		
		foreach(self::$loaded_modules as $module)
		{
			$css = call_user_func( array($module,'process'), $css);
		}
		
		# Parse the mixins
		if(class_exists('Mixins'))
			$css = Mixins::parse($css);
		
		# Find missing constants
		if(class_exists('Constants'))
			$css = Constants::replace($css);
			
		/* --------------------------------------------------------
		
		Post-process Hook
		
		---------------------------------------------------------- */
		
		foreach(self::$loaded_modules as $module)
		{
			$css = call_user_func( array($module,'post_process'), $css);
		}
		
		# Parse the expressions
		if(class_exists('Expression'))
			$css = Expression::parse($css);
		
		# Parse the nested selectors
		if(class_exists('NestedSelectors'))
			$css = NestedSelectors::parse($css);
			
		/* --------------------------------------------------------
		
		Finishing Up
		
		---------------------------------------------------------- */
		
		# Convert all url()'s to absolute paths if required
		# and change all the ~ urls to actual absolute paths
		if( $found = Scaffold_CSS::find_functions('url', '', $css) )
		{
			foreach($found[1] as $key => $value)
			{
				$url = Scaffold_Utils::unquote($value);
								
				# Absolute Path
				if($url[0] == "/" || $url[0] == "\\")
				{
					continue;
				}
				
				# Relative Path
				else
				{
					$css = str_replace($value, str_replace( $_SERVER['DOCUMENT_ROOT'], "/", self::find_file($url) ), $css);
				}
			}
		}

		# If they want to minify it
		if(class_exists('Minify'))
			$css = Minify::compress($css);
		
		/* --------------------------------------------------------
		
		Formatting Hook
		
		---------------------------------------------------------- */
		
		# Formatting hook
		foreach(self::$loaded_modules as $module)
		{
			$css = call_user_func(array($module,'formatting_process'), $css);
		}
		
		self::remove_include_path(dirname($file));
		
		return $css;
	}
}