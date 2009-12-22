<?php

/**
 * Scaffold_Logger
 *
 * Handles logging of messages and errors
 * 
 * @author Anthony Short
 */
class Scaffold_Logger
{

	/**
	 * Logs
	 *
	 * @var array
	 */
	public static $log = array();
		
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
	 * Logs a message
	 *
	 * @author Anthony Short
	 * @param $message
	 * @return void
	 */
	public static function log($group,$message,$level = 4)
	{
		if(!$message)
			return;
			
		if(class_exists('FB'))
		{
			FB::group($group);
			
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
						self::log($key,$value,$level);
					}
				}
			}
			else
			{
				call_user_func(array('FB',self::$log_levels[$level - 1]), $message);
			}
			
			FB::groupEnd();
		}

		return self::$log[$level][$group] = $message;			
	}

}