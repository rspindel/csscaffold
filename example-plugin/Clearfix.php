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
		if( $found = find_selectors_with_property($css, 'clear', 'clearfix') )
		{
			// Turn them into a comma-separated string
			$selectors = join($found[1], ",");
			
			// Create the :after selectors remembering to add the extra :after to the end
			$afters = join($found[1], ":after,") . ":after";
			
			// Add the clearfix class
			$css .= ".clearfix,".$selectors."{display:block;}";
			
			// Add the cleafix:after classes
			$css .= ".clearfix:after,".$afters."{content:'\\0020';display:block;height:0;clear:both;visibility:hidden;overflow:hidden;}";
	
			// Remove all clear:clearfix; properties
			$css = remove_properties('clear','clearfix', $css);
		}
				
		return $css;
	}

}