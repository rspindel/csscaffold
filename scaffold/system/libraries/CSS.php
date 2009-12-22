<?php

/**
 * CSS Utilities
 *
 * Has methods for interacting with the CSS string
 * and makes it very easy to find properties and values within the css
 * 
 * @author Anthony Short
 */
abstract class Scaffold_CSS
{
	/**
	 * Compresses down the CSS file. Not a complete compression,
	 * but enough to minimize parsing time.
	 *
	 * @author Anthony Short
	 * @return null
	 */	
	public static function compress($css)
	{		
		# Remove comments
		$css = self::remove_comments($css);
			
		# Remove extra white space
		$css = preg_replace('/\s+/', ' ', $css);
		
		# Remove line breaks
		$css = preg_replace('/\n|\r/', '', $css);
		
		return $css;
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
		$css = self::convert_entities('encode', $css);
		
		# Remove normal CSS comments
		$css = trim(preg_replace('#/\*[^*]*\*+([^/*][^*]*\*+)*/#', '', $css));
		
		# Remove single line comments. Thanks Naonak!
		$css = preg_replace('#(\s|$)//.*$#Umsi', '', $css);
		
		$css = self::convert_entities('decode', $css);

		return $css;
	}

	/**
	 * Transforms CSS into XML
	 *
	 * @author Shaun Inman
	 * @param $css
	 * @return string
	 */
	public static function to_xml($css)
	{
		# Strip comments to prevent parsing errors
		$xml = self::remove_comments($css);
		
		# These will break the xml, so we'll transform them for now
		$xml = self::convert_entities('encode', $xml);

		# Add semi-colons to the ends of property lists which don't have them
		$xml = preg_replace('/((\:|\+)[^;])*?\}/', "$1;}", $xml);

		# Transform properties
		$xml = preg_replace('/([-_A-Za-z*]+)\s*:\s*([^;}{]+)(?:;)/ie', "'<property name=\"'.trim('$1').'\" value=\"'.trim('$2').'\" />\n'", $xml);

		# Transform selectors
		$xml = preg_replace('/(\s*)([_@#.0-9A-Za-z\+~*\|\(\)\[\]^\"\'=\$:,\s-]*?)\{/me', "'$1\n<rule selector=\"'.preg_replace('/\s+/', ' ', trim('$2')).'\">\n'", $xml);
		
		# Close rules
		$xml = preg_replace('/\;?\s*\}/', "\n</rule>", $xml);
		
		# Indent everything one tab
		$xml = preg_replace('/\n/', "\r\t", $xml);
		
		# Tie it up with a bow
		$xml = '<?xml version="1.0" ?'.">\r<css>\r\t$xml\r</css>\r"; 

		return simplexml_load_string($xml);
	}

	/**
	 * Finds CSS 'functions'. These are things like url(), embed() etc.
	 *
	 * @author Anthony Short
	 * @param $name
	 * @param $capture_group
	 * @return array
	 */
	public static function find_functions($name, $css, $capture_group = "")
	{
		$regex =
		"/
			{$name}
			(
				\(
					( (?: [^()]+ | (?1) )* )
				\)
			)
		/sx";

		if(preg_match_all($regex, $css, $match))
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
	public static function find_at_group($group, $css)
	{
		$found = array();
		
		$regex = 
		"/
			# Group name
			@{$group}
			
			# Flag
			(?:
				\(( [^)]*? )\)
			)?
			
			[^{]*?

			(
				([0-9a-zA-Z\_\-\@*&]*?)\s*
				\{	
					( (?: [^{}]+ | (?2) )*)
				\}
			)

		/ixs";
			
		if(preg_match_all($regex, $css, $matches))
		{
			$found['groups'] = $matches[0];
			$found['flag'] = $matches[1];
			$found['content'] = $matches[4];
						
			foreach($matches[4] as $key => $value)
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

			return $found;		
		}
		
		return false;
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
	public static function find_selectors_with_property($property, $value = ".*?", $css = "")
	{		
		if(preg_match_all("/([^{}]*)\s*\{\s*[^}]*(".$property."\s*\:\s*(".$value.")\s*\;).*?\s*\}/sx", $css, $match))
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
	public static function find_properties_with_value($property, $value = ".*?", $css = "")
	{		
		# Make the property name regex-friendly
		#$property = str_replace('-', '\-', preg_quote($property));
		
		if(preg_match_all("/\{(?:[^\}]*({$property}\:\s*({$value})\s*\;).*?)\}/sx", $css, $match))
		{
			return $match;
		}
		else
		{
			return array();
		}
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
	public static function find_selectors($selector, $css = "", $recursive = "")
	{		
		if($recursive != "")
		{
			$recursive = "|(?{$recursive})";
		}

		$regex = 
			"/
				
				# This is the selector we're looking for
				({$selector})
				
				# Return all inner selectors and properties
				(
					([0-9a-zA-Z\_\-\*&]*?)\s*
					\{	
						(?P<properties>(?:[^{}]+{$recursive})*)
					\}
				)
				
			/xs";
		
		if(preg_match_all($regex, $css, $match))
		{
			return $match;
		}
		else
		{
			return array();
		}
	}
	
	/**
	 * Finds all selectors of a certain name in the css. This finds
	 * the selector in any context. eg #id or .class, #id, .class etc.
	 *
	 * @author Anthony Short
	 * @param $selector
	 * @param $css
	 * @return array
	 */
	public static function find_selector_names($selector, $css = "")
	{		
		# Get it ready to be put in regex
		$selector = Scaffold_Utils::preg_quote($selector);
		
		$regex = "/
			[^}]*
			{$selector}
			[^{]*
		/sx";

		if(preg_match_all($regex, $css, $match))
		{
			return $match;
		}
		else
		{
			return false;
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
	public static function find_property($property, $css = "")
	{ 		
		if(preg_match_all('/('.Scaffold_Utils::preg_quote($property).')\s*\:\s*(.*?)\s*\;/sx', $css, $matches))
		{
			return (array)$matches;
		}
		else
		{
			return array();
		}
	}
	
	/**
	 * Check if a selector exists
	 *
	 * @param $name
	 * @return boolean
	 */
	public static function selector_exists($name)
	{
		return preg_match('/'.preg_quote($name).'\s*?({|,)/', $this->css);
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
	public static function remove_properties($property, $value, $css = "")
	{
		if($css == "") $css =& self::$css;
		return preg_replace('/'.$property.'\s*\:\s*'.$value.'\s*\;/', '', $css);
	}
	
	/**
	 * Encodes or decodes parts of the css that break the xml
	 *
	 * @author Anthony Short
	 * @param $css
	 * @return string
	 */
	public static function convert_entities($action = 'encode', $css)
	{
		$css_replacements = array(
			'"' => '#SCAFFOLD-QUOTE#',
			'>' => '#SCAFFOLD-GREATER#',
			'&' => '#SCAFFOLD-PARENT#',
			'data:image/PNG;' => '#SCAFFOLD-IMGDATA-PNG#',
			'data:image/JPG;' => "#SCAFFOLD-IMGDATA-JPG#",
			'data:image/png;' => '#SCAFFOLD-IMGDATA-PNG#',
			'data:image/jpg;' => "#SCAFFOLD-IMGDATA-JPG#",
			'http://' => "#SCAFFOLD-HTTP#",
		);
		
		switch ($action)
		{
		    case 'decode':
		        $css = str_replace(array_values($css_replacements),array_keys($css_replacements), $css);
		        break;
		    
		    case 'encode':
		        $css = str_replace(array_keys($css_replacements),array_values($css_replacements), $css);
		        break;  
		}
	    
		return $css;
	}

}