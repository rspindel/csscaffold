<?php

# Settings for the Icy compressor
$config = array(

	# Convert rgb() to hex values
	'rgbtohex' => true,
	
	# Convert longer color names to shorter hex values
	'colors2hex' => true,
	
	# Convert long hex values to shorter color names
	'hex2colors' => true,
	
	# Remove any extra measurements that aren't needed
	'remove_zeros' => true,
	
	# Convert font-weights to numbers
	'text_weights_to_numbers' => true,
	
	# Combine identical selectors
	'combine_identical_selectors' => true,
	
	# Remove properties which have been declared twice
	'remove_overwritten_properties' => true,
	
	# Combine properties
	'combine_props_list' => true,
	
	# Combine identical rules
	'combine_identical_rules' => true,
	
	# Shorten hex values 
	'short_hex' => true,
	
	# Shorten Margin and Padding
	'short_margins_and_paddings' => true
	
);