<?php
/**
 * Adds the image-replace-matrix property that allows you to automatically
 * replace text with an image matrix on your server.
 *
 * Because functions can't have - in their name, it is replaced
 * with an underscore. The property name is still image-replace-matrix
 *
 * @author Anthony Short
 * @param $url
 * @return string
 */
//function Scaffold_image_replace($url, $width, $height, $x = 0, $y = 0)
function Scaffold_image_replace_matrix($params)
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

	foreach(array('url','width','height','x','y') as $key => $name)
	{
		$$name = trim(str_replace('#COMMA#',',', array_shift($params) ));
	}

	$url = preg_replace('/\s+/','',$url);
	$url = preg_replace('/url\\([\'\"]?|[\'\"]?\)$/', '', $url);

	if(!$x) $x = 0;

	if(!$y) $y = 0;

	$path = Scaffold::find_file($url);

	if($path === false)	return false;
	
	// Make sure theres a value so it doesn't break the css
	if(!$width && !$height)
	{
		$width = $height = 0;
	}
	
	// Build the selector
	$properties = "background:url(".Scaffold::url_path($path).") no-repeat {$x}px {$y}px;
		height:{$height}px;
		width:{$width}px;
		display:block;
		text-indent:-9999px;
		overflow:hidden;";

	return $properties;
}