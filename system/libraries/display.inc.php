<?php if (!defined('CSS_CACHEER')) { header('Location:/'); }

/**
 * Display an error message
 * @param string $message The message to be displayed
 */
function error($message) {
	print "ERROR : $message\n";
}

/**
 * Debug Information
 */
function dump($var)
{
	var_dump($var);
	die();
}

/**
 * Print out and exit
 * @param string $var to be displayed
 */
function stop($var) {
	print_r($var);
	exit;
}

/**
 * Log Message
 */
function log_message($message)
{
	$f = "system/logs/plugin_report.txt";
	$log = file_get_contents($f);
	$log .= gmdate('r') . "\n" . $message . "\n"; 
	file_put_contents($f,$log);
}