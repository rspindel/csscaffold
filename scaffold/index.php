<?php

/******************************************************************************
* Get the common functions
******************************************************************************/
	
	# Fetch the core functions.
	require 'config/config.php'; 
	require 'core/Common.php'; 

/******************************************************************************
* Received request from mod_rewrite and set some vars
******************************************************************************/

	# The file that the user requested
	$requested_file	= isset($_GET['request']) ? $_GET['request'] : '';

	# Do they want to recache
	$recache = isset($_GET['recache']);

/******************************************************************************
* Define constants
******************************************************************************/
	
	# Fix up any weird slash issues. We'll just ditch them
	# and let our join path function fix it
	$css_dir = trim_slashes($css_dir);
	
	# The full server path to this directory
	$system_dir = realpath('./');
	
	# If cache dir is blank, use the default
	if ($cache_dir == "")
	{
		$cache_dir = join_path($system_dir, 'cache');
	}
	
	# If asset dir is blank, use the default
	if ($assets_dir == "")
	{
		$assets_dir = join_path($css_dir, 'assets');
	}
	
	# Define the document root
	define('DOCROOT', $_SERVER['DOCUMENT_ROOT']);

	# Full path to the cache folder
	define('CACHEPATH', $cache_dir);
		
	# Full path to the system folder
	define('BASEPATH', $system_dir);
	
	# Url to the scaffold folder
	define('BASEURL', str_replace(DOCROOT, '', BASEPATH));
	
	# Full path to the CSS directory
	define('CSSPATH', join_path(DOCROOT,$css_dir));
	
	# Url path to the css directory
	define('URLPATH', $css_dir);
	
	# Url to the assets folder
	define('ASSETURL', trim_slashes($assets_dir));
	
	# Url path to the asset directory
	define('ASSETPATH', join_path(DOCROOT,$assets_dir));
	
	# Clean up
	unset($cache_dir, $css_dir, $assets_dir, $system_dir);
	

/******************************************************************************
* Make sure the files/folders are writeable
******************************************************************************/

	if (!is_writable(CACHEPATH))
	{
		stop("Cache path (".CACHEPATH.") is not writable");
	}
	
/******************************************************************************
* Load the required classes
******************************************************************************/
	
	require './core/Benchmark.php';
	require './core/Plugins.php';
	require './core/Cache.php';
	require './core/User_agent.php';
	require './core/Config.php';
	require './core/CSScaffold.php';
	
	require './libraries/FirePHPCore/FirePHP.class.php';
	require './libraries/FirePHPCore/fb.php';
	
	if($config['debug'] === true)
	{
		FB::setEnabled(true);
	}
	else
	{
		FB::setEnabled(false);
		error_reporting(E_ALL);
	}
	
		
/******************************************************************************
* We've got everything we need, lets do this thing...
******************************************************************************/
	
	# Setup CSScaffold with our CSS file
	CSScaffold::run($requested_file, $recache);

