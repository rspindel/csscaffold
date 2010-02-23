<?php

/**
 * Scaffold_Module
 *
 * The class for Scaffold modules
 * 
 * @author Anthony Short
 */
class Scaffold_Module
{
	/**
	 * The configuration
	 *
	 * @var array
	 */
	public $config = array();
	
	/**
	 * Default settings which are used if the configuration
	 * settings from the file aren't set.
	 *
	 * @var array
	 */
	protected $defaults = array();
	
	/**
	 * Sets the configuration
	 *
	 * @author your name
	 * @param $config
	 * @return void
	 */
	public function __construct($config)
	{
		$this->config = array_merge($this->defaults, $config);
	}

	/**
	 * Handles importing of CSS files and partials
	 *
	 * @param 	$css	Object	Instance of the Scaffold_CSS object
	 * @return 	$css	Object	The modified CSS object
	 */
	public function import( Scaffold_CSS $css ) { return $css; } 
	
	/**
	 * Handles the extraction of syntax parts to use during processing
	 *
	 * @param 	$css	Object	Instance of the Scaffold_CSS object
	 * @return 	$css	Object	The modified CSS object
	 */
	public function pre_process( Scaffold_CSS $css ) { return $css; } 
	
	/**
	 * Where most of the processing should occur.
	 *
	 * @param 	$css	Object	Instance of the Scaffold_CSS object
	 * @return 	$css	Object	The modified CSS object
	 */
	public function process( Scaffold_CSS $css ) { return $css; } 
	
	/**
	 * Making sure the last parts of the syntax are valid CSS
	 *
	 * @param 	$css	Object	Instance of the Scaffold_CSS object
	 * @return 	$css	Object	The modified CSS object
	 */
	public function post_process( Scaffold_CSS $css ) { return $css; } 
	
	/**
	 * Only formatting of the CSS should occur here
	 *
	 * @param 	$css	Object	Instance of the Scaffold_CSS object
	 * @return 	$css	Object	The modified CSS object
	 */
	public function format( Scaffold_CSS $css ) { return $css; } 
	
	/**
	 * Only occurs when not in production. For creating additional assets or
	 * changing the final output of Scaffold
	 *
	 * @param 	$css	Object	Instance of the Scaffold_CSS object
	 * @return 	$css	Object	The modified CSS object
	 */
	public function output( Scaffold_CSS $css ) { return $css; } 
}