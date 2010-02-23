<?php

/**
 * Handles message logging and saving
 * @package Scaffold
 */

class Scaffold_Log
{
	/**
	 * Singleton instance
	 *
	 * @var object
	 */
	private static $instance;

	/**
	 * Log messages
	 *
	 * @var array
	 */
	public $messages = array();
	
	/**
	 * The log directory
	 *
	 * @var string
	 */
	public $directory;
	
	/**
	 * Get in the singleton instance of the log
	 *
	 * @author your name
	 * @param $param
	 * @return Scaffold_Log
	 */
	public static function instance($save = true)
	{
		if(self::$instance === null)
		{
			# Create the instance
			self::$instance = new self;
			
			# Save the log on exit
			if($save === true)
			{
				register_shutdown_function(array(self::$instance,'save'));
			}
		}
		
		return self::$instance;
	}

	/**
	 * Logs a message
	 *
	 * @param $message
	 * @param $level The severity of the log message
	 * @return void
	 */
	public function add($message,$level = 3)
	{
		if($this->directory === null)
			return;
		
		$this->messages[] = array
		(
			'type' => $level,
			'time' => date('Y-m-d H:i:s P'),
			'body' => $message
		);
		
		return $this;
	}

	/**
	 * Save all currently logged messages to a file.
	 *
	 * @return  void
	 */
	public function save()
	{
		if(empty($this->messages) OR $this->directory === null)
			return $this;
	
		$filename = $this->directory.date('Y-m-d').'.log.php';

		if(is_file($filename))
		{
			touch($filename);
			chmod($filename, 0644);
		}

		// Messages to write
		$messages = array();

		foreach($this->messages as $message)
		{
			$messages[] = $message['time'] . ' --- ' . $message['type'] . ' : ' . $message['body'];
		}
		
		file_put_contents($filename, implode(PHP_EOL, $messages).PHP_EOL, FILE_APPEND);

		return $this;
	}

	/**
	 * Get or set the logging directory.
	 *
	 * @param   string  new log directory
	 * @return  string
	 */
	public function directory($dir = NULL)
	{
		if($dir !== null)
		{
			if (is_dir($dir) AND is_writable($dir))
			{
				// Change the log directory
				$this->directory = $dir . DIRECTORY_SEPARATOR;
			}
		}

		return $this->directory;
	}
	
	/**
	 * Returns all the currently stored messages
	 *
	 * @author your name
	 * @param $param
	 * @return array
	 */
	public function messages()
	{
		return $this->messages;
	}
}