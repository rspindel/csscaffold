<?php

/******************************************************************************
* Get the common functions
******************************************************************************/
	
	# Fetch the core functions.
	require 'config/config.php'; 
	require 'core/Common.php'; 
	
/******************************************************************************
* Path Settings
******************************************************************************/
	 
	/**
	 * CSS DIRECTORY
	 *
	 * REQUIRED. Absolute path to your css directory. eg. /themes/css/
	 *
	 * @var string
	 **/
	$css_dir = "./";
	
	/**
	 * ASSET FOLDER PATH
	 *
	 * Leave this blank unless you want to use a folder other than
	 * css/assets. Make sure you use an absolute path.
	 *
	 * @var string
	 **/
	$assets_dir = "";
	
	/**
	 * CACHE DIRECTORY PATH
	 *
	 * Leave this unless you would like to set something other 
	 * than the default system/cache/ folder.  Use a full server 
	 * path with trailing slash.
	 * 
	 * Default is the cache folder inside the system directory
	 *
	 * @var string
	 **/
	$cache_dir = "";	

/******************************************************************************
* Define constants
******************************************************************************/
	
	$css_dir = join_path($_SERVER['DOCUMENT_ROOT'],$css_dir);
	$css_dir = realpath($css_dir);
	$css_dir = str_replace($_SERVER['DOCUMENT_ROOT'], "/", $css_dir);
	
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
	require './core/Config.php';
	require './core/CSScaffold.php';
	require './core/CSS.php';
	
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
	# But make sure a file was requested
	if(isset($_GET['request']))
	{
		CSScaffold::run($_GET);
	}
	else
	{
		stop("No CSS file requested");
	}


