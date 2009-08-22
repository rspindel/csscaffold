<?php defined('SYSPATH') OR die('No direct access allowed.');

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
	* Hold the configuration
	*
	* @var array
	**/
	public static $configuration;

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
			# Load the config file
			self::load(join_path(SYSPATH,'config.php'));
		}
	}
	
	/**
	 * Loads a config file into the global configuration
	 *
	 * @author Anthony Short
	 * @param $path
	 * @return boolean
	 */
	public static function load($path, $sub_array = "")
	{
		require($path);

		# If the config file doesn't contain an array
		if(!isset($config) || !is_array($config))
		{
			stop("Your config file does not contain a config array - $path");
			return false; 
		}
		
		# Set the config values in our core config
		if($sub_array == "")
		{
			self::$configuration = $config;
		}
		else
		{
			self::$configuration[$sub_array] = $config;
		}
		
		# Remove the config array
		unset($config);
		
		return true;
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
			self::init();
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
}