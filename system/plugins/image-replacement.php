<?php
/******************************************************************************
 Prevent direct access
 ******************************************************************************/
if (!defined('CSS_CACHEER')) { header('Location:/'); }

$plugin_class = 'ImageReplacement';

class ImageReplacement extends CacheerPlugin
{
	function process($css)
	{	
		global $path;
			
		$ua = (isset($_SERVER['HTTP_USER_AGENT'])) ? parse_user_agent($_SERVER['HTTP_USER_AGENT']) : "";
		

		if (is_dir($path['image_titles']))
		{
			if ($dir_handle = opendir($path['image_titles'])) 
			{
				while (($ir_file = readdir($dir_handle)) !== false) 
				{
					
					
					if(strlen($ir_file) < 3)
					{
						continue;
					}
					
					
					$ext = substr($ir_file, -3, 3);

					
					// Make sure its an image file, if not, skip it
					if( $ext == 'png' || $ext == 'jpg' || $ext == 'gif' )
					{ 
						// Ditch the extension
						$fn = preg_replace('/\.png|\.jpg|\.gif/', "", $ir_file);
											
													
						// Get the size of the image file
						$size = GetImageSize($path['image_titles'].$ir_file);
						$width = $size[0];
						$height = $size[1];
						
						
						// Make sure theres a value so it doesn't break the css
						if(!$width && !$height)
						{
							$width = $height = 0;
						}
						
						
						// Build the selector
						$properties = "
							background:url(".$path['image_titles']."/$ir_file) no-repeat 0 0;
							height:".$height."px;
							width:".$width."px;
							display:block;
							text-indent:-9999px;
							overflow:hidden;
							";
							
							
						// If the browser is Safari 3, or FF 3.1, don't do the image replace
						// instead, remove all the image-replace properties and return the css
						// and we'll just the the embedded fonts instead
						if( ($ua['browser'] == 'applewebkit' && $ua['version'] >= 525) || ($ua['browser'] == 'firefox' && $ua['version'] >= 3.1) )
						{
							$css = preg_replace('/image\-replace\s*\:\s*.*?\;/', '', $css);
						}
						
						else
						{
							// Find all the image-replace:; properties and replace them
							preg_match_all("/image\-replace\:\s*[\'\"]*($fn)[\'\"]*\s*\;/",$css, $match);
							
							foreach ($match[0] as $key => $value) 
							{
								$css = str_replace($match[0][$key], $properties, $css);
							}	
						}
						
						
						// Create the classes for each image replace and put them
						// in the css as well, so we can do it both ways.
						$selector = ".ir-" . $fn . "{" . $properties . "}";
						$css .= $selector;
						
											
					}			
				}			
				closedir($dir_handle);
			}
		}
		


		
		return $css;
	}

}

?>