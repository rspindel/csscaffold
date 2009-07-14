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
	function post_process($css)
	{	
		# Find all of the math() functions
		if(preg_match_all('/eval\([\'\"]?((?:[^);]++|\))*)[\'\"]?\)/', $css, $matches))
		{			
			# Loop through them, stripping out anything but simple math
			# executing it and replacing it within the css	
			foreach($matches[1] as $key => $match)
			{
				$match = preg_replace('/[a-zA-Z]*/','',$match); # Only include the simple math operators
				$match = remove_all_quotes($match);
				
				eval("\$result = ".$match.";");
				
				if ($result)
				{
					$css = str_replace($matches[0][$key], $result, $css);
				}
				else
				{
					stop("Error: Eval: Can't process this function - " . $match);
				}
			}
		}

		return $css;
	}
	
}

?>