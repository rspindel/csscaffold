<?php

/**
 * CSS
 *
 * The CSS object. Has methods for interacting with the CSS string
 * and makes it very easy to find properties and values within the css
 * 
 * @author Anthony Short
 */
abstract class CSS
{
	/**
	 * The CSS string
	 *
	 * @var string
	 */
	public static $css;
	
	/**
	 * The constructor
	 *
	 * @author Anthony Short
	 * @param $css
	 * @return null
	 */
	public static function load($css)
	{
		self::$css = $css;
	}
	
	/**
	 * Appends the string to the css string
	 *
	 * @author Anthony Short
	 * @param $string
	 * @return null
	 */
	public static function append($string)
	{
		self::$css .= $string;
	}
	
	/**
	 * Compresses down the CSS file as much as possible
	 *
	 * @author Anthony Short
	 * @return null
	 */	
	public static function compress()
	{
		$css =& self::$css;
		
		# Remove comments
		$css = self::remove_comments($css);
			
		# Remove extra white space
		$css = preg_replace('/\s+/', ' ', $css);
		
		# Remove line breaks
		$css = preg_replace('/\n|\r/', '', $css);
	}

	/**
	 * Replaces a matched string with another matched string.
	 * Can be either a direct string replace or a regular expression.
	 *
	 * @author Anthony Short
	 * @param $match The string to match
	 * @param $replace Replace the match with this string
	 * @param $regex Is it a regular expression
	 * @return null
	 */
	public static function replace($match, $replace, $regex = false)
	{
		if($regex === true)
		{
			self::$css = preg_replace($match, $replace, self::$css);
			return true;
		}
		else
		{
			self::$css = str_replace($match, $replace, self::$css);
			return true;
		}
		
		return false;
	}
	
	/**
	 * Removes the string from the css rather than replacing it.
	 *
	 * @author Anthony Short
	 * @param $string
	 * @return null
	 */
	public static function remove($string)
	{
		if(is_array($string))
		{
			foreach($string as $key => $value)
			{
				self::replace($value, '');
			}
		}
		else
		{
			self::replace($string, '');
		}
	}
	
	/**
	 * Finds CSS 'functions'. These are things like url(), embed() etc.
	 *
	 * @author Anthony Short
	 * @param $name
	 * @param $capture_group
	 * @return null
	 */
	public static function find_functions($name, $capture_group = "")
	{
		if(preg_match_all('/'.$name.'\(([^\)]+)\)/', self::$css, $match))
		{
			return ($capture_group == "") ? $match : $match[$capture_group];
		}
		else
		{
			return array();
		}
	}
	
	/**
	 * Finds @groups within the css and returns
	 * an array with the values, and groups.
	 *
	 * @author Anthony Short
	 * @param $group string
	 * @param $css string
	 */
	public static function find_at_group($group)
	{	
		$found['values'] = $found['groups'] = array();
		
		if(preg_match_all('#@'.$group.'\s*\{\s*([^\}]+)\s*\}\s*#i', self::$css, $matches))
		{	
			$found['groups'] = $matches[0];
						
			foreach($matches[1] as $key => $value)
			{
				$a = explode(";", substr($value, 0, -1));
									
				foreach($a as $value)
				{
					$t = explode(":", $value);
					
					if(isset($t[1]))
					{
						$found['values'][trim($t[0])] = $t[1];
					}
				}
			}			
		}
		
		return $found;
	}
	
	/**
	 * FIND SELECTORS WITH PROPERTY
	 * 
	 * Finds selectors which contain a particular property
	 *
	 * @author Anthony Short
	 * @param $css
	 * @param $property string
	 * @param $value string
	 */
	public static function find_selectors_with_property($property, $value = ".*?")
	{
		if(preg_match_all("/([^{}]*)\s*\{\s*[^}]*(".$property."\s*\:\s*(".$value.")\s*\;).*?\s*\}/sx", self::$css, $match))
		{
			return $match;
		}
		else
		{
			return array();
		}
	}
	
	/**
	 * Finds all properties with a particular value
	 *
	 * @author Anthony Short
	 * @param $property
	 * @param $value
	 * @param $css
	 * @return array
	 */
	public static function find_properties_with_value($property, $value = ".*?")
	{
		# Make the property name regex-friendly
		$property = str_replace('-', '\-', preg_quote($property));
		
		if(preg_match_all("/\{([^\}]*({$property}\:\s*({$value})\s*\;).*?)\}/sx", self::$css, $match))
		{
			return $match;
		}
		else
		{
			return array();
		}
	}
	
	/**
	 * Alias for find_properties_with_value
	 *
	 * @author Anthony Short
	 * @return array
	 */
	public static function find_property_with_value($property, $value)
	{
		return self::find_properties_with_value($property, $value);
	}
		
	/**
	 * FIND SELECTORS
	 * 
	 * Finds a selector and returns it as string
	 *
	 * @author Anthony Short
	 * @param $selector string
	 * @param $css string
	 */
	public static function find_selectors($selector, $recursive = 1)
	{
		$regex = 
			"/
				
				# This is the selector we're looking for
				({$selector})
				
				# Return all inner selectors and properties
				(
					([0-9a-zA-Z\_\-\*&]*?)\s*
					\{	
						(?P<properties>(?:[^{}]+|(?{$recursive}))*)
					\}
				)
				
			/xs";
			
		# /($selector)\s*\{(([^{}]+)|(?R))*\}/sx
		
		if(preg_match_all($regex, self::$css, $match))
		{
			return $match;
		}
		else
		{
			return array();
		}
	}
	
	/**
	 * FIND PROPERTY
	 * 
	 * Finds all properties within a css string
	 *
	 * @author Anthony Short
	 * @param $property string
	 * @param $css string
	 */
	public static function find_property($property)
	{
		if(preg_match_all('/(?P<property_name>'.str_replace('-', '\-', preg_quote($property)).')\s*\:\s*(?P<property_value>.*?)\s*\;/', self::$css, $matches))
		{
			return (array)$matches;
		}
		else
		{
			return array();
		}
	}
	
	/**
	 * Alias for find_property
	 *
	 * @author Anthony Short
	 * @param $property
	 * @param $css
	 * @return array
	 */
	public static function find_properties($property)
	{
		return find_property($property);
	}
	
	/**
	 * REMOVE PROPERTIES
	 * 
	 * Removes all instances of a particular property from the css string
	 *
	 * @author Anthony Short
	 * @param $property string
	 * @param $value string
	 * @param $css string
	 */
	public static function remove_properties($property, $value)
	{
		return preg_replace('/'.$property.'\s*\:\s*'.$value.'\s*\;/', '', self::$css);
	}
	
	/**
	 * REMOVE CSS COMMENTS
	 * 
	 * Removes css style comments
	 *
	 * @author Anthony Short
	 * @param $css string
	 */
	public static function remove_comments($css)
	{
		return trim(preg_replace('#/\*[^*]*\*+([^/*][^*]*\*+)*/#', '', $css));
	}

	/**
	 * Transforms CSS into XML
	 *
	 * @author Shaun Inman
	 * @param $css
	 * @return string
	 */
	public static function to_xml()
	{
		$xml = trim(self::$css);
		
		# Strip comments to prevent parsing errors
		$xml = preg_replace('#(/\*[^*]*\*+([^/*][^*]*\*+)*/)#', '', $xml);
		
		# These will break the xml, so we'll transform them for now
		$xml = str_replace('"', '#SCAFFOLD-QUOTE#', $xml);
		$xml = str_replace('>','#SCAFFOLD-GREATER#', $xml);
		$xml = str_replace('&','#SCAFFOLD-PARENT#', $xml);
		$xml = str_replace('data:image/PNG;', "#SCAFFOLD-IMGDATA-PNG#", $xml);
		$xml = str_replace('data:image/JPG;', "#SCAFFOLD-IMGDATA-JPG#", $xml);
		
		# Transform properties
		$xml = preg_replace('/([-_A-Za-z]+)\s*:\s*([^;}{]+)(?:;)/ie', "'<property name=\"'.trim('$1').'\" value=\"'.trim('$2').'\" />'", $xml);
		
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
	 * Adds a selector to the CSS
	 *
	 * @author Anthony Short
	 * @param $selector
	 * @param $properties
	 * @return null
	 */
	public static function add($selector, $properties)
	{
		CSS::append($selector ."{". $properties . "}");
	}
	
	/**
	* Calculates the total amount of padding present
	* in a selector. This doesn't factor in cascading.
	*
	* @param   string   All of the properties of a selector
	* @return  string	The total amount of left and right padding combined
	*/
	public static function get_padding($properties)
	{
		$padding = $paddingleft = $paddingright = 0;
		
		# Get the padding (in its many different forms)

		if (preg_match_all('/padding\:(.+?)\;/x', $properties, $matches))
		{
			$padding = preg_split('/\s/', $matches[1][0]);
			$padding = str_replace("px", "", $padding);
			
			if (sizeof($padding) == 1)
			{
				$paddingright = $paddingleft = $padding[0];
			} 
			elseif (sizeof($padding) == 2 || sizeof($padding) == 3)
			{
				$paddingleft = $paddingright = $padding[1];
			}
			elseif (sizeof($padding) == 4)
			{
				$paddingright = $padding[1];
				$paddingleft = $padding[3];
			}
		}
	
		if (preg_match_all('/padding\-left\:(.+?)\;/x', $properties, $paddingl))
		{
			$paddingleft = str_replace('px', '', $paddingl[1][0]);
		}
		
		if (preg_match_all('/padding\-right\:(.+?)\;/x', $properties, $paddingr))
		{
			$paddingright = str_replace('px', '', $paddingr[1][0]);
		}

		return $paddingleft + $paddingright;
		
	}

	/**
	* Calculates the total amount of border present
	* in a selector. This doesn't factor in cascading.
	*
	* @param   string   All of the properties of a selector
	* @return  string	The total amount of left and right border combined
	*/
	public static function get_border($properties)
	{		
		$border = $borderleft = $borderright = 0;

		if (preg_match_all('/border\:.+?\;/x', $properties, $matches))
		{
			if (preg_match_all('/\d.?px/', $matches[0][0], $match))
			{
				$borderw = str_replace('px','',$match[0][0]);
				
				$borderleft = $borderright = $borderw;
			}
		}	
		if (preg_match_all('/border\-left\:.+?\;/x', $properties, $matches))
		{
			if (preg_match_all('/\d.?px/', $matches[0][0], $match))
			{
				$borderleft = str_replace('px','',$match[0][0]);
			}
		}
		
		if (preg_match_all('/border\-right\:.+?\;/x', $properties, $matches))
		{
			if (preg_match_all('/\d.?px/', $matches[0][0], $match))
			{
				$borderright = str_replace('px','',$match[0][0]);
			}
		}
			
		return $borderleft + $borderright;
		
	}


}