<?php defined('BASEPATH') OR die('No direct access allowed.');

/**
 * Clearfix class
 *
 * @package CSScaffold
 **/
class Clearfix extends Plugins
{

	function process($css)
	{
		// Find all selectors with clear:clearfix;
		if(preg_match_all("/([^{}]*)\s*\{\s*[^}]*clear\s*\:\s*clearfix\s*\;.*?\s*\}/sx", $css, $match))
		{
			// Turn them into a comma-separated string
			$selectors = join($match[1], ",");
			
			// Create the :after selectors remembering to remove the extra :after to the end
			$afters = join($match[1], ":after,") . ":after";
			
			// Find the .clearfix classes
			if(preg_match_all('/\.clearfix([^:])/', $css, $clearfix))
			{
				foreach ($clearfix[0] as $key => $value)
				{
					// Append our selector string to it
					$css = str_replace($value, $selectors.",".$value, $css);
				}
			}
			// If they don't exist, create them
			else
			{
				$css .= ".clearfix,".$selectors."{display:block;}";
			}
			
			// Find the .clearfix:after classes
			if(preg_match_all('/\.clearfix\:after/', $css, $clearfix))
			{
				foreach ($clearfix[0] as $key => $value)
				{
					// Append our selector string to it
					$css = str_replace($value, $afters.",".$value, $css);
				}
			}
			// If they don't exist, create them
			else
			{
				$css .= ".clearfix:after,".$afters."{content:'\\0020';display:block;height:0;clear:both;visibility:hidden;overflow:hidden;}";
			}

			// Remove all clear:clearfix; properties
			$css = preg_replace('/clear\s*\:\s*clearfix\s*\;/', '', $css);
			
		}
		
		return $css;
	}

} // END Clearfix