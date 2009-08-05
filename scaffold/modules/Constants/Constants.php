<?php defined('BASEPATH') OR die('No direct access allowed.');

/**
 * Constants
 *
 * Allows you to use constants within your css by defining them
 * within @constants and then using a property list.
 *
 * @author Anthony Short
 * @dependencies None
 **/
class Constants extends Plugins
{
	/**
	 * Stores all of the constants for the app
	 *
	 * @var array
	 */
	public static $constants = array();
	
	/**
	 * The pre-processing function occurs after the importing,
	 * but before any real processing. This is usually the stage
	 * where we set variables and the like, getting the css ready
	 * for processing.
	 *
	 * @author Anthony Short
	 * @param $css
	 */
	public static function parse()
	{				
		# Find the constants group and values
		$found 	= CSS::find_at_group('constants');

		# If there are some constants, let do it.
		if($found !== false)
		{
			# Create our template style constants
			foreach($found['values'] as $key => $value)
			{
				unset(self::$constants[$key]);
				self::set($key, $value);
			}
	
			# Remove the @constants groups
			CSS::replace($found['groups'], array());		
		}
	}
	
	/**
	 * Sets constants
	 *
	 * @author Anthony Short
	 * @param $key
	 * @param $value
	 * @return null
	 */
	public static function set($key, $value = "")
	{
		# So we can pass through a whole array
		# and set them all at once
		if(is_array($key))
		{
			foreach($key as $name => $val)
			{
				self::$constants[$name] = $val;
			}
		}
		else
		{
			self::$constants[$key] = $value;
		}	
	}
	
	/**
	 * Returns the constant value
	 *
	 * @author Anthony Short
	 * @param $key
	 * @return string
	 */
	public static function get($key)
	{
		return self::$constants[$key];
	}
		
	/**
	 * Replace constants
	 *
	 * @author Anthony Short
	 * @param $
	 * @return return type
	 */
	public static function replace()
	{
		if (!empty(self::$constants))
		{
			foreach(self::$constants as $key => $value)
			{
				if($value != "")
				{
					CSS::replace( "!{$key}", unquote($value));
				}
			}
		}
	}

}