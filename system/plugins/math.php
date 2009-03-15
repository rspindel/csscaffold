<?php if (!defined('CSS_CACHEER')) { header('Location:/'); }

/**
 * The class name
 * @var string
 */
$plugin_class = 'Math';

/**
 * Math class
 *
 * @package csscaffold
 **/
class Math extends CacheerPlugin
{	
	function post_process($css)
	{
		global $options;
		
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
		
		if(preg_match_all('/round\((\d+)\)/', $css, $matches))
		{
			foreach($matches[1] as $key => $match)
			{
				$num = $this->round_nearest($match,$options['Grid']['baseline']);
				$css = str_replace($matches[0][$key],$num."px",$css);
			}
		}
		
		return $css;
	}
	
	// Round a number to the nearest multiple
	function round_nearest($number,$multiple) 
	{ 
		return round($number/$multiple)*$multiple;
	}
	
	
}

?>