<?php

/**
 * Iterator
 *
 * @author Anthony Short
 * @dependencies None
 **/
class Iteration extends Scaffold_Module
{
	
	/**
	 * This function occurs before everything else
	 *
	 * @author Anthony Short
	 * @param $css
	 */
	public static function parse($css)
	{
		# Find all the @server imports
		$css = self::parse_fors($css);
		
		# Parse all the enumerate() functions
		$css = self::parse_enumerate($css);
		
		return $css;
	}
	
	/**
	 * Parses @fors within the css
	 *
	 * @author Anthony Short
	 * @param $string
	 * @return string
	 */
	public static function parse_fors($css)
	{
		if($found = self::find_fors($css))
		{			
			foreach($found[0] as $key => $value)
			{				
				$s = "";
				
				$from = $found[2][$key];
				$to = $found[3][$key];
				$var = $found[1][$key];
				
				for ($i = $from; $i <= $to; $i++)
				{
					$s .= str_replace("!{$var}", $i, $found[5][$key]);	
				}
				
				$css = str_replace($found[0][$key], $s, $css);				
			}
		}

		return $css;
	}
	
	/**
	 * Parses enumerate()'s within the CSS. Enumerate
	 * lets you build a selector based on a start and end
	 * number. Like columns-1 through columns-12.
	 *
	 * enumerate('.name-',start,end) { properties }
	 *
	 * Will produce something like:
	 *
	 * .name-1, .name-2, .name-3 { properties }
	 *
	 * @author Anthony Short
	 * @param $css
	 * @return $css string
	 */
	public static function parse_enumerate($css)
	{
		if( $found = Scaffold_CSS::find_functions('enumerate', $css) )
		{
			foreach($found[2] as $key => $value)
			{
				$params = explode(',', $value);
				
				$name 	= Scaffold_Utils::unquote($params[0]);
				$start 	= Scaffold_Utils::unquote($params[1]);
				$end 	= Scaffold_Utils::unquote($params[2]);
				
				$css = str_replace($found[0][$key], self::enumerate($name, $start, $end), $css);
			}
		}
		
		return $css;
	}
	
	/**
	 * Finds for statements in a string
	 *
	 * @author Anthony Short
	 * @param $string
	 * @return array
	 */
	public static function find_fors($string = "")
	{
		$recursive = '4'; 
		
		$regex = 
			"/
				
				# Find the @if's
				(?:@(?:for))\s\!(.*?)\sfrom\s(\d+)\sto\s(\d+)\s*
				
				# Return all inner selectors and properties
				(
					(?:[0-9a-zA-Z\_\-\*&]*?)\s*
					\{	
						((?:[^{}]+|(?{$recursive}))*)
					\}
				)
				
			/xs";
		
		if(preg_match_all($regex, $string, $match))
		{
			return $match;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Takes a string, a seperator and a max number and generates
	 * a long string from them
	 *
	 * @author Anthony Short
	 * @param $string
	 * @return string
	 */
	public static function enumerate($string, $min, $max, $sep = ",")
	{
		$ret = array();
		
		for ($i = $min; $i <= $max; $i++)
		{
			$ret[] = $string . $i;
		}
		
		return implode($sep, $ret);
	}


}