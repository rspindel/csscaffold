<?php

class Cache
{
	var $cached_file; 
	
	public function __construct()
	{
		
	}
	
	public function set($cached_file)
	{
		$this->cached_file = $cached_file;
		
		// When was the cache last modified
		$this->cached_mod_time = (int) @filemtime($this->cached_file);
	}
	
	public function clear_cache()
	{
		@unlink($this->cached_file);
	}
	
	public function empty_cache()
	{		
		$f = read_dir(CACHEPATH);
		
		foreach($f as $file)
		{
			if(substr($file, -3) == 'css')
			{
				@unlink($file);
			}
		}
	}
	
	public function write_cache($data, $mod_time)
	{
		$css_handle = fopen($this->cached_file, 'w');
		fwrite($css_handle, $data);
		fclose($css_handle);
		chmod($this->cached_file, 0777);
		touch($this->cached_file, $mod_time);
	}
		
	// Create hash of query string to allow variables to be cached
	public function generate_hash($args = array())
	{
		ksort($args);
		return md5(serialize($args));
	}
	
	// Make sure the cache exists
	function cache_exists()
	{
		if ($this->cached_file != CACHEPATH && !is_dir(CACHEPATH))
		{
			$path = CACHEPATH;
			$dirs = explode('/', $this->relative_dir);
			foreach ($dirs as $dir)
			{
				$path .= '/'.$dir;
				mkdir($path, 0777);
			}
		}
	}
}