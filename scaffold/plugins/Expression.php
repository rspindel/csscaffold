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
		# Find all of the math() functions
		if(preg_match_all('/
		
			eval
			\(
				[\'\"]?
				
				(([^);]++\)?|(?:2))*?)
				
				[\'\"]?
			\)
			
			/sx',
			CSS::$css, $matches)
		)
		{
			# Loop through them, stripping out anything but simple math
			# executing it and replacing it within the css	
			foreach($matches[0] as $key => $match)
			{				
				$expr =& $matches[1][$key];
				
				# Remove units
				$expr = preg_replace('/(px|em|%)/','',$expr); 
				
				# Remove quotes
				$expr = remove_all_quotes($expr);
				
				eval("\$result = ".$expr.";");
				
				if ($result)
				{
					CSS::replace($matches[0][$key], $result);
				}
				else
				{
					stop("Error: Eval: Can't process this function - " . $match);
				}
			}
		}
	}
	
}
