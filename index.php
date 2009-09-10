<?php

/**
 * This file acts as the "front controller" for CSScaffold. You can
 * configure your CSScaffold, modules, plugins and system directories here.
 * PHP error_reporting level may also be changed.
 *
 * @see https://github.com/anthonyshort/csscaffold/tree/master
 */

/**
 * The document root for the server. If you're server doesn't set this
 * variable, you can manually enter in the server path to the document root
 */
$document_root = $_SERVER['DOCUMENT_ROOT'];

/**
 * CSS directory. This is where you are storing your CSS files.
 *
 * This path can be relative to this file or absolute from the document root.
 */
$css = "../";

/**
 * The path to the system folder. This path can be relative to this file 
 * or absolute from the document root.
 */
$scaffold = "./";

/**
 * Sets the cache path. By default, this is inside of the system folder.
 * You can set it to a custom location here. Be aware that when Scaffold
 * recaches, it empties the whole cache to remove all flagged cache files. 
 */
$cache = "cache";
 
/**
 * Run the installer to help you solve path issues.
 */
define('INSTALL', FALSE);
 
/**
 * Define the website environment status. When this flag is set to TRUE, 
 * errors in your css will result in a blank page rather than displaying
 * error information to the user.
 */
define('IN_PRODUCTION', FALSE);

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
define('DOCROOT', str_replace('\\', '/', $document_root). '/');

# If this is a symlink, change to the real file
is_link(FRONT) and chdir(dirname(realpath(__FILE__)));

# Check if the css path is relative or absolute
$css = file_exists(realpath($css)) ? realpath($css) : DOCROOT.$css;
$scaffold = file_exists(realpath($scaffold)) ? realpath($scaffold) : DOCROOT.$scaffold;
$cache = file_exists(realpath($cache)) ? realpath($cache) : DOCROOT.$cache;

# Set the constants
define('SYSPATH', 	str_replace('\\', '/', $scaffold). '/');
define('CSSPATH', 	str_replace('\\', '/', $css). '/');
define('CACHEPATH', str_replace('\\', '/', $cache). '/');

# Clean up
unset($css, $document_root, $path, $scaffold, $cache); 

if(INSTALL && !IN_PRODUCTION)
{
	require SYSPATH.'install'.EXT;
}
else
{
	require SYSPATH.'core/Bootstrap'.EXT;
}
