<?php defined('BASEPATH') OR die('No direct access allowed.');

/**
 * Math
 *
 * Lets you do simple math equations within your css via math()
 *
 * @author Anthony Short
 * @dependencies None
 **/
class Expression extends Plugins
{
	/**
	 * The final process before it is cached. This is usually just
	 * formatting of css or anything else just before it's cached
	 *
	 * @author Anthony Short
	 * @param $css
	*/
	function post_process()
	{
		CSS::$css = $this->parse_expressions();
	}
	
	/**
	 * Finds eval chunks in property values
	 *
	 * @author Anthony Short
	 * @return null
	 */
	public static function find_expressions($css)
	{
		return CSS::find_properties_with_value('[a-zA-Z-]++','[^;]*?(\[[\'\"]?([^]]*?)[\'\"]?\])[^;]*?', $css);
		#preg_match_all('/\:[^;]*?(\[[\'\"]?([^]]*?)[\'\"]?\])[^;]*?\;/', $css, $found);
		#stop($found);
	}
	
	/**
	 * Parses the expressions in an array from find_expressions
	 *
	 * @author Anthony Short
	 * @return null
	 */
	public static function parse_expressions($css = "")
	{
		# If theres no css string given, use the master css
		if($css == "") $css = CSS::$css;
		
		# Find all of the property values which have [] in them.
		if($matches = self::find_expressions($css))
		{			
			foreach($matches[0] as $key => $value)
			{
				stop($matches);
				$expr =& $matches[5][0];
				
				# Remove units
				$expr = preg_replace('/(px|em|%)/','',$expr); 
				
				# Remove quotes
				$expr = remove_all_quotes($expr);
				
				eval("\$result = ".$expr.";");
				
				if($result)
				{
					# Replace the string in the css
					$updated 	= str_replace($matches[4][0], $result, $value);
					$css 		= str_replace($matches[0][0], $updated, $css);
				}
				else
				{
					stop("Error: Eval: Can't process this function - " . $value);
				}
								
				# If we can find more expressions in this selector, parse them.
				$css = self::parse_expressions($css);
			}
		}
		
		return $css;
	}
	
}
