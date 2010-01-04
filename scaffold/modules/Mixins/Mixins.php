<?php

/**
 * Mixins
 *
 * Allows you to use SASS-style mixins, essentially assigning classes
 * to selectors from within your css. You can also pass arguments through
 * to the mixin.
 * 
 * @author Anthony Short
 */
class Mixins extends Scaffold_Module
{

	/**
	 * Stores the mixins for debugging purposes
	 *
	 * @var array
	 */
	public static $mixins = array();
	
	/**
	 * Holds the base mixins
	 *
	 * @var array
	 */
	public static $bases = array();

	/**
	 * The main processing function called by Scaffold. MUST return $css!
	 *
	 * @author Anthony Short
	 * @return $css string
	 */
	public static function parse($css)
	{
		$bases =& self::$bases;
		
		# Finds any selectors starting with =mixin-name
		if( $found = Scaffold_CSS::find_selectors('\=(?P<name>[0-9a-zA-Z_-]*)(\((?P<args>.*?)\))?', $css, 5) )
		{
			# Just to make life a little easier
			$full_base 		= $found[0];
			$base_names 	= $found['name'];
			$base_args 		= $found['args'];
			$base_props 	= $found['properties'];
			
			# Clean up memory
			unset($found);
			
			# Puts the mixin bases into a more suitable array
			foreach($base_names as $key => $value)
			{	
				$bases[$value]['properties'] = $base_props[$key];
				
				# If there are mixin arguments, add them
				$bases[$value]['params'] = ( $base_args[$key] != "" ) ? explode(',', $base_args[$key]) : array();
			}
						
			# Store this away for debugging
			self::$mixins = $bases;
			
			# Remove all of the mixin bases
			$css = str_replace($full_base,'',$css);
			
			# Clean up memory
			unset($full_base, $base_names, $base_args, $base_props);
			
			# Find the mixins
			if($mixins = self::find_mixins($css))
			{
				# Loop through each of the found +mixins
				foreach($mixins[2] as $mixin_key => $mixin_name)
				{
					$css = str_replace($mixins[0][$mixin_key], self::build_mixins($mixin_key, $mixins), $css);
				}
			}
			
			# Clean up
			unset($bases, $mixins);
		}

		return $css;
	}
	
	/**
	 * Encodes a string for use in regex
	 *
	 * @author Anthony Short
	 * @param $str
	 * @return return type
	 */
	public static function regexerize($str)
	{
		return str_replace('-','\-',preg_quote($str));
	}
	
	/**
	 * Replaces the mixins with their properties
	 *
	 * @author Anthony Short
	 * @param $mixin_key - The bases array key corrosponding to the current mixin
	 * @param $mixins - An array of found mixins
	 * @return string
	 */
	public static function build_mixins($mixin_key, $mixins, $already_mixed = array())
	{
		$bases =& self::$bases;
		
		$mixin_name = $mixins[2][$mixin_key];
				
		if(isset($bases[$mixin_name]))
		{	
			$base_properties = $bases[$mixin_name]['properties'];
							
			# If there is no base for that mixin and we aren't in a recursion loop
			if(is_array($bases[$mixin_name]) AND !in_array($mixin_name, $already_mixed) )
			{
				$already_mixed[] = $mixin_name;
				
				# Parse the parameters of the mixin
				$params = self::parse_params($mixins[0][$mixin_key], $mixins[4][$mixin_key], $bases[$mixin_name]['params']);
				
				# Create the property string
				$new_properties = str_replace(array_keys($params),array_values($params),$base_properties);
				
				# Parse conditionals if there are any in there
				$new_properties = self::parse_conditionals($new_properties);
	
				# Find nested mixins
				if($inner_mixins = self::find_mixins($new_properties))
				{
					# Loop through all the ones we found, skipping on recursion by passing
					# through the current mixin we're working on
					foreach($inner_mixins[0] as $key => $value)
					{
						# Parse the mixin and replace it within the property string
						$new_properties = str_replace($value, self::build_mixins($key, $inner_mixins, $already_mixed), $new_properties);
					}
				}	
							
				# Clean up memory
				unset($inner_mixins, $params, $mixins);

				return preg_replace('/^(\s|\n|\r)*|(\n|\r|\s)*$/','',$new_properties);
			}
			elseif(in_array($mixin_name, $already_mixed))
			{
				Scaffold_Log::log('Recursion in mixin - ' . $mixin_name,1);
			}
		}
		else
		{
			Scaffold_Log::log('Missing mixin - ' . $mixin_name,2);
		}
		
	}
	
	/**
	 * Finds +mixins
	 *
	 * @author Anthony Short
	 * @param $string
	 * @return array
	 */
	public static function find_mixins($string)
	{	
		return Scaffold_Utils::match('/\+(([0-9a-zA-Z_-]*?)(\((.*?)\))?)\;/xs', $string);
	}
	
	/**
	 * Parses the parameters of the base
	 *
	 * @author Anthony Short
	 * @param $params
	 * @return array
	 */
	public static function parse_params($mixin_name, $params, $function_args = array())
	{
		$parsed = array();
		
		# Make sure any commas inside ()'s, such as rgba(255,255,255,0.5) are encoded before exploding
		# so that it doesn't break the rule.
		if(preg_match_all('/\([^)]*?,[^)]*?\)/',$params, $matches))
		{
			foreach($matches as $key => $value)
			{
				$original = $value;
				$new = str_replace(',','#COMMA#',$value);
				$params = str_replace($original,$new,$params);
			}
		}

		$mixin_params = ($params != "") ? explode(',', $params) : array();
		
		# Loop through each function arg and create the parsed params array
		foreach($function_args as $key => $value)
		{
			$v = explode('=', $value);			

			# If the user didn't include one of the params, we'll check to see if a default is available			
			if(count($mixin_params) == 0 || !isset($mixin_params[$key]))
			{	
				# If there is a default value for the param			
				if(strstr($value, '='))
				{
					$parsed_value = Scaffold_Utils::unquote( trim($v[1]) );
					$parsed[trim($v[0])] = (string)$parsed_value;
				}
				
				# Otherwise they've left one out
				else
				{
					throw new Exception("Missing mixin parameter - " . $mixin_name);
				}
			}
			else
			{

				$value = (string)Scaffold_Utils::unquote(trim($mixin_params[$key]));
				$parsed[trim($v[0])] = str_replace('#COMMA#',',',$value);
			}		
		}

		return $parsed;
	}
	
	/**
	 * Parses a string for CSS-style conditionals
	 *
	 * @param $string A string of css
	 * @return void
	 **/
	public static function parse_conditionals($string = "")
	{		
		# Find all @if, @else, and @elseif's groups
		if($found = self::find_conditionals($string))
		{
			# Go through each one
			foreach($found[1] as $key => $value)
			{
				$result = false;
				
				# Find which equals sign was used and explode it
				preg_match("/\!=|\!==|===|==/", $value, $match); 
				
				# Explode it out so we can test it.
				$exploded = explode($match[0], $value);
				$val = trim($exploded[0]);

				if(preg_match('/[a-zA-Z]/', $val) && (strtolower($val) != "true" && strtolower($val) != "false") )
				{					
					$value = str_replace($val, "'$val'", $value);
				}
				
				eval("if($value){ \$result = true;}");
				
				# When one of them is if true, replace the whole group with the contents of that if and continue
				if($result)
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
				(?:@(?:if))\((.*?)\)
				
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
	}

}