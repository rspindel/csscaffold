<?php

/**
 * Multiple_properties
 *
 * You can target multiple properties at once by using
 * commas to separate property names.
 * 
 * @author Anthony Short
 * @dependencies None
 */
class Multiple_properties extends Plugins
{
	/**
	 * Post process
	 *
	 * @author Anthony Short
	 * @param $css
	 * @return string
	 */
	function process()
	{
		# Find all selectors with properties with commas in them
		if ( preg_match_all('/(((?:[a-zA-Z-]++\,\s*)|(?3))+[a-zA-Z-]++\s*)\:([^{;]*)\;/', CSS::$css, $matches) )
		{
			foreach($matches[0] as $key => $value)
			{
				# Explode it at the commas
				$properties = explode(',', $matches[1][$key]);
				
				# Create the properties
				$values = ":".$matches[3][$key].";";
				$properties = implode($values, $properties) . $values;
				
				# String replace the old comma'd property name with the new properties
				CSS::replace($value, $properties);
			}
		}
	}
}