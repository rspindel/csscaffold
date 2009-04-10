<?php if (!defined('CSS_CACHEER')) { header('Location:/'); }

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
	

/**
 * CREATE SIZE REPORT
 *
 * Creates a size report inside your logs folder
 *
 * @var boolean
 **/
$config['create_report'] = TRUE;
