<?php
/******************************************************************************
 Prevent direct access
 ******************************************************************************/
if (!defined('CSS_CACHEER')) { header('Location:/'); }


$plugin_class = 'CSS3Helper';

class CSS3Helper extends CacheerPlugin
{

	function post_process($css)
	{
		
		$css = $this->borderRadius($css);
		$css = $this->fontFace($css);
		
		return $css;
	}
		
	function borderRadius($css)
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
	
	
	function fontFace($css)
	{
		global $font_dir;
	
	
		if (is_dir($font_dir))
		{
			if ($dir_handle = opendir($font_dir)) 
			{
				while (($font_file = readdir($dir_handle)) !== false) 
				{
					
					
					if(strlen($font_file) < 3)
					{
						continue;
					}
					
					
					$ext = substr($font_file, -3, 3);

					
					// Make sure its an image file, if not, skip it
					if( $ext == 'otf' || $ext == 'ttf' || $ext == 'eot' )
					{ 
						// Ditch the extension
						$fn = preg_replace('/\.otf|\.ttf|\.eot/', "", $font_file);
											
						// Build the selector
						$properties = "name:'$fn';src:url('$font_dir$font_file');";
												
						
						// Add them as classes
						$atfontface = "@font-face {" . $properties . "}";
						$css .= $atfontface;
						
															
					}			
				}			
				closedir($dir_handle);
			}
		}
		
		return $css;
	}
	
}

?>