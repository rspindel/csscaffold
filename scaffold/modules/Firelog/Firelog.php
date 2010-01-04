<?php

/**
 * Firelog
 *
 * Sends logged messages to FirePHP.
 * 
 * @author Anthony Short
 */
class Firelog extends Scaffold_Module
{
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
		'log',
	);

	/**
	 * During the output phase, gather all the logs and send them to FireBug
	 *
	 * @author Anthony Short
	 * @param $css
	 * @return void
	 */
	public static function output($css)
	{
		if( CSScaffold::$config['in_production'] === false)
		{
			self::_enable();
			
			/* --------------------------------------------------------
			
			General
			
			---------------------------------------------------------- */
			
			# Log about the completed file
			self::_file(CSScaffold::$internal_cache['output'],'File');

			/* --------------------------------------------------------
			
			Constants
			
			---------------------------------------------------------- */
			
			if(Constants::$constants)
			{
				$table = array();
				$table[] = array('Constants Name', 'Value');
		
				foreach(Constants::$constants as $key => $value)
				{
					$table[] = array($key,$value);
				}
	
				FB::table('Constants', $table);
			}
			
			/* --------------------------------------------------------
			
			Mixins
			
			---------------------------------------------------------- */
			
			if(Mixins::$mixins)
			{
				$table = array();
				$table[] = array('Mixin Name', 'Parameters', 'Properties');
				
				foreach(Mixins::$mixins as $key => $value)
				{
					$table[] = array($key,implode(',',$value['params']),$value['properties']);
				}
		
				FB::table('Mixins', $table);
			}
			
			/* --------------------------------------------------------
			
			Import
			
			---------------------------------------------------------- */
			
			self::_group('Included Files', Import::$loaded, 3);
							
			/* --------------------------------------------------------
			
			Modules
			
			---------------------------------------------------------- */	
			
			/*
			foreach(Scaffold_Logger::$log as $group => $value)
			{
				FB::group($group);
				foreach($value as $error)
				{
					self::_log($error[0],$error[1]);
				}
				FB::groupEnd();
			}
			*/
			
			# Log the basics
			self::_group('Flags',CSScaffold::flags());
			#self::_group('Include Paths', CSScaffold::include_paths());
			#self::_group('Modules', CSScaffold::modules());
		}	
	}

	/**
	 * Loads FirePHP
	 *
	 * @author Anthony Short
	 * @param $param
	 * @return return type
	 */
	private static function _enable()
	{
		if(!class_exists('FB'))
			require dirname(__FILE__) . '/libraries/FirePHPCore/fb.php';
		
		# Enable it
		FB::setEnabled(true);
	}
	
	/**
	 * Logs a string or array to Firebug
	 *
	 * @author Anthony Short
	 * @param $group
	 * @return void
	 */
	private static function _log($message,$level=4)
	{		
		if(is_array($message))
		{
			foreach($message as $key => $value)
			{
				if(is_numeric($key))
				{
					call_user_func(array('FB',self::$log_levels[$level - 1]), $value);
				}
				else
				{
					self::_log($key,$value,$level);
				}
			}
		}
		else
		{
			call_user_func(array('FB',self::$log_levels[$level - 1]), $message);
		}
	}
	
	/**
	 * Logs to a group
	 *
	 * @author Anthony Short
	 * @param $group
	 * @return void
	 */
	private static function _group($group,$message,$level=4)
	{
		FB::group($group);
		self::_log($message,$level);
		FB::groupEnd();
	}
	
	/**
	 * Logs info about a file
	 *
	 * @author Anthony Short
	 * @param $file
	 * @return void
	 */
	private static function _file($file,$name = false)
	{
		if($name === false)
			$name = $file;

		# Log about the compiled file
		$contents = file_get_contents($file);
		$gzipped = gzcompress($contents, 9);
		
		$table = array();
		$table[] = array('Name','Value');
		$table[] = array('Compressed Size', Scaffold_Utils::readable_size($contents));
		$table[] = array('Gzipped Size', Scaffold_Utils::readable_size($gzipped));
		FB::table($name,$table);
	}
}