<?php

/**
 * Adds the image-width property that allows you to automatically
 * obtain the height of a image
 *
 * Because functions can't have - in their name, it is replaced
 * with an underscore. The property name is still image-height
 *
 * @author Kirk Bentley
 * @param $url
 * @return string
 */
function Scaffold_image_height($url)
{
	$url = preg_replace('/\s+/','',$url);
	$url = preg_replace('/url\\([\'\"]?|[\'\"]?\)$/', '', $url);

	$path = Scaffold::find_file($url);
	
	if($path === false)
		return false;
																		
	// Get the width of the image file
	$size = GetImageSize($path);
	$height = $size[1];
	
	// Make sure theres a value so it doesn't break the css
	if(!$height)
	{
		$height = 0;
	}
	
	return $height;
}