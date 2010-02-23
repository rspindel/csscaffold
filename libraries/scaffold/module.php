<?php

/**
 * Scaffold_Module
 *
 * The parent class for Scaffold modules
 * 
 * @author Anthony Short
 */
class Scaffold_Module
{
	/**
	 * Configuration for the module
	 *
	 * @var array
	 */
	public $config;
	
	/**
	 * Default settings which are used if the configuration
	 * settings from the file aren't set.
	 *
	 * @var array
	 */
	protected $defaults = array();
	
	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct($config)
	{
		$this->config = array_merge($this->defaults,$config);
	}
}