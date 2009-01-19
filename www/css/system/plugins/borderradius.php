<?php
/******************************************************************************
 Prevent direct access
 ******************************************************************************/
if (!defined('CSS_CACHEER')) { header('Location:/'); }

$plugin_class = 'BorderRadiusPlugin';

class BorderRadiusPlugin extends CacheerPlugin
{
	function post_process($css)
	{
		if(preg_match_all('/border\-radius\:(.*?)\;/', $css, $matches))
		{
			foreach($matches[0] as $key => $match)
			{				
				$s = "-moz-border-radius:".$matches[1][$key].";";
				$s .= "-webkit-border-radius:".$matches[1][$key].";";
				
				// Remove the border-radius:xpx;
				$css = str_replace($match,$s,$css);
			}
		}
		
		if(preg_match_all('/border\-radius\-topleft\:(.*?)\;/', $css, $matches))
		{
			foreach($matches[0] as $key => $match)
			{				
				$s = "-moz-border-radius-topleft:".$matches[1][$key].";";
				$s .= "-webkit-border-top-left-radius:".$matches[1][$key].";";
				
				// Remove the border-radius:xpx;
				$css = str_replace($match,$s,$css);
			}
		}
		
		if(preg_match_all('/border\-radius\-topright\:(.*?)\;/', $css, $matches))
		{
			foreach($matches[0] as $key => $match)
			{				
				$s = "-moz-border-radius-topright:".$matches[1][$key].";";
				$s .= "-webkit-border-top-right-radius:".$matches[1][$key].";";
				
				// Remove the border-radius:xpx;
				$css = str_replace($match,$s,$css);
			}
		}
		
		if(preg_match_all('/border\-radius\-bottomleft\:(.*?)\;/', $css, $matches))
		{
			foreach($matches[0] as $key => $match)
			{				
				$s = "-moz-border-radius-bottomleft:".$matches[1][$key].";";
				$s .= "-webkit-border-bottom-left-radius:".$matches[1][$key].";";
				
				// Remove the border-radius:xpx;
				$css = str_replace($match,$s,$css);
			}
		}
		
		if(preg_match_all('/border\-radius\-bottomright\:(.*?)\;/', $css, $matches))
		{
			foreach($matches[0] as $key => $match)
			{				
				$s = "-moz-border-radius-bottomright:".$matches[1][$key].";";
				$s .= "-webkit-border-bottom-right-radius:".$matches[1][$key].";";
				
				// Remove the border-radius:xpx;
				$css = str_replace($match,$s,$css);
			}
		}

		return $css;
	}
}

?>