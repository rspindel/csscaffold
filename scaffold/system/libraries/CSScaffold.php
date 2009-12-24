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
 * These classes are required - Scaffold_Benchmark, Scaffold_Controller,
 * Scaffold_CSS, Scaffold_Module and Scaffold_Utils.
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
	 * The final output from Scaffold
	 *
	 * @var string
	 */
	public static $output;

	/**
	 * Default config options
	 */
	protected static $default = array
	(
		'in_production' 		=> false,
		'internal_cache'		=> false,
		'disable' 				=> array(),
		'document_root' 		=> '',
		'system' 				=> 'system',
		'cache' 				=> 'system/cache'
	);

	/**
	 * Sets the initial variables, checks if we need to process the css
	 * and then sends whichever file to the browser.
	 *
	 * @return void
	 * @author Anthony Short
	 **/
	public static function setup( $config ) 
	{
		# Set the debugging
		self::debug( $config['in_production'] );
		
		$system = str_replace('\\', '/', realpath($config['system'])).'/';
		
		# Prepare the logger
		self::log_threshold( $config['log_threshold'] );
		self::log_directory( $system . 'logs/' );

		# Set the current cache path
		self::cache_set( $config['cache'] );
		
		if ( self::$cache_lifetime = $config['cache_lifetime'] && $config['in_production'] === true )
		{
			self::$internal_cache['modules'] 		= self::cache('modules', self::$cache_lifetime);
			self::$internal_cache['include_paths'] 	= self::cache('include_paths', self::$cache_lifetime);
			self::$internal_cache['config'] 		= self::cache('config', self::$cache_lifetime);
		}
		
		# Set the options and paths in the config by merging the default
		# with the options set by the user.
		self::config_set('core', array_merge(self::$default, $config));
		
		if(!isset(self::$internal_cache['include_paths']))
		{
			# Add include paths
			self::add_include_path(
				$system, 
				$system.'modules/', 
				$config['document_root'] 
			);
		}
		
		# If we've got it from the internal cache already
		if(isset(self::$internal_cache['modules']))
		{
			foreach(self::$internal_cache['modules'] as $module)
			{
				# The find paths are set in our internal cache
				require_once $module;
			}
		}
		else
		{	
			self::modules();
			self::internal_cache_save('include_paths');
		}
		
		if(!isset(self::$internal_cache['config']))
		{
			self::$internal_cache['config'] = self::$config;
			self::internal_cache_save('config');
		}
		else
		{
			self::$config = self::$internal_cache['config'];
		}
	}
	
	/**
	 * Sets the debugging level
	 *
	 * @author Anthony Short
	 * @param $debug
	 * @return void
	 */
	public static function debug($debug)
	{
		if($debug === false)
		{	
			ini_set('display_errors', TRUE);
			error_reporting(E_ALL & ~E_STRICT);
		}
		else
		{
			error_reporting(0);
		}	
	}

	/**
	 * Parse the CSS
	 *
	 * @param array List of files
	 * @param string Base directory for files
	 * @param string Alterations to the config set in the setup.
	 * @return string - The processes css file as a string
	 * @author Anthony Short
	 */
	public static function parse( $files, $options = array(), $return = false )
	{		
		# Set the default recache state
		$recache = false;
		
		# Set the default output
		self::$output = false;
	
		# Options set via the URL... usually
		self::$options = array_flip($options);
		
		$cache_id = self::cache_id($files);
		$cache_folder = Scaffold_Utils::join_path(self::config('core.cache'),$cache_id);
		
		if(!is_dir($cache_folder))
		{
			self::cache_create($cache_id);
		}

		self::cache_set( $cache_folder );

		# Get the cached files
		if ( self::$cache_lifetime )
		{
			self::$internal_cache['output']	= self::cache('output', self::$cache_lifetime);
		}
		
		if(isset(self::$internal_cache['output']) && self::config('core.in_production') === true)
		{
			return self::output( self::$internal_cache['output'], $return );
		}
		
		$flags = self::flags();
	
		# Go through each of the files, test them individually for changes, and 
		# add them to the output list for combining.
		foreach($files as $file)
		{
			# Find the CSS file
			$request = self::find_file($file, false, true);	
			$file = Scaffold_Utils::urlpath( $request );

			# Find the name of the we need to create in the cache directory.
			$cached_file = self::$cache_path . self::cache_id(array($request,$flags)) . '.css';
	
			if (!Scaffold_Utils::is_css($file))
				self::error("Requested file isn't a css file: $file" );
				
			if(file_exists($cached_file))
			{
				$cached_mod_time = (int) filemtime($cached_file);
			}
			else
			{
				$cached_mod_time = 0;
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
				$ER = error_reporting(0);
				if(file_exists($cached_file)) unlink($cached_file);
				error_reporting($ER);
				
				# Parse the CSS string
				$css = self::parse_file( $request );

				# Write the css file to the cache
				self::cache_write( $css, $cached_file );
				
				# We'll need to recache the combined css too
				$recache = true;
			}

			$files_to_join[] = $cached_file;
		}		

		if(count($files_to_join) > 1)
		{
			$combined = self::$cache_path . self::cache_id( array($files_to_join,self::$flags) ) . '.css';
			
			# If any of the files has changed, we need to recache the group	
			# OR the cache file doesn't exist at all
			if($recache OR !file_exists($combined))
			{
				$css = '';
				
				foreach($files_to_join as $file)
				{
					$css .= file_get_contents( $file );
				}
	
				self::cache_write($css,$combined);
			}

			self::$internal_cache['output'] = $combined;
		}
		else
		{
			self::$internal_cache['output'] = $files_to_join[0];
		}

		self::internal_cache_save('output');
		
		return self::output( self::$internal_cache['output'], $return );
	}
	
	/**
	 * Sets the output of Scaffold
	 *
	 * @author Anthony Short
	 * @param $output
	 * @return void
	 */
	public static function set_output($output)
	{
		if(self::$output === false)
		{
			self::$output = $output;
		}
	}
	
	/**
	 * Gets the flags from each of the modules
	 *
	 * @author Anthony Short
	 * @param $param
	 * @return $array The array of flags
	 */
	public static function flags()
	{
		if(isset(self::$internal_cache['flags']))
			return self::$internal_cache['flags'];

		# Get the flags for this request		
		foreach(self::modules() as $module)
		{
			call_user_func(array($module,'flag'));
		}
		
		return self::$internal_cache['flags'];
	}

	/**
	 * Loads modules
	 *
	 * @return Array The names of the loaded addons
	 */
	public static function modules()
	{
		# If the modules have already been loaded
		if(isset(self::$internal_cache['modules']))
			return array_keys(self::$internal_cache['modules']);
		
		# Get each of the folders inside the Plugins and Modules directories
		$modules = self::list_files('modules');
		
		# The list of disabled modules
		$disabled = self::config('core.disable');

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
			
			if(!isset(self::$internal_cache['config'][$module]))
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
				self::$internal_cache['modules'][$name] = $controller;
			}
		}

		# We'll save this to a file for easy re-use.
		self::internal_cache_save('modules');
		
		return array_keys(self::$internal_cache['modules']);
	}
	
	/**
	 * Parses the single CSS file
	 *
	 * @author Anthony Short
	 * @param $file 	The file to the parsed
	 * @return $css 	string
	 */
	public static function parse_file($file)
	{
		self::add_include_path(dirname($file));

		# Handy info for some modules
		$dir = Scaffold_Utils::fix_path(dirname($file));
		self::config_set('current.file', $file);
		self::config_set('current.path', $dir);
		self::config_set('current.url', Scaffold_Utils::urlpath($dir) );
		
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
		
		if(class_exists('Iteration'))
			$css = Iteration::parse($css);	
		
		if(class_exists('Constants'))
			$css = Constants::replace($css);
			
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
	 * @author Anthony Short
	 */
	public static function output( $cache_file, $return = false )
	{
		$css = file_get_contents($cache_file);
		$modified = (int) filemtime($cache_file);
		
		/* --------------------------------------------------------
		
		Output Hook
		
		---------------------------------------------------------- */

		if( self::config('core.in_production') === false )
		{				
			foreach(self::modules() as $module)
			{
				call_user_func(array($module,'output'), $css);
			}
		}

		# We want the CSS, baby!
		if(self::$output === false AND $return === false)
		{
			self::set_output($css);
			
			if
			(
				isset( $_SERVER['HTTP_IF_MODIFIED_SINCE'], $_SERVER['SERVER_PROTOCOL'] ) AND 
				$modified <= strtotime( $_SERVER['HTTP_IF_MODIFIED_SINCE'] )
			)
			{
				header("{$_SERVER['SERVER_PROTOCOL']} 304 Not Modified");
				exit;
			}
			
			# Set the default headers
			header('Content-Type: text/css');
			header('Last-Modified: '. gmdate('D, d M Y H:i:s', $modified) .' GMT');
			echo self::$output;
			return;
		}
		
		self::log_save();
		
		# Hooray!
		if($return)
		{
			return self::$output;
		}
		else
		{
			echo self::$output;
		}
	}

}