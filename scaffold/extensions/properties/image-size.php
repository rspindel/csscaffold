<?php

/**
 * Adds the image-size property that allows you to automatically
 * obtain the image width and height properties.
 *
 * Because functions can't have - in their name, it is replaced
 * with an underscore. The property name is still image-size
 *
 * @author Kirk Bentley
 * @param $url
 * @param $w - currently accepts 50% which will return half
 *			   of the image width. Useful for hover states.
 * @param $h - currently accepts 50% which will return half
 *			   of the image height. Useful for hover states.
 * @return string
 */
function Scaffold_image_size($params)
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
	
	foreach(array('url','w','h') as $key => $name)
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
	
	if($w == '50%'){
		$width = $width*0.5;
	}
	
	if($h == '50%'){
		$height = $height*0.5;
	}
	
	// Build the selector
	$properties = "height:{$height}px;
		width:{$width}px;";

	return $properties;
}