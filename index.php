<?php
/**
 * This file acts as the front controller for CSScaffold.
 * If you plan on using Scaffold anywhere else, you
 * probably want to do what this file is doing. True story.
 *
 * @package CSScaffold
 */

# Include the config file
include 'config.php';

# Load the libraries. Do it manually if you don't like this way.
include 'libraries/Bootstrap.php';

/**
 * Choose whether to show or hide errors
 */
if(SCAFFOLD_PRODUCTION === false)
{	
	ini_set('display_errors', true);
	error_reporting(E_ALL & ~E_STRICT);
}
else
{
	ini_set('display_errors', false);
	error_reporting(0);
}

/**
 * Set timezone, just in case it isn't set. PHP 5.2+ 
 * throws a tantrum if you try and use time() without
 * this being set.
 */
if (function_exists('date_default_timezone_set'))
{
	date_default_timezone_set('GMT');
}

# And we're off!
if(isset($_GET['f']))
{	
	# Parse any array of files all at once.
	$result = Scaffold::parse($_GET['f'],$config);

	/** 
	 * If the user wants us to render the CSS to the browser, we run this event.
	 * This will send the headers and output the processed CSS.
	 */
	Scaffold::render($result['content'],$result['headers'],$config['gzip_compression']);
}

/**
 * Prints out the value and exits.
 *
 * @author Anthony Short
 * @param $var
 */
function stop($var = '') 
{
	if( $var == '' ) $var = 'Hammer time! Line ' . __LINE__;
	header('Content-Type: text/plain');
	print_r($var);
	exit;
}