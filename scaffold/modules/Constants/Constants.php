<?php

/**
 * Constants
 *
 * Allows you to use constants within your css by defining them
 * within @constants and then using a property list.
 *
 * You can set CSScaffold constants using XML. This allows you to create
 * constants using a CMS or by any other means to tie it in with your CSS.
 *
 * XML must be in this format:
 
 	<?xml version="1.0" ?>
 	<constants>
 	
 		<constant>
 			<name>Foo</name>
 			<value>Bar</value>
 		</constant>
 	
 	</constants>
 *
 * By default, it requires a constants.xml file in the root of the CSS directory.
 * You can change this in the plugins config.
 *
 * @author Anthony Short
 */
class Constants
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
	public static function pre_process($css)
	{
		$css = Scaffold_CSS::convert_entities('encode', $css);
		
		# Global Constants
		foreach(Scaffold::$config['Constants']['global'] as $key => $value)
		{
			self::set($key,$value);
		}
		
		# XML Constants
		if(isset(Scaffold::$config['Constants']['xml_path']))
		{
			self::load_xml_constants( Scaffold::$config['Constants']['xml_path'] );
		}
			
		# Find the constants group and values
		$found = Scaffold_CSS::find_at_group('constants', $css );

		# If there are some constants, let do it.
		if($found !== false)
		{
			# Create our template style constants
			foreach($found['values'] as $key => $value)
			{
				unset(self::$constants[$key]);
				$value = Scaffold_CSS::convert_entities('decode',$value);
				$value = Scaffold_Utils::unquote($value);
				$value = self::replace($value);
				self::set($key, $value);
			}

			# Remove the @constants groups
			$css = str_replace($found['groups'], array(), $css);		
		}
		
		$css = Scaffold_CSS::convert_entities('decode', $css);
		
		$css = self::replace($css);
		
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
	
	public static function replace($css)
	{
		# Pull the constants into the local scope as variables
		extract(self::$constants, EXTR_SKIP);
		
		# Remove unset variables from the string, so errors aren't thrown
		foreach(array_unique( Scaffold_Utils::match('/\{?\$([A-Za-z0-9_-]+)\}?/', $css, 1) ) as $value)
		{
			if(!isset($$value))
			{
				$css = preg_replace('/\{?\$'.$value.'\}?/', '',$css);
				Scaffold::error('Missing constant - ' . $value);
			}
		}

		$css = stripslashes( eval('return "' . addslashes($css) . '";') );
		
		# Replace the variables within the string like a normal PHP string
		return $css;
	}
	
	/**
	 * Loads constants from an XML file
	 *
	
	 * @param $param
	 * @return return type
	 */
	private static function load_xml_constants($file)
	{
		# If the xml file doesn't exist
		if(!file_exists($file))
		{
			Scaffold_Log::log("Missing constants XML file. The file ($file) doesn't exist.",1);
			return;
		}
		
		# Load the xml
		$xml = simplexml_load_file($file);
		
		# Loop through them and set them as constants
		foreach($xml->constant as $key => $value)
		{
			self::set((string)$value->name, (string)$value->value);
		}
	}
		
}