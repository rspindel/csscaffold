<?php

//error_reporting(0);

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
	$config['cache_lock'] = FALSE;
	
	
	/**
	 * SHOW CSS HEADER INFORMATION
	 *
	 * Output information at the top of your cached file for debugging
	 *
	 * @var boolean
	 **/
	$config['show_header'] = TRUE;


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
	$css_server_path = "../css/";
		
		
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
	$assets_dir = "../css/assets";
	