<?php defined('BASEPATH') OR die('No direct access allowed.');

/**
 * Conditional
 *
 * Lets you use if/else statements within your css
 *
 * eg. 
 *	@if(){} 
 *	@elseif() {} 
 *	@else {}
 *
 * Currently only works with browser testing
 *
 * @author Anthony Short
 * @dependencies none
 **/
class Conditional extends Plugins
{
	/**
	 * Process
	 *
	 * @author Anthony Short
	 * @param $css
	*/
	function process($css)
	{
		# Find all @if, @else, and @elseif's groups
		$found = find_selectors('(?P<name>@if)(\((?P<args>.*?)\))?', $css, 4);
		$args = $found['args'];
		
		# Go through each one
		foreach($args as $key => $value)
		{
			$logic = "if($value){ \$result = 1; } else { \$result = 0; }";

			# Parse the args
			eval($logic);
			
			# When one of them is if true, replace the whole group with the contents of that if and continue
			if($result == 1)
			{
				$css = str_replace($found[0][$key], $found['properties'][$key], $css);
			}
			else
			{
				$css = str_replace($found[0][$key], '', $css);
			}	
		}
		
		return $css;
	}
	
}