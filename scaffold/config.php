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
	 * ALWAYS RECACHE
	 *
	 * If true, it will recache the css every time. This means
	 * you don't need to do ?recache during development.
	 *
	 * @var boolean
	 **/	
	$config['always_recache'] = false;
	
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
	$config['debug'] = false;