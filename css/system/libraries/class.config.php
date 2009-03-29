<?PHP

class Config
{	
	function __construct()
	{		
		// Load config/config.php. This should really use the BASEPATH, but meh.  
		$this->load('config/config.php');
		
		/******************************************************************************
		Get some paths
		******************************************************************************/
		 		
		// Set the cache directory
		if($this->cache_dir == "") 
		{
			$this->cache_dir = "cache";
		}
		
		// Set the system directory
		if($this->system_dir == "") 
		{
			$this->system_dir = "./";
		}
		
		// Set the system directory. Skip it if it starts
		// with a slash as they're probably setting a full server path
		if(substr($this->system_dir, 0, 1)  != "/")
		{
			$this->system_dir = realpath($this->system_dir);
		}
		
		// Set the cache directory. Skip it if it starts
		// with a slash as they're probably setting a full server path
		if(substr($this->cache_dir, 0, 1)  != "/")
		{
			$this->cache_dir = realpath($this->cache_dir);
		}
		
		// Set the CSS directory. Skip it if it starts
		// with a slash as they're probably setting a full server path
		if(substr($this->css_server_path, 0, 1)  != "/")
		{
			$this->css_server_path = realpath($this->css_server_path);
		}
		
		// Set the asset directory. Skip it if it starts
		// with a slash as they're probably setting a full server path
		if(substr($this->assets_dir, 0, 1)  != "/")
		{
			$this->assets_dir = realpath($this->css_server_path."/".$this->assets_dir);
		}
	
		
		/******************************************************************************
		Check each of the paths
		******************************************************************************/
		
		foreach(array(
			$this->cache_dir, 
			$this->system_dir, 
			$this->css_server_path, 
			$this->assets_dir) as $key => $path)
		{
			if(!is_dir($path))
			{
				error("Error: The cache directory (".$path.") does not exist");
				exit();
			}
		}
	
		/******************************************************************************
		Define constants
		******************************************************************************/
		 
		 // Full path to the cache folder
		 define('CACHEPATH', $this->cache_dir);
		 
		 // Full path to the system folder
		 define('BASEPATH', $this->system_dir);
		 
		 // Full path to the CSS directory
		 define('CSSPATH', $this->css_server_path);
		 
		 // Url path to the css directory
		 define('URLPATH', $this->css_dir);
		
		 // Url path to the asset directory
		 define('ASSETPATH', $this->assets_dir);
		
	}
	
	// Loads a config.php file
	function load($file)
	{
		if (!file_exists($file))
		{
			stop("The config file ".$file.".php could not be loaded");
			return false;
		}
		
		include($file);
		
		if(!is_array($config) || !isset($config))
		{
			error("Your config file does not contain a config array");
			return false; 
		}
		
		// Make each of the config values a class variable
		foreach($config as $key => $setting)
		{
			$this->$key = $setting;
		}
		
		//$this->config = array_merge($this->config, $config);

		unset($config);
		
		return true; 
	}

	// Sets a config value
	function set($name, $value, $plugin = "")
	{
		if($plugin != '')
		{
			if (!isset($this->$plugin) || !is_array($this->$plugin))
			{
				return false;
			}
				
			$this->config[$plugin][$name] = $value;
		}
		else
		{
			$this->$name = $value;
		}
	}	
}