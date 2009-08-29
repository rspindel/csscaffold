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
	 * The constructor.
	 */
	private function __construct() {}
	
	/**
	 * Place any importing here. This will happen
	 * before everything else. 
	 */
	public static function import_process() {}

	/**
	 * For any preprocessing of the css. Arranging the css,
	 * stripping comments.. etc.
	 */
	public static function pre_process() {}
	
	/**
	 * The main grunt of the processing of the css string
	 */
	public static function process() {}
	
	/**
	 * For formatters, compressors and prettifiers
	 */
	public static function post_process() {}

	/**
	 * For formatters, compressors and prettifiers
	 */
	public static function formatting_process() {}
	
	/**
	 * Loads a library
	 */
	public static function load_library($library) 
	{
		require CSScaffold::find_file('libraries', $library, TRUE);
	}

}