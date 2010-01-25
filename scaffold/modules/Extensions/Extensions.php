<?php

/**
 * Custom_Properties
 *
 * Allows you to create new properties by dumping a function into
 * the properties folder.
 * 
 * @author Anthony Short
 */
class Extensions
{
	/**
	 * The list of created properties
	 * @var array
	 */
	public static $properties = array();
	
	/**
	 * List of available functions
	 * @var array
	 */
	public static $functions = array();
	
	/**
	 * Post Process
	 *
	 * @param $css
	 * @return string
	 */
	public static function post_process($css)
	{
		$css = self::load_custom_properties($css);
		$css = self::load_custom_functions($css);
		return $css;
	}

	/**
	 * Loads each of the property functions and parses them.
	 *
	 * @param $css
	 * @return $css string
	 */
	private static function load_custom_properties($css)
	{
		$properties = Scaffold::list_files('extensions/properties');
				
		foreach ($properties as $path)
		{
			require_once $path;
			
			$property_name = pathinfo($path, PATHINFO_FILENAME);
			self::$properties[] = $property_name;
			
			if($found = Scaffold_CSS::find_property($property_name, $css))
			{
				$originals = array_unique($found[0]);

				foreach($originals as $key => $value)
				{					
					$result = call_user_func('Scaffold_'.str_replace('-','_',$property_name), $found[2][$key] );
										
					if($result)
					{
						$css = str_replace($originals[$key],$result,$css);
					}
					else
					{
						Scaffold::error('Invalid property - <strong>' . $originals[$key] . '</strong>');
					}
				}
			}
		}

		return $css;
	}

	/**
	 * Find and load all of the functions
	 *
	 * @param $css
	 */
	private static function load_custom_functions($css)
	{
		$functions = Scaffold::list_files('extensions/functions');
		
		foreach ($functions as $path)
		{
			require_once $path;
			
			$function_name = pathinfo($path, PATHINFO_FILENAME);
			self::$functions[] = $function_name;

			if($found = Scaffold_CSS::find_functions($function_name,$css))
			{			
				$originals = array_unique($found[0]);

				foreach($originals as $key => $value)
				{					
					$result = call_user_func_array('Scaffold_'.$function_name, explode(',',$found[2][$key]) );
										
					if($result)
					{
						$css = str_replace($originals[$key],$result,$css);
					}
					else
					{
						Scaffold::error('Invalid function - <strong>' . $originals[$key] . '</strong>');
					}
				}
			}
		}

		return $css;
	}
}