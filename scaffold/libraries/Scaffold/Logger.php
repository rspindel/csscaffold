<?php

/**
 * Scaffold_Log
 *
 * Logs messages to files and handles errors
 * 
 * @author your name
 */
class Scaffold_Log
{
	/**
	 * If the log is enabled
	 *
	 * @var boolean
	 */
	public static $enabled = false;

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
		'debug',
	);
	
	/**
	 * The log directory
	 *
	 * @var string
	 */
	public static $log_directory;
	
	/**
	 * Adds the log directory and threshold. Should be run before using this class
	 *
	
	 * @param $threshold
	 * @return boolean
	 */
	public static function setup($dir)
	{
		self::log_directory($dir);
	}
	
	/**
	 * Enable logging
	 *
	
	 * @param $enable
	 * @return void
	 */
	public static function enable($enable)
	{
		self::$enabled = $enable;
	}

	/**
	 * Logs a message
	 *
	 * @param $message
	 * @param $level The severity of the log message
	 * @return void
	 */
	public static function log($message,$level = 3)
	{
		if(self::$enabled === false)
			return false;

		self::$log[$level][date('Y-m-d H:i:s P')] = $message;
	}

	/**
	 * Save all currently logged messages to a file.
	 *
	 * @return  void
	 */
	public static function save()
	{
		if (empty(self::$log) OR self::$enabled === false)
			return false;

		$filename = self::log_directory().date('Y-m-d').'.log.php';

		if (!is_file($filename))
		{
			touch($filename);
			chmod($filename, 0644);
		}

		// Messages to write
		$messages = array();
		$log = self::$log;

		foreach($log as $type => $value)
		{
			foreach($value as $date => $message)
			{
				$messages[] = $date.' --- '.self::$log_levels[$type].': '.$message;
			}
		}

		return file_put_contents($filename, implode(PHP_EOL, $messages).PHP_EOL, FILE_APPEND);
	}

	/**
	 * Get or set the logging directory.
	 *
	 * @param   string  new log directory
	 * @return  string
	 */
	public static function log_directory($dir = NULL)
	{
		if(self::$enabled === false)
			return false;

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
			return false;
		}
	}
}