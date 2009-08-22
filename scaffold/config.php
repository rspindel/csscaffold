<?php

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
$config['always_recache'] = true;

/**
 * SHOW CSS HEADER INFORMATION
 *
 * Output information at the top of your cached file for debugging
 *
 * @var boolean
 **/
$config['show_header'] = true;