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
		global $options;
			
		$ua = (isset($_SERVER['HTTP_USER_AGENT'])) ? parse_user_agent($_SERVER['HTTP_USER_AGENT']) : "";
		
		if (is_dir($options['assets']."/titles"))
		{
			if ($dir_handle = opendir($options['assets']."/titles")) 
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
							
						// Find all the image-replace:; properties and replace them
						preg_match_all("/image\-replace\:\s*[\'\"]*($fn)[\'\"]*\s*\;/",$css, $match);
						
						foreach ($match[0] as $key => $value) 
						{
							$css = str_replace($match[0][$key], $properties, $css);
						}					
											
					}			
				}			
				closedir($dir_handle);
			}
		}
		return $css;
	}

}

?>