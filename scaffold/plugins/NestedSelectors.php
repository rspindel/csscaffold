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
	function process($css)
	{
		$xml = self::css_to_xml($css);
		
		$css = "";
		
		foreach($xml->rule as $key => $value)
		{
			$css .= self::parse_rule($value);
		}

		$css = str_replace('#SCAFFOLD-GREATER#', '>', $css);
		$css = str_replace('#SCAFFOLD-QUOTE#', '"', $css);
		$css = str_replace("#SCAFFOLD-IMGDATA-PNG#", "data:image/PNG;", $css);
		
		return $css;
	}
	
	/**
	 * Transforms CSS into XML
	 *
	 * @author Shaun Inman
	 * @param $css
	 * @return string
	 */
	private function css_to_xml($css)
	{
		$xml = trim($css);
		
		# Strip comments to prevent parsing errors
		$xml = preg_replace('#(/\*[^*]*\*+([^/*][^*]*\*+)*/)#', '', $xml);
		
		# These will break the xml, so we'll transform them for now
		$xml = str_replace('"', '#SCAFFOLD-QUOTE#', $xml);
		$xml = str_replace('>','#SCAFFOLD-GREATER#', $xml);
		$xml = str_replace('&','#SCAFFOLD-PARENT#', $xml);
		$xml = str_replace('data:image/PNG;', "#SCAFFOLD-IMGDATA-PNG#", $xml);
		
		# Transform properties
		$xml = preg_replace('/([-_A-Za-z]+)\s*:\s*([^;}{]+)(?:;?)/ie', "'<property name=\"'.trim('$1').'\" value=\"'.trim('$2').'\" />'", $xml);
		
		# Transform selectors
		$xml = preg_replace('/(\s*)([_@#.0-9A-Za-z\+~*\|\(\)\[\]^\"\'=\$:,\s-]*?)\{/me', "'$1<rule selector=\"'.preg_replace('/\s+/', ' ', trim('$2')).'\">'", $xml);
		
		# Close rules
		$xml = preg_replace('/\!?\}/', '</rule>', $xml);
		
		# Indent everything one tab
		$xml = preg_replace('/\n/', "\r\t", $xml);
		
		# Tie it up with a bow
		$xml = '<?xml version="1.0" ?'.">\r<css>\r\t$xml\r</css>\r"; 
		
		return simplexml_load_string($xml);
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