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
	function pre_process($css)
	{				
		# Find the constants group and values
		$found = find_at_group('constants', $css);
		$colors = find_at_group('colors', $css);
		
		# Join them together
		$found = @array_merge_recursive($found, $colors);
		
		# Add default constants here
		self::set('asset_url', ASSETURL);
		self::set('scaffold_url', BASEURL);
		
		# Create our template style constants
		foreach($found['values'] as $key => $value)
		{
			unset(self::$constants[$key]);
			self::set($key, $value);
		}
		
		#self::get_xml_constants();
		
		# Remove the @constants groups
		$css = str_replace($found['groups'], array(), $css);
		
		return $css;
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
	public static function replace($css)
	{
		if (!empty(self::$constants))
		{
			foreach(self::$constants as $key => $value)
			{
				if($value != "")
				{
					$css = str_replace( "!" . $key, unquote($value), $css);
				}
			}
		}
		
		return $css;
	}
	
	/**
	 * Opens the constants.xml and builds an
	 * array of constants from it which we can add to our own
	 *
	 * @author Anthony Short
	 * @param $css
	 */
	function get_xml_constants()
	{		
		# Override any constants with our XML constants
		$xml = load_xml(ASSETPATH . '/xml/constants.xml');
		
		# Replace the constants in the array with the XML constants		
		foreach($xml->constant as $key => $constant)
		{
			self::set((string)$constant->name, (string) $constant->val);
		}
		
		unset($xml);
	}

}