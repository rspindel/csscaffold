<?php defined('BASEPATH') OR die('No direct access allowed.');

/**
 * NestedSelectors
 *
 * @author Anthony Short
 * @dependencies None
 **/
class NestedSelectors extends Plugins
{
	
	/**
	 * The main processing function called by Scaffold. MUST return $css!
	 *
	 * @author Anthony Short
	 * @return $css string
	 */
	function process()
	{
		$xml = CSS::to_xml();
		
		$css = "";
		
		foreach($xml->rule as $key => $value)
		{
			$css .= self::parse_rule($value);
		}

		$css = str_replace('#SCAFFOLD-GREATER#', '>', $css);
		$css = str_replace('#SCAFFOLD-QUOTE#', '"', $css);
		$css = str_replace("#SCAFFOLD-IMGDATA-PNG#", "data:image/PNG;", $css);
		$css = str_replace("#SCAFFOLD-IMGDATA-JPG#", "data:image/JPG;", $css);
		
		CSS::$css = $css;
	}
	
	/**
	 * Parse the css selector rule
	 *
	 * @author Anthony Short
	 * @param $rule
	 * @return return type
	 */
	private function parse_rule($rule, $parent = '')
	{
		$css_string = "";
		$property_list = "";

		# Get the selector and store it away
		foreach($rule->attributes() as $type => $value)
		{
			$child = (string)$value;
			
			# If the child references the parent selector
			if (strstr($child, "#SCAFFOLD-PARENT#"))
			{				
				# If there are listed parents eg. #id, #id2, #id3
				if (strstr($parent, ","))
				{
					$single_parents = explode(",", $parent);
										
					foreach($single_parents as $single_parent_key => $single_parent)
					{
						$single_parents[$single_parent_key] = str_replace("#SCAFFOLD-PARENT#", $single_parent, $child);
					}
					
					$parent = implode(",",$single_parents);
				}
				else
				{
					$parent = str_replace("#SCAFFOLD-PARENT#", $parent, $child);
				}
			}
			else
			{
				# If there are listed parents eg. #id, #id2, #id3
				if (strstr($parent, ","))
				{
					$single_parents = explode(",", $parent);
											
					foreach($single_parents as $single_parent_key => $single_parent)
					{
						$single_parents[$single_parent_key] = "$single_parent $child";
					}
					
					$parent = implode(",",$single_parents);
				}
				else
				{
					$parent = "$parent $child";
				}
			}
		}
				
		foreach($rule->property as $p)
		{
			$property = (array)$p->attributes(); 
			$property = $property['@attributes'];
			
			$property_list .= $property['name'].":".$property['value'].";";
		}
		
		$css_string .= $parent . "{" . $property_list . "}";

		foreach($rule->rule as $inner_rule)
		{
			$css_string .= self::parse_rule($inner_rule, $parent);
		}
		
		return $css_string;
	}

}