<?php

/**
 * This file acts as the front controller for CSScaffold.
 * If you plan on using Scaffold anywhere else, you
 * probably want to do what this file is doing.
 */

ini_set('display_errors', true);
error_reporting(E_ALL | E_STRICT);

if(isset($_GET['f']))
{
	/**
	 * Scaffold configuration
	 */
	include './config.php';
	
	/**
	 * The Scaffold core class
	 */
	include './libraries/Scaffold/Scaffold.php';
	
	/**
	 * Load the libraries. Do it manually if you don't like this way.
	 */
	spl_autoload_register(array('Scaffold','auto_load'),true);
	
	/**
	 * Let Scaffold catch exceptions and errors
	 */
	set_exception_handler(array('Scaffold','exception_handler'));
	set_error_handler(array('Scaffold','error_handler'));

	/**
	 * Starts Scaffold and sets it's options. 
	 */
	Scaffold::init($config);

	/**
	 * Parse a single file and get the result
	 */
	$scaffold = new Scaffold_Engine();
	$result = $scaffold->parse_file($_GET['f'],Scaffold::cache());

	/**
	 * Get the headers for this file.
	 */
	$headers = Scaffold_HTTP::headers($_GET['f'], Scaffold::$lifetime);

	/**
	 * If the user wants us to render the CSS to the browser, we run this event.
	 * This will send the headers and output the processed CSS.
	 */
	Scaffold::render($result,$headers);
}
else
{
	exit('No file or string requested.');
}

/**
 * Prints out the value and exits.
 *
 * @author Anthony Short
 * @param $var
 */
function stop($var = '') 
{
	if( $var == '' ) $var = 'Hammer time!';
	header('Content-Type: text/plain');
	print_r($var);
	exit;
}