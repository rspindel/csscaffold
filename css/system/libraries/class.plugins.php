<?php

class Plugins
{
	var $flags = array();
	var $loaded = array();

	function __construct()
	{
		$this->CORE = get_instance();
	}
	
	public function load_plugins()
	{	
		// Load each of the plugins
		foreach(read_dir(BASEPATH . "/plugins") as $plugin)
		{
			include($plugin);
									
			if ( isset($plugin_class) && class_exists($plugin_class) )
			{
				$this->loaded[] = $plugin_class;
				$plugins[$plugin_class] = new $plugin_class($flags);
				
				// Set the flags
				$this->flags = array_merge($this->flags, $plugins[$plugin_class]->flags);
				
				// Update the config
				$this->CORE->CONFIG->$plugin_class = $settings;
							
				unset($settings);
			}
		}
		return $plugins;
	}
	
	function pre_process($css) 	{ return $css; }
	function process($css) 		{ return $css; }
	function post_process($css) { return $css; }
}