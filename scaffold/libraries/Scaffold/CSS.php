<?php

/**
 * CSS Utilities
 *
 * Has methods for interacting with the CSS string
 * and makes it very easy to find properties and values within the css
 * 
 * @package CSScaffold
 * @author Anthony Short
 */
abstract class Scaffold_CSS
{
	/**
	 * Compresses down the CSS file. Not a complete compression,
	 * but enough to minimize parsing time.
	 *
	 * @return string $css
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
	 * Removes css comments
	 *
	 * @return string $css
	 */
	public static function remove_comments($css)
	{
		$css = self::convert_entities('encode', $css);
		
		# Remove normal CSS comments
		$css = trim(preg_replace('#/\*[^*]*\*+([^/*][^*]*\*+)*/#', '', $css));
		
		$css = self::convert_entities('decode', $css);

		return $css;
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
				\s*\(\s*
					( (?: (?1) | [^()]+ )* )
				\s*\)\s*
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
		$property = Scaffold_Utils::preg_quote($property);
		$regex = "/ ({$property}) \s*\:\s* ({$value}) /sx";
			
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
	public static function selector_exists($name,$css)
	{
		return preg_match('/'.preg_quote($name).'\s*?({|,)/', $css);
	}
		
	/**
	 * Removes all instances of a particular property from the css string
	 *
	 * @author Anthony Short
	 * @param $property string
	 * @param $value string
	 * @param $css string
	 */
	public static function remove_properties($property, $value, $css = "")
	{
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