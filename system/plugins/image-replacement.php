<?php if (!defined('CSS_CACHEER')) { header('Location:/'); }

/**
 * The class name
 * @var string
 */
$plugin_class = 'ImageReplacement';

/**
 * ImageReplacement class
 *
 * @package csscaffold
 **/
class ImageReplacement extends CacheerPlugin
{
	function process($css)
	{	
		global $config;
		
		$directory = $config['assets_dir']."/titles";
		$images = read_dir($directory);
		
		// If we found some images in the titles directory
		if($images != "")
		{
			foreach($images as $name => $path)
			{
				$ext = substr($name, -3, 3);

				// Make sure its an image file, if not, skip it
				if( $ext == 'png' || $ext == 'jpg' || $ext == 'gif' )
				{ 
					// Ditch the extension
					$name = preg_replace('/\.png|\.jpg|\.gif/', "", $name);
																
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
						background:url($path) no-repeat 0 0;
						height:".$height."px;
						width:".$width."px;
						display:block;
						text-indent:-9999px;
						overflow:hidden;
						";
					
					// Find all the image-replace:; properties and replace them
					if(preg_match_all("/image\-replace\s*\:\s*[\'\"]($name)[\'\"]\s*\;/sx",$css, $match))
					{
						foreach ($match[0] as $key => $value) 
						{
							$css = str_replace($match[0][$key], $properties, $css);
						}
					}				
				}	
			}	
		}				
		return $css;
	}
}