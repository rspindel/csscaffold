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
# Errors. This is overridden by the debug option later.
ini_set('display_errors', TRUE);
error_reporting(E_ALL & ~E_STRICT);

# Include the config file
include 'config.php';

# Include the required classes
require $config['path']['system'] . 'core/Utils.php';
require $config['path']['system'] . 'core/Benchmark.php';
require $config['path']['system'] . 'core/Module.php';
require $config['path']['system'] . 'core/CSS.php';
require $config['path']['system'] . 'core/Controller.php';
require $config['path']['system'] . 'core/Exception.php';
require $config['path']['system'] . 'vendor/FirePHPCore/fb.php';
require $config['path']['system'] . 'vendor/FirePHPCore/FirePHP.class.php';
require $config['path']['system'] . 'controllers/CSScaffold.php';

# Set the server variable for document root
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
if(isset($_GET['request']))
{
	# The files we want to parse. Full absolute URL file paths work best.
	# They can also be relative to the CSS directory you define in the config.
	# eg. request=/themes/stylesheets/master.css,/themes/stylesheets/screen.css
	$files = $_GET['request'];
	
	# Scaffold's parse method can take an optional second parameter.
	# Certain modules have output triggers. When a certain output
	# word is set, the module takes over the output.
	# eg output=typography
	$output = (isset($_GET['output'])) ? $_GET['output'] : false;
	
	# We can set an optional url parameter to force a recache too. This can
	# be set in the config also, so that it will always recache, no matter what.
	$force_recache = isset($_GET['recache']);
	
	# Setup CSScaffold using our config. Scaffold can be setup once,
	# or before each parse (so you can change the config for different files).
	CSScaffold::setup($config);
	
	# Give scaffold a path to the file you want to pass, or a group of files
	# seperated with a comma.
	CSScaffold::run($files, $output, $force_recache);
	
	# Output the CSS. If the first param is set to true, Scaffold will return
	# the result of all of the parsed files up to this point. Clears out the
	# internal CSS cache so you can parse more files again if you want.
	# eg. $css = CSScaffold::output(true);
	CSScaffold::output();
}