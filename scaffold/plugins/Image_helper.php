<?php defined('BASEPATH') OR die('No direct access allowed.');

/**
 * ImageReplacement class
 *
 * @author Anthony Short
 * @dependencies None
 **/
class Image_helper extends Plugins
{
	/**
	 * The main processing function. This is done AFTER
	 * imports and the setting of variables etc, and before
	 * the css is formatted. 
	 *
	 * @author Anthony Short
	 * @param $css
	 */
	function process($css)
	{
		#$css = preg_replace('/assets_(url|embed)\((\'|\")([^)]+)(\'|\")\)/', "$1(/".ASSETURL."/$3)", $css);
					
		if(preg_match_all('/(image\-replace)\s*\:\s*(url\((\'|\")([^)]+)(\'|\")\))\s*\;/', $css, $found))	
		{
			foreach ($found[4] as $key => $value) 
			{
				$path = join_path(DOCROOT,$value);
										
				# Check if it exists
				if (!file_exists($path)) 
				{
					continue;
				}
					
				// Make sure its an image file, if not, skip it
				if( is_image($value) )
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
						background:url($value) no-repeat 0 0;
						height:{$height}px;
						width:{$width}px;
						display:block;
						text-indent:-9999px;
						overflow:hidden;
					";		
	
				}	
				
				$css = str_replace($found[0][$key], $properties, $css);
			}
			
			# Remove any left overs
			$css = str_replace($found[0], '', $css);
		
		}
					
		return $css;
	}

}