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
 * @author Anthony Short
 * @dependencies none
 **/
class Conditional extends Plugins
{
	/**
	 * Parses a string for CSS-style conditionals
	 *
	 * @param $string A string of css
	 * @return void
	 **/
	public static function parse($string = "")
	{
		if($string == "") $string =& CSS::$css;
		
		# Find all @if, @else, and @elseif's groups
		if($found = self::find_conditionals($string))
		{
			# Go through each one
			foreach($found[1] as $key => $value)
			{
				$logic = "if($value){ \$result = 1; } else { \$result = 0; }";

				# Parse the args
				@eval($logic);
				
				# When one of them is if true, replace the whole group with the contents of that if and continue
				if($result == 1)
				{
					$string = str_replace($found[0][$key], $found[3][$key], $string);
				}
				# If there is an @else
				elseif($found[5] != "")
				{
					$string = str_replace($found[0][$key], $found[7][$key], $string);
				}
				else
				{
					$string = str_replace($found[0][$key], '', $string);
				}	
			}
		}
		return $string;
	}
	
	/**
	 * Finds if statements in a string
	 *
	 * @author Anthony Short
	 * @param $string
	 * @return array
	 */
	public static function find_conditionals($string = "")
	{
		$recursive = 2; 
		
		$regex = 
			"/
				
				# Find the @if's
				(?:@(?:if|elseif))\((.*?)\)
				
				# Return all inner selectors and properties
				(
					(?:[0-9a-zA-Z\_\-\*&]*?)\s*
					\{	
						((?:[^{}]+|(?{$recursive}))*)
					\}
				)
				
				\s*
				
				(
					# Find the @elses if they exist
					(@else)

					# Return all inner selectors and properties
					(
						(?:[0-9a-zA-Z\_\-\*&]*?)\s*
						\{	
							((?:[^{}]+|(?{$recursive}))*)
						\}
					)
				)?
				
			/xs";
		
		if(preg_match_all($regex, $string, $match))
		{
			return $match;
		}
		else
		{
			return array();
		}

		#return CSS::find_selectors('(?P<name>@if)(\((?P<args>.*?)\))?', 5, $string);
	}
	
}