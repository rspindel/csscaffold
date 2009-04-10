<?php

/**
 * Core
 *
 * The super object for the whole of the app. Based
 * on the awesome Kohana php framework.
 *
 * @package csscaffold
 * @author Anthony Short
 **/
abstract class Core
{	
	// The singleton instance of the controller
	public static $instance;
	
	// The location of the cache file
	public static $cached_file; 
	
	// Configuration
	public static $configuration;
	
	// The modified time of the cache file
	public static $cached_mod_time;
	
	// Holds the user agent information of the user
	public static $user_agent;

	function setup()
	{
		self::$user_agent = ( ! empty($_SERVER['HTTP_USER_AGENT']) ? trim($_SERVER['HTTP_USER_AGENT']) : '');
		//self::$instance =& $this;
	}

	public static function & get_instance()
	{
		return self::$instance;
	}
	
	/**
	* -------------------------------------------------------------------------
	*	Caching
	* -------------------------------------------------------------------------
	*/
	
	public static function clear_cache()
	{
		unlink(self::$cached_file);
	}
	
	public static function empty_cache()
	{		
		$f = read_dir(CACHEPATH);
		
		foreach($f as $file)
		{
			if(substr($file, -3) == 'css')
			{
				unlink($file);
			}
		}
	}
	
	public static function write_cache($data, $mod_time)
	{
		self::cache_exists();
		
		file_put_contents(self::$cached_file, $data);
		
		//$css_handle = fopen(self::$cached_file, 'w');
		//fwrite($css_handle, $data);
		//fclose($css_handle);
		//chmod(self::$cached_file, 0777);
		touch(self::$cached_file, $mod_time);
	}
		
	// Create hash of query string to allow variables to be cached
	public static function generate_hash($args = array())
	{
		ksort($args);
		return md5(serialize($args));
	}
	
	// Make sure the cache exists
	public static function cache_exists()
	{
		if (self::$cached_file != CACHEPATH && !is_dir(CACHEPATH))
		{
			$path = CACHEPATH;
			$dirs = explode('/', self::$relative_dir);
			foreach ($dirs as $dir)
			{
				$path .= '/'.$dir;
				mkdir($path, 0777);
			}
		}
		return TRUE;
	}
	
	// Set the cache
	public static function set_cache($flags, $recache = FALSE)
	{		
		// Generate checksum based on plugin flags
		$checksum = self::generate_hash($flags);
		
		// Determine the name of the cache file
		$cached_file = CACHEPATH.preg_replace('#(.+)(\.css)$#i', "$1-{$checksum}$2", self::config('relative_file'));

		// Turn off recaching if the cache is locked
		if ($recache === TRUE && self::config('cache_lock') === TRUE)
		{
			$recache = FALSE;
		}
		
		// Check to see if we should delete the cache file
		if ($recache === TRUE && file_exists($cached_file))
		{
			self::empty_cache();
		}
		
		// Save it to the member var
		self::$cached_file = $cached_file;
		
		// When was the cache last modified
		if (file_exists(self::$cached_file))
		{
			$cached_mod_time =  (int) filemtime(self::$cached_file);
		}
		else
		{
			$cached_mod_time = 0;
		}
		
		self::config_set('cached_mod_time', $cached_mod_time);
		
		return TRUE;
	}
	
	/**
	* -------------------------------------------------------------------------
	*	Config
	* -------------------------------------------------------------------------
	*/
	
	/**
	 * Get a config item or group.
	 *
	 * @param   string   item name
	 * @param   boolean  force a forward slash (/) at the end of the item
	 * @param   boolean  is the item required?
	 * @return  mixed
	 */
	public static function config($key, $group = "")
	{
		if (self::$configuration === NULL)
		{			
			// Load the config file
			require BASEPATH.'config/config.php'; 
			
			// If the config file doesn't contain an array
			if(!is_array($config) || !isset($config))
			{
				error("Your config file does not contain a config array");
				return false; 
			}
			
			// Set the config values in our core config
			self::$configuration = $config;
			
			// Remove the config array
			unset($config);
			
			return TRUE;
		}
		
		if ($group != "")
		{
			return self::$configuration[$group][$key];
		}
		elseif (is_array($key))
		{
			return TRUE;
		}
		else
		{
			return self::$configuration[$key];
		}
	}

	/**
	 * Sets a configuration item, if allowed.
	 *
	 * @param   string   config key string
	 * @param   string   config value
	 * @return  boolean
	 */
	public static function config_set($key, $value)
	{
		// Do this to make sure that the config array is already loaded
		self::config($key);
		
		// Used for recursion
		$conf =& self::$configuration;
		
		if(is_array($key) && is_array($value))
		{
			foreach ($key as $k => $name)
			{
				// Set the item
				$conf[$name] = $value[$k];
			}
		}
		else
		{
			// Set the item
			$conf[$key] = $value;
		}

		return TRUE;
	}
	
	/**
	* Retrieves current user agent information:
	* keys:  browser, version, platform, mobile, robot, referrer, languages, charsets
	* tests: is_browser, is_mobile, is_robot, accept_lang, accept_charset
	*
	* @param   string   key or test name
	* @param   string   used with "accept" tests: user_agent(accept_lang, en)
	* @return  array    languages and charsets
	* @return  string   all other keys
	* @return  boolean  all tests
	*/
	public static function user_agent($key = 'agent', $compare = NULL)
	{
		static $info;

		// Return the raw string
		if ($key === 'agent')
			return self::$user_agent;

		if ($info === NULL)
		{
			// Parse the user agent and extract basic information
			require BASEPATH.'config/user_agents.php'; 
			
			// Set the config values in our core config
			self::$configuration['user_agents'] = $config;
			
			// Remove the config array
			unset($config);
			
			$agents = self::$configuration['user_agents'];

			foreach ($agents as $type => $data)
			{
				foreach ($data as $agent => $name)
				{
					if (stripos(self::$user_agent, $agent) !== FALSE)
					{
						if ($type === 'browser' AND preg_match('|'.preg_quote($agent).'[^0-9.]*+([0-9.][0-9.a-z]*)|i', self::$user_agent, $match))
						{
							// Set the browser version
							$info['version'] = $match[1];
						}

						// Set the agent name
						$info[$type] = $name;
						break;
					}
				}
			}
		}

		if (empty($info[$key]))
		{
			switch ($key)
			{
				case 'is_robot':
				case 'is_browser':
				case 'is_mobile':
					// A boolean result
					$return = ! empty($info[substr($key, 3)]);
				break;
				case 'languages':
					$return = array();
					if ( ! empty($_SERVER['HTTP_ACCEPT_LANGUAGE']))
					{
						if (preg_match_all('/[-a-z]{2,}/', strtolower(trim($_SERVER['HTTP_ACCEPT_LANGUAGE'])), $matches))
						{
							// Found a result
							$return = $matches[0];
						}
					}
				break;
				case 'charsets':
					$return = array();
					if ( ! empty($_SERVER['HTTP_ACCEPT_CHARSET']))
					{
						if (preg_match_all('/[-a-z0-9]{2,}/', strtolower(trim($_SERVER['HTTP_ACCEPT_CHARSET'])), $matches))
						{
							// Found a result
							$return = $matches[0];
						}
					}
				break;
				case 'referrer':
					if ( ! empty($_SERVER['HTTP_REFERER']))
					{
						// Found a result
						$return = trim($_SERVER['HTTP_REFERER']);
					}
				break;
			}

			// Cache the return value
			isset($return) and $info[$key] = $return;
		}

		if ( ! empty($compare))
		{
			// The comparison must always be lowercase
			$compare = strtolower($compare);

			switch ($key)
			{
				case 'accept_lang':
					// Check if the lange is accepted
					return in_array($compare, self::user_agent('languages'));
				break;
				case 'accept_charset':
					// Check if the charset is accepted
					return in_array($compare, self::user_agent('charsets'));
				break;
				default:
					// Invalid comparison
					return FALSE;
				break;
			}
		}

		// Return the key, if set
		return isset($info[$key]) ? $info[$key] : NULL;
	}

}