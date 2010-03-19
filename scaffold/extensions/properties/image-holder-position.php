<?php

/**
 * Adds the image-replace property that allows you to automatically
 * replace text with an image on your server.
 *
 * Because functions can't have - in their name, it is replaced
 * with an underscore. The property name is still image-holder-position
 *
 * @author Kirk Bentley
 * @param $url
 * @param $x - x position
 * @param $y - y position
 * @return string
 */
function Scaffold_image_holder_position($params)
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
	
	foreach(array('url','x','y') as $key => $name)
	{
		$$name = trim(str_replace('#COMMA#',',', array_shift($params) ));
	}
	
	$url = preg_replace('/\s+/','',$url);
	$url = preg_replace('/url\\([\'\"]?|[\'\"]?\)$/', '', $url);

	$path = Scaffold::find_file($url);
	
	if($path === false)
		return false;
																		
	// Get the size of the image file
	$size = GetImageSize($path);
	$width = $size[0];
	$height = $size[1];
	
	// Make sure theres a value so it doesn't break the css
	if(!$width && !$height)
	{
		$width = $height = 0;
	}
	
	$left = $x;
	
	if($x == '50%'){
		$left = -$width*0.5;
	}
	
	$top = $y;
	
	if($y == '50%'){
		$top = -$height*0.5;
	}
	
	// Build the selector
	$properties = "top:{$top}px;
		left:{$left}px;";

	return $properties;
}