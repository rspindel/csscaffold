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
		return self::$log[$group][] = array($message,$level);			
	}

}