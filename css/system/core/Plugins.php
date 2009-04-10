<?php

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
	* For any preprocessing of the css - like importing files.
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
	* For formatters, compressers and prettifiers
	*
	* @param   string   The css file as a string
	* @return  string	The css file as a string
	*/
	public function post_process($css) { return $css; }
}