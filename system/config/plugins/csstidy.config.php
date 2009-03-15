<?php if (!defined('CSS_CACHEER')) { header('Location:/'); }

$options[$plugin_class] = array(
	'preserve_css' 				=> false,
	'sort_selectors' 			=> false,
	'sort_properties' 			=> true,
	'merge_selectors' 			=> 2,
	'optimise_shorthands'		=> 1,
	'compress_colors' 			=> true,
	'compress_font-weight' 		=> false,
	'lowercase_s' 				=> true,
	'case_properties' 			=> 1,
	'remove_bslash' 			=> false,
	'remove_last_;' 			=> true,
	'discard_invalid_properties'=> false,
);