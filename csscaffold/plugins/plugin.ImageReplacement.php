<?php defined('BASEPATH') OR die('No direct access allowed.');

/**
 * ImageReplacement class
 *
 * @package csscaffold
 **/
class ImageReplacement extends Plugins
{
	function process($css)
	{			
		$directory = ASSETPATH."/titles";
		
		// Find all the image-replace:; properties and replace them
		if(preg_match_all("/image\-replace\s*\:\s*url\([\'\"](.*?)[\'\"]\)\s*\;/sx",$css, $match))
		{
			foreach ($match[1] as $key => $value) 
			{
				$file = pathinfo($value);
				
				// Check it from the root or if its relative to the css directory
				if ( file_exists(DOCROOT . $value) ) 
				{
					$path = DOCROOT . $value;
				}
				elseif ( file_exists(DOCROOT . URLPATH . $value) ) 
				{
					$path = DOCROOT . URLPATH . $value;
				}
				else
				{
					stop(unslash(DOCROOT) . URLPATH . $value);
					continue;
				}
				 
				$ext = $file['extension'];

				// Make sure its an image file, if not, skip it
				if( $ext == 'png' || $ext == 'jpg' || $ext == 'gif' )
				{ 																
					// Get the size of the image file
					$size = GetImageSize($path);
					$width = $size[0];
					$height = $size[1];
					
					// Make sure theres a value so it doesn't break the css
					if(!$width && !$height)
					{
						$width = $height = 0;
					}
					
					// Build the selector
					$properties = "
						background:url(". $value .") no-repeat 0 0;
						height:".$height."px;
						width:".$width."px;
						display:block;
						text-indent:-9999px;
						overflow:hidden;
					";		

				}	
				
				$css = str_replace($match[0][$key], $properties, $css);
			}
		}	
					
		return $css;
	}
}