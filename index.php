<?php
/**
 * This file acts as the front controller for CSScaffold.
 *
 * It's fairly straightforward. It loads a config file, sets an include path,
 * and then runs CSScaffold. 
 *
 * You should be able to pull CSScaffold out and use it however you wish as
 * long as the config file is in the r,ight format.
 *
 * If you're looking to do something unique, like moving files and folders
 * around, you'll probably need to change this file or create a custom
 * front controller for Scaffold. Just keep in mind that the run method
 * NEEDS those three parameters, and all of the required 
 * files. Apart from that, you should be able to drop it into anything.
 *
 * @package CSScaffold
 */
 ini_set('display_errors', TRUE);
 error_reporting(E_ALL & ~E_STRICT);
 
# Include the config file
include 'config.php';

# Include the required classes
require $config['path']['system'] . 'libraries/Utils.php';
require $config['path']['system'] . 'libraries/Module.php';
require $config['path']['system'] . 'libraries/CSS.php';
require $config['path']['system'] . 'libraries/Controller.php';
require $config['path']['system'] . 'libraries/CSScaffold.php';

# Extra Classes
require $config['path']['system'] . 'libraries/Benchmark.php';

# If we want to debug (turn on errors and FirePHP)
if($config['debug'])
{	
	# Set the error reporting level.
	ini_set('display_errors', TRUE);
	error_reporting(E_ALL & ~E_STRICT);
	
	# Set error handler
	set_error_handler(array('CSScaffold', 'exception_handler'));

	# Set exception handler
	set_exception_handler(array('CSScaffold', 'exception_handler'));
}
else
{
	# Turn off errors
	error_reporting(0);
}

# Set the server variable for document root. A lot of 
# the utility functions depend on this. Windows servers
# don't set this, so we'll add it manually if it isn't set
if(!isset($_SERVER['DOCUMENT_ROOT']))
{
	$_SERVER['DOCUMENT_ROOT'] = $config['path']['document_root'];
}

# Set timezone, just in case it isn't set
if (function_exists('date_default_timezone_set'))
{
	date_default_timezone_set('GMT');
}

# And we're off!
if(isset($_GET['f']))
{
	# The files we want to parse. Full absolute URL file paths work best.
	# eg. request=/themes/stylesheets/master.css,/themes/stylesheets/screen.css
	$files = $_GET['f'];
	
	# Optional. You can see the base directory for all files you request. This
	# means you can see d to your CSS directory, then your f list of files doesn't
	# need to include the CSS directory.
	$dir = (isset($_GET['d'])) ? $_GET['d'] : false;
	
	# Scaffold's parse method can take an optional second parameter.
	# Certain modules have output triggers. When a certain output
	# word is set, the module takes over the output.
	# eg output=typography
	$output = (isset($_GET['mode'])) ? $_GET['mode'] : false;
	
	# Setup CSScaffold using our config. Scaffold can be setup once,
	# or before each parse (so you can change the config for different files).
	CSScaffold::setup($config);
	
	# Give scaffold a path to the file you want to pass, or a group of files
	# seperated with a comma.
	CSScaffold::run($files,$dir);
	
	# Output the CSS. If the first param is set to true, Scaffold will return
	# the result of all of the parsed files up to this point. Clears out the
	# internal CSS cache so you can parse more files again if you want.
	# eg. $css = CSScaffold::output(true);
	CSScaffold::output($output);
}

/**
 * Prints out the value and exits. Used for debugging.
 *
 * @author Anthony Short
 * @param $var
 */
function stop($var) 
{
	header('Content-Type: text/plain');
	print_r($var);
	exit;
}
