<?php

class Core
{	
	public static $instance;
	
	function __construct()
	{
		self::$instance =& $this;
		 
		// Initialize some global classes
		$this->CONFIG =& new Config();
		$this->CACHE =& new Cache();
		$this->UA =& new User_agent();
		$this->BM =& new Benchmark();
	}

	public static function &get_instance()
	{
		return self::$instance;
	}
}

function &get_instance()
{
	return Core::get_instance();
}
$CORE = new CORE();