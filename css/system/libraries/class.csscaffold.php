<?php

/**
 * CSScaffold
 *
 * Handles all of the inner workings of the framework and juicy goodness.
 * This is where the metaphorical cogs of the system reside. 
 *
 * @package default
 * @author Anthony Short
 **/
class CSScaffold extends Core {
	
	/**
	 * The file that was requested
	 *
	 * @var array
	 **/
	 var $flags = array();
	 
	/**
	 * The last modified time of the requested css file
	 *
	 * @var string
	 **/
	 var $requested_mod_time;
	 
	/**
	 * Sets the initial variables, checks if we need to process the css
	 * and then sends whichever file to the browser.
	 *
	 * @return void
	 * @author Anthony Short
	 **/
	public function __construct($requested_file, $recache = TRUE) 
	{		
		$this->CORE = get_instance();
		
		// Start the timer
		$this->CORE->BM->mark("start");
		
		// Set the recache state
		$this->recache = $recache;

		// Get our config values
		$this->CORE->CONFIG->set('requested_file', $requested_file); 
		$this->CORE->CONFIG->set('requested_file_name', basename($requested_file));
		$this->CORE->CONFIG->set('requested_dir', preg_replace('#/[^/]*$#', '', $requested_file));
		$this->CORE->CONFIG->set('relative_file', trim_slashes(substr($requested_file, strlen(URLPATH))) );
		$this->CORE->CONFIG->set('relative_dir', (strpos($this->CORE->CONFIG->relative_file, '/') === false) ? '' : preg_replace("/\/[^\/]*$/", '', $this->CORE->CONFIG->relative_file));
		
		// Get the modified time of the CSS file
		$this->requested_mod_time = filemtime(CSSPATH . "/" . $this->CORE->CONFIG->relative_file);
		
		// Load the plugins and flags
		$PLUGINS = new Plugins();
		$plugins = $PLUGINS->load_plugins();
		$this->loaded = $PLUGINS->loaded;
		
		// Send the flags to the cache and get it ready
		$this->CORE->CACHE->set($PLUGINS->flags, $recache);
		
		// Process the css
		$this->parse_css($plugins);	
	}
				
	/**
	 * Loads the CSS
	 *
	 * @return string - The unprocessed css file as a string
	 * @author Anthony Short
	 **/
	public function load_css()
	{
		if (substr($this->CORE->CONFIG->requested_file, -4) != '.css')
		{
			error("Error: Request file isn't a css file");
			exit;
		}
		
		elseif(substr($this->CORE->CONFIG->requested_file, 0, strlen(URLPATH)) != URLPATH)
		{
			error("Error: The file wasn't requested from the css directory");
			exit;
		}
		
		elseif(!file_exists(CSSPATH . "/" . $this->CORE->CONFIG->relative_file))
		{
			error("Error: The requested CSS file ". CSSPATH . "/" . $this->CORE->CONFIG->relative_file . " doesn't exist");
			exit;
		}
		
		return file_get_contents(CSSPATH . "/" . $this->CORE->CONFIG->relative_file);
	}
	
	
	public function parse_css($plugins)
	{
		// If the cache is stale or doesn't exist
		if (($this->CORE->CACHE->cached_mod_time < $this->requested_mod_time))
		{	
			// Load the CSS file
			$css = $this->load_css();
			
			// Parse our css through the plugins
			foreach($plugins as $plugin)
			{
				$css = $plugin->pre_process($css);
			}
			
			foreach($plugins as $plugin)
			{
				$css = $plugin->process($css);
			}
			
			foreach($plugins as $plugin)
			{
				$css = $plugin->post_process($css);
			}
			
			// Write the css file to the cache
			$this->CORE->CACHE->write_cache($css, $this->requested_mod_time);
		} 
	}
		
	/**
	 * Output the CSS to the browser
	 *
	 * @return void
	 * @author Anthony Short
	 **/
	public function output_css()
	{		
		// Stop the timer...
		$this->CORE->BM->mark("end");
			
		if 
		(
			isset($_SERVER['HTTP_IF_MODIFIED_SINCE'], $_SERVER['SERVER_PROTOCOL']) && 
			$this->requested_mod_time <= strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'])
		)
		{
			header("{$_SERVER['SERVER_PROTOCOL']} 304 Not Modified");
			exit();
		}
		else
		{			
			$css = file_get_contents($this->CORE->CACHE->cached_file);
			
			$filesize = round(strlen($css) / 1024 , 2);
			
			if ($this->CORE->CONFIG->show_header === TRUE)
			{
				$header  = "/* Processed and cached by Shaun Inman's CSS Cacheer. ";
				$header .= "Cached filesize is " . $filesize . " kilobytes. ";
				$header	.= "Processed in ".$this->CORE->BM->elapsed_time("start", "end")." seconds and rendered as " . $this->CORE->UA->browser . $this->CORE->UA->version;
				$header .= ' (with '.str_replace('Plugin', '', preg_replace('#,([^,]+)$#', " &$1", join(', ', $this->loaded ))).' enabled)';
				$header .= ' on '.gmdate('r').' <http://shauninman.com/search/?q=cacheer> */'."\r\n";
				$css = $header.$css;
			}
			 
			header('Content-Type: text/css');
			header("Vary: User-Agent, Accept");
			header('Last-Modified: '. gmdate('D, d M Y H:i:s', $this->requested_mod_time).' GMT');
			echo $css;
			exit();
		}
	}
	
	

} // end CSScaffold