<?php

/**
 * Adds multiple box shadow properties allowing multiple box shadows
 *
 * @author Kirk Bentley
 * @return string
 *
 * @example 'box-shadow-multi: 0 2px 2px rgba(0, 0, 0, 0.3), 0 1px 0 #9ecff9 inset;'
 */
function Scaffold_box_shadow_multi($params)
{
	if(preg_match_all('/\([^)]*?,[^)]*?\)/',$params, $matches))
	{
		foreach($matches as $key => $original)
		{
			$new = str_replace(',','#COMMA#',$original);
			$params = str_replace($original,$new,$params);
		}
	}

	$params = explode(',',$params);

	$applyTo = array(
		'-moz-box-shadow',
		'-webkit-box-shadow',
		'box-shadow',
	);

	$gecko = '';
	$webkit = '';
	$bs = '';

	$shadow_output = '';

	$cnt = 1;
	foreach($params as $shadow){
		
		$shadow = str_replace('#COMMA#', ',',trim($shadow));
		
		if(preg_match_all('/\([^)]*?,[^)]*?\)/',$shadow, $matches))
		{
			foreach($matches as $key => $original)
			{
				$new = str_replace(' ','#SPACE#',$original);
				$shadow = str_replace($original,$new,$shadow);
			}
		}
		
		$shadow_params = explode(' ', $shadow);
		
		$joiner = ($cnt === count($params) ? ';':',');
		
		foreach(array('x','y','blur','color','inset') as $key => $name)
		{
			$$name = trim(str_replace('#SPACE#',' ', array_shift($shadow_params) ));
		}
		
		$shadow_output .= ($inset === 'inset' ? 'inset':'').' '.$x.' '.$y.' '.$blur.' '.$color.$joiner;
		
		$cnt++;
	}

	foreach($applyTo as $k){
		switch($k)
		{
			case '-moz-box-shadow':
				$gecko .= $k.': '.$shadow_output;
				break;
			case '-webkit-box-shadow':
				$webkit .= $k.': '.$shadow_output;
				break;
			case 'box-shadow':
				$bs .= $k.': '.$shadow_output;
				break;
		}
	}

	// Build the selector
	$properties = $gecko."\n\t".$webkit."\n\t".$bs;

	return $properties;
}