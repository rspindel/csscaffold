<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Plugins
 *
 * The base class for all the plugins
 *
 * @package default
 * @author Anthony Short
 **/
class Plugins
{
	/**
	 * Stores the paths to plugin files
	 *
	 * @var array
	 */
	public $path = array();
	
	/**
	* Place any importing here. This will happen
	* before everything else. 
	*/
	public function import_process() {}

	/**
	* For any preprocessing of the css. Arranging the css,
	* stripping comments.. etc.
	*/
	public function pre_process() {}
	
	/**
	* The main grunt of the processing of the css string
	*/
	public function process() {}
	
	/**
	* For formatters, compressors and prettifiers
	*/
	public function post_process() {}

	/**
	* For formatters, compressors and prettifiers
	*/
	public function formatting_process() {}
}