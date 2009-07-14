<?php

/******************************************************************************
* System Configuration
 ******************************************************************************/
	
	/**
	 * CACHE LOCK
	 *
	 * If you lock the cache, it will never recache your css
	 *
	 * @var boolean
	 **/	
	$config['cache_lock'] = false;
	
	/**
	 * SHOW CSS HEADER INFORMATION
	 *
	 * Output information at the top of your cached file for debugging
	 *
	 * @var boolean
	 **/
	$config['show_header'] = true;
	
	/**
	* DEBUG MODE
	*
	* Enables FirePHP for debugging. DO NOT HAVE THIS TURNED ON WHEN
	* YOUR SITE IS LIVE! IT COULD DISPLAY DANGEROUS SYSTEM INFORMATION
	*
	* @var boolean
	**/
	$config['debug'] = true;
	#error_reporting(0);


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
	$css_dir = "/css";
	
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