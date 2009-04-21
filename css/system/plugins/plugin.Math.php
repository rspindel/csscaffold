<?php defined('BASEPATH') OR die('No direct access allowed.');

/**
 * Math class
 *
 * @package csscaffold
 **/
class Math extends Plugins
{

	function post_process($css)
	{	
		if(preg_match_all('/math\([\"|\'](.*?)[\"|\']\)/', $css, $matches))
		{	
			foreach($matches[1] as $key => $match)
			{	
				$match = str_replace('px', '', $match);
				$match = preg_replace('/[^*|\/|\(|\)|0-9|+|-]*/sx','',$match); // Only include the simple math operators
				eval("\$result = ".$match.";");
				$css = str_replace($matches[0][$key], $result, $css);
			}
		}

		return $css;
	}
	
}

?>