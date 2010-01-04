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
	 * Adds the log directory and threshold. Should be run before using this class
	 *
	
	 * @param $threshold
	 * @return boolean
	 */
	public static function setup($threshold,$dir)
	{
		self::$threshold = $threshold;
		self::log_directory($dir);
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
	 * Save all currently logged messages to a file.
	 *
	 * @return  void
	 */
	public static function save()
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
}