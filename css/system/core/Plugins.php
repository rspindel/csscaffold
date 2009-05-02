<?php defined('BASEPATH') OR die('No direct access allowed.');

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
	 * The plugin flags
	 * @var array
	 */
	var $flags = array();
	
	/**
	 * The plugin settings
	 * @var string
	 */
	var $settings = array();
	
	/**
	* Place any importing here. This will happen
	* before everything else. 
	*
	* @param   string   The css file as a string
	* @return  string	The css file as a string
	*/
	public function import($css) { return $css; }

	/**
	* For any preprocessing of the css. Arranging the css,
	* stripping comments.. etc.
	*
	* @param   string   The css file as a string
	* @return  string	The css file as a string
	*/
	public function pre_process($css) { return $css; }
	
	/**
	* The main grunt of the processing of the css string
	*
	* @param   string   The css file as a string
	* @return  string	The css file as a string
	*/
	public function process($css) { return $css; }
	
	/**
	* For formatters, compressors and prettifiers
	*
	* @param   string   The css file as a string
	* @return  string	The css file as a string
	*/
	public function post_process($css) { return $css; }
}