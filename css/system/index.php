<?php

	// Turn off those ugly errors :)
	//error_reporting(0);
	
/******************************************************************************
* Path Settings
******************************************************************************/
	 
	/**
	 * CSS DIRECTORY
	 *
	 * REQUIRED. URL path to you css directory. eg. /themes/css/
	 *
	 * @var string
	 **/
	$css_dir = "/css/";
	
	
	/**
	 * CSS SERVER PATH
	 *
	 * REQUIRED. You can set it as relative to the system directory, or
	 * use an absolute full server path
	 *
	 * @var string
	 **/
	$css_server_path = "../";
		
		
	/**
	 * SYSTEM FOLDER PATH
	 *
	 * Leave this unless you would like to set something other 
	 * than the default system folder.  Use a full server path with trailing slash.
	 * If you change this setting, you'll probably need to change the cache path below.
	 * Remember to check your .htaccess file in your CSS directory also.
	 *
	 * @var string
	 **/
	$system_dir = "./";
	
	
	/**
	 * CACHE DIRECTORY PATH
	 *
	 * Leave this unless you would like to set something other 
	 * than the default system/cache/ folder.  Use a full server path with trailing slash.
	 *
	 * @var string
	 **/
	$cache_dir = "cache";
	
	
	/**
	 * ASSET FOLDER PATH
	 *
	 * The name of your asset folder relative to your css directory. 
	 * Use full server path if its out of the css directory with a trailing slash.
	 *
	 * @var string
	 **/
	$assets_dir = "../assets";
	

/******************************************************************************
* Get the common functions
******************************************************************************/
	
	// Fetch the core functions.
	require 'libraries/functions.inc.php'; 


/******************************************************************************
* Received request from mod_rewrite and set some vars
******************************************************************************/

	// The file that the user requested
	$requested_file	= isset($_GET['cssc_request']) ? $_GET['cssc_request'] : '';

	// Do they want to recache
	$recache = isset($_GET['recache']);

	
/******************************************************************************
* Define constants
******************************************************************************/
	
	// Define the document root
	define('DOCROOT', $_SERVER['DOCUMENT_ROOT']);

	// Full path to the cache folder
	define('CACHEPATH', slash(realpath($cache_dir)));
		
	// Full path to the system folder
	define('BASEPATH', slash(realpath($system_dir)));
	
	// Full path to the CSS directory
	define('CSSPATH', slash(realpath($css_server_path)));
	
	// Url path to the css directory
	define('URLPATH', slash($css_dir));
	
	// Url path to the asset directory
	define('ASSETPATH', slash(realpath($assets_dir)));
	
	// Clean up
	unset($cache_dir, $system_dir, $css_server_path, $css_dir, $assets_dir);
	
	
/******************************************************************************
* Run the install check
******************************************************************************/

	if ($requested_file	== "" && file_exists('./install.php'))
	{
		// Load the installation tests
		include './install.php';
		exit();
	}
	elseif ($requested_file == "")
	{
		echo "You need to specify a css file";
		exit;
	}
	

/******************************************************************************
* Make sure the files/folders are writeable
******************************************************************************/

	if (!is_writable(CACHEPATH))
	{
		stop("Cache path (".CACHEPATH.") is not writable");
	}
	elseif (!is_writable(ASSETPATH) )
	{
		stop("Asset path (".ASSETPATH.") is not writable");
	}
	
	foreach (read_dir(ASSETPATH) as $key => $value)
	{
		if (!is_writable($value))
		{
			stop("$value is not writable");
		}
	}
	
/******************************************************************************
* Load the required classes
******************************************************************************/
	
	// Load our Core class
	require 'core/Core.php';
	
	// Allows us to test times between events
	require 'core/Benchmark.php';
	
	// Allows us to use plugins within our main object
	require 'core/Plugins.php';
		 
	// Load our CSScaffold class / Controller
	require 'core/CSScaffold.php';

		
/******************************************************************************
* We've got everything we need, let do this thing...
******************************************************************************/
	
	// Setup CSScaffold with our CSS file
	CSScaffold::setup($requested_file, $recache);
	
	// Parse the css
	CSScaffold::parse_css();
	
	// Send it to the browser
	CSScaffold::output_css();

