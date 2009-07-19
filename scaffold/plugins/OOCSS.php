<?php

/**
 * OOCSS
 *
 * Adds useful functions so that CSS can behave in a more 
 * object-orientated way.
 * 
 * @author Anthony Short
 */
class OOCSS extends Plugins
{
	/**
	 * Process
	 *
	 * @author Anthony
	 * @return null
	 */
	function process()
	{
		# Find all selectors with the property 'extends'.
		$found = CSS::find_selectors_with_property('extends');
		
		foreach($found as $key => $value)
		{
			# Remove the property from the css
			CSS::remove($found[2][$key]);
			
			# The selector we're going to find
			$find =& $found[3][$key];
			
			# The selector with with the extends property we're going to 
			# add to all of the rules we find.
			$selector =& $found[1][$key];
			
			# Find all the rules we need to add our selector too.
			# We've appended and prepended some extra regex to make sure
			# we find all of the selectors before and after the one we're looking for
			$rules = CSS::find_selectors("[^{}]*" . unquote($find) . "[^{]*");
			
			# Loop through each of the rules and replace them with
			# our new selector added to them.
			foreach($rules as $key => $rule)
			{
				# We'll store our updated selector list array
				$updated = array();
				
				# Put the selectors into an array
				$exploded = explode(",", $rules[1][$key]);
				
				# Loop through each selector in the list
				foreach($exploded as $single_selector)
				{
					$updated[] = str_replace(unquote($find), $selector, $single_selector);
				}
				
				# Merge the old selector list and the new one
				$updated = array_merge($updated, $exploded);
				
				# Get rid of unnecessary extra selectors
				$updated = array_unique($updated);	
				
				# Implode it into a regular css selector string
				$updated = implode(",", $updated);		
				
				# Replace the original selector string with our new one to add the properties
				$updated = str_replace($rules[1][$key], $updated, $rules[0][$key]);
				
				# Replace the entire, original rule, with our updated
				# rule which includes our selector added to the list
				CSS::replace($rules[0][$key], $updated);
			
			} # End foreach rules
		} # End foreach found
	} # End process
}