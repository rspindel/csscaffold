<?php

/**
 * Creates a generic exception.
 */
class Scaffold_Exception extends Exception {

	# Template file
	protected $template = 'scaffold_error_page';

	# Header
	protected $header = FALSE;

	# Error code
	protected $code = E_SCAFFOLD;

	/**
	 * Set exception message.
	 *
	 * @param  string  i18n language key for the message
	 * @param  array   addition line parameters
	 */
	public function __construct($error)
	{
		$args = array_slice(func_get_args(), 1);

		// Fetch the error message
		#$message = Kohana::lang($error, $args);
		/*
		if ($message === $error OR empty($message))
		{
			// Unable to locate the message for the error
			$message = 'Unknown Exception: '.$error;
		}
		*/

		// Sets $this->message the proper way
		parent::__construct($message);
	}

	/**
	 * Magic method for converting an object to a string.
	 *
	 * @return  string  i18n message
	 */
	public function __toString()
	{
		return (string) $this->message;
	}

	/**
	 * Fetch the template name.
	 *
	 * @return  string
	 */
	public function getTemplate()
	{
		return $this->template;
	}

	/**
	 * Sends an Internal Server Error header.
	 *
	 * @return  void
	 */
	public function sendHeaders()
	{
		// Send the 500 header
		header('HTTP/1.1 500 Internal Server Error');
	}

} // End Kohana Exception

/**
 * Scaffold_User_Exception
 *
 * Captures errors and displays them.
 * 
 * @author Anthony Short
 */
class Scaffold_User_Exception extends Scaffold_Exception
{
	/**
	 * Set exception message.
	 *
	 * @param $title string The name of the exception
	 * @param  array   addition line parameters
	 */
	public function __construct($title, $message)
	{
		Exception::__construct($message);

		$this->code = $title;
	}
}