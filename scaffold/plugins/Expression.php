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
		return match('/\:[^;]*?(\[[\'\"]?([^]]*?)[\'\"]?\])[^;]*?\;/', $css);
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
			# So we don't double up on the same expression
			$originals 		= array_unique($matches[1]);
			$expressions 	= array_unique($matches[2]);
					
			foreach($expressions as $key => $expression)
			{								
				# Remove units and quotes
				$expression = preg_replace('/(px|em|%)/','', remove_all_quotes($expression)); 
				
				eval("\$result = ".$expression.";");
				
				if($result)
				{
					# Replace the string in the css
					$css = str_replace($originals[$key], $result, $css);
				}
				else
				{
					stop("Error: Eval: Can't process this function - " . $expression);
				}
			}
		}
		
		return $css;
	}
	
}
