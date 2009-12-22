<?php

/**
 * Custom_Properties
 *
 * Allows you to create new properties by dumping a function into
 * the properties folder.
 * 
 * @author Anthony Short
 */
class Custom_Properties extends Scaffold_Module
{
	/**
	 * The list of created properties
	 *
	 * @var array
	 */
	private static $properties = array();
	
	/**
	 * List of invalid properties/values
	 *
	 * @var array
	 */
	private static $invalid = array();

	/**
	 * Loads each of the property functions and parses them.
	 *
	 * @author Anthony Short
	 * @param $css
	 * @return $css string
	 */
	public static function process($css)
	{
		$properties = CSScaffold::list_files('extensions/properties');
				
		foreach ($properties as $path)
		{
			require_once $path;
			
			$property_name = pathinfo($path, PATHINFO_FILENAME);
			self::$properties[] = $property_name;
			
			if($found = Scaffold_CSS::find_property($property_name, $css))
			{
				$originals 	= array_unique($found[0]);

				foreach($originals as $key => $value)
				{					
					$result = call_user_func('Scaffold_'.str_replace('-','_',$property_name), $found[2][$key] );
										
					if($result)
					{
						$css = str_replace($originals[$key],$result,$css);
					}
					else
					{
						self::$invalid[$property_name][] = $originals[$key]; 
					}
				}
			}
		}

		return $css;
	}
}