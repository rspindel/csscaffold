<?php

/**
 * Scaffold_Exception
 *
 * Captures errors and displays them.
 * 
 * @author Anthony Short
 */
class Scaffold_Exception extends Exception
{
	/**
	 * Set exception message.
	 *
	 * @param $title string The name of the exception
	 * @param  array   addition line parameters
	 */
	public function __construct($title, $description, $message)
	{
		# FB::error($message);
		$file = fopen(SYSPATH . '/views/error.php', 'rb');
		
		$message = "<ul><li>" . implode("</li><li>", $message) . "</li></ul>";
		
		fpassthru($file);
		fclose($file);

		parent::__construct($message);
	}
}