<?php defined('BASEPATH') OR die('No direct access allowed.');

/**
 * Config
 *
 * The global configuration options for the app
 * 
 * @author Anthony Short
 */
abstract class Config
{
	/**
	* The singleton instance of the controller
	*
	* @var object
	**/
	public static $instance;
	
	/**
	* Hold the configuration
	*
	* @var array
	**/
	public static $configuration;

	/**
	* Returns the instance of the class
	*
	* @return self
	* @author Anthony Short
	**/
	public static function & get_instance()
	{
		return self::$instance;
	}
	
	/**
	 * Initializes the config
	 *
	 * @author Anthony Short
	 * @return boolean
	 */
	private function init()
	{
		if (self::$configuration === NULL)
		{
			// Load the config file
			require join_path(BASEPATH,'config/config.php') ; 
			
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
			
			return true;
		}
	}

	/**
	 * Get a config item or group.
	 *
	 * @param   string   item name
	 * @param   boolean  force a forward slash (/) at the end of the item
	 * @param   boolean  is the item required?
	 * @return  mixed
	 */
	public static function get($key, $group = "")
	{
		if (self::$configuration === NULL)
		{			
			// Load the config file
			require join_path(BASEPATH,'config/config.php') ; 
			
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
			if(isset(self::$configuration[$key]))
			{
				return self::$configuration[$key];
			} 
		}
	}

	/**
	 * Sets a configuration item, if allowed.
	 *
	 * @param   string   config key string
	 * @param   string   config value
	 * @return  boolean
	 */
	public static function set($key, $value = '')
	{
		// Do this to make sure that the config array is already loaded
		self::init();
		
		// Used for recursion
		$conf =& self::$configuration;
		
		# If they're both arrays
		if(is_array($key) && is_array($value))
		{
			foreach ($key as $k => $name)
			{
				$conf[$name] = $value[$k];
			}
		}
		
		# If we only gave it an array
		elseif(is_array($key) && $value == '')
		{
			foreach($key as $name => $val)
			{
				$conf[$name] = $val;
			}
		}
		
		# Otherwise, do it normally
		else
		{
			$conf[$key] = $value;
		}

		return true;
	}
	
	/**
	 * Sets a group configuration
	 *
	 * @param   string   config key string
	 * @param   string   config value
	 * @return  boolean
	 */
	public static function __set($key, $value)
	{
		# Do this to make sure that the config array is already loaded
		self::init();
		
		# Used for recursion
		$conf =& self::$configuration;
		
		# Store it away
		$conf[$key] = $value;
	}


}