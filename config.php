<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * CACHE LOCK
 *
 * If you lock the cache, it will never recache your css
 *
 * @var boolean
 */	
$config['cache_lock'] = false;

/**
 * ALWAYS RECACHE
 *
 * If true, it will recache the css every time. This means
 * you don't need to do ?recache during development.
 *
 * @var boolean
 */	
$config['always_recache'] = true;

/**
 * SHOW CSS HEADER INFORMATION
 *
 * Output information at the top of your cached file for debugging
 *
 * @var boolean
 **/
$config['show_header'] = true;

/**
 * Enabled Plugins
 *
 * Set which plugins are currently enabled. Plugins may also have
 * their own configuration options. Check the plugin folder for a 
 * config.php file to customize the way the plugin works.
 *
 * All of these plugins are already installed. This is where you can 
 * turn them on or off.
 * 
 * To install a new plugin, drop it in scaffold/plugins, then add it to
 * this list. For more information on any of these plugins, or about
 * creating your own plugins, check the wiki on Github.
 * 
 */
$config['plugins'] = array
(
	# Easily image replace text. Just use the image-replace property
	# and give it a url() like a normal image. Scaffold takes care of the rest.
	'ImageReplace' => true,
	
	# Allows you to make the final output much more readable. Just as ?pretty as a 
	# url parameter when calling your CSS from a browser.
	'Pretty' => true,

	# Feed different browsers different CSS rules by targetting
	# the browsers from WITHIN your CSS
	'Browsers' => false,

	# Define a grid from within your CSS and have access to
	# constants, mixins and more. This makes creating layouts
	# much, much quicker.
	'Layout' => true,
	
	# Uses the minify library to compress your CSS as much as possible.
	'Minify' => false,
	
	# Base one selector on another selector so you can 'extend' your
	# CSS rules, just like objects in OOP
	'OOCSS' => false,
	
	# Validate your CSS on the fly. DO NOT keep this running all the
	# time. It will cause your CSS to load very slowly, and can bog
	# down the validation servers.
	'Validate' => false,
	
	# Set constants via XML, allowing a CMS to tie itself in with
	# your CSS files. 
	'XML_constants' => false,
);

/**
 * FIREBUG
 *
 * Enable Firebug output
 */
FB::setEnabled(false);

/**
 * LANGUAGE
 *
 * Choose a language. Currently, only supports English
 *
 * @var boolean
 */
$config['language'] = 'english';