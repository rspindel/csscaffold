<?php

/**
 * This file acts as the "front controller" for CSScaffold. You can
 * configure your CSScaffold, modules, plugins and system directories here.
 * PHP error_reporting level may also be changed.
 *
 * @see https://github.com/anthonyshort/csscaffold/tree/master
 */

/**
 * Path to the default config file
 */
$config_file = 'config.php';

/**
 * Load the config
 */
require $config_file;

/**
 * Make sure the we're using PHP 5.2 or newer
 */
version_compare(PHP_VERSION, '5.2', '<') and exit('CSScaffold requires PHP 5.2 or newer.');

/**
 * Set the error reporting level. Unless you have a special need, E_ALL is a
 * good level for error reporting.
 */
error_reporting(E_ALL & ~E_STRICT);

/**
 * Setting it to false will remove all errors
 */
ini_set('display_errors', TRUE);

/**
 * If you rename all of your .php files to a different extension, set the new
 * extension here. This option can left to .php, even if this file has a
 * different extension.
 */
define('EXT', '.php');

/**
 * --------------------------------------------------------------------------------
 * Don't touch anything below here.
 * --------------------------------------------------------------------------------
 */

# Path information about the current file
$path = pathinfo(__FILE__);

# This file
define('FRONT', $path['basename']);

# If this is a symlink, change to the real file
is_link(FRONT) and chdir(dirname(realpath(__FILE__)));

# Set the docroot
define('DOCROOT', str_replace('\\', '/', $document_root). '/');

# Check if the paths are relative or absolute
$scaffold = file_exists(realpath($scaffold)) ? realpath($scaffold) : DOCROOT.$scaffold;
$css = file_exists(realpath($css)) ? realpath($css) : DOCROOT.$css;
$system = file_exists(realpath($system)) ? realpath($system) : DOCROOT.$system;
$cache = file_exists(realpath($cache)) ? realpath($cache) : DOCROOT.$cache;
$plugins = file_exists(realpath($plugins)) ? realpath($plugins) : DOCROOT.$plugins;
$config_file = file_exists(realpath($config_file)) ? realpath($config_file) : DOCROOT.$config_file;

# Set the constants
define('SCAFFOLD',  str_replace('\\', '/', $scaffold). '/');
define('SYSPATH', 	str_replace('\\', '/', $system). '/');
define('CSSPATH', 	str_replace('\\', '/', $css). '/');
define('CACHEPATH', str_replace('\\', '/', $cache). '/');
define('PLUGINS',   str_replace('\\', '/', $plugins). '/');
define('CONFIG',    str_replace('\\', '/', $config_file));

# URL to the css directory
define('CSSURL', str_replace(DOCROOT, '/', CSSPATH));
define('SYSURL', str_replace(DOCROOT, '/', SYSPATH));

# Clean up
unset($css, $document_root, $path, $system, $cache, $scaffold, $plugins, $config_file); 

if(INSTALL && !IN_PRODUCTION)
{
	require SYSPATH.'views/install'.EXT;
}
else
{
	require SYSPATH.'core/Bootstrap'.EXT;
}