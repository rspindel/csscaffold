<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * ImageReplacement class
 *
 * @author Anthony Short
 * @dependencies None
 **/
class ImageReplace extends Plugins
{

	/**
	 * The second last process, should only be getting everything
	 * syntaxically correct, rather than doing any heavy processing
	 *
	 * @author Anthony Short
	 * @return $css string
	 */
	public static function post_process()
	{
		$found = CSS::find_properties_with_value('image-replace', 'url\([\'\"]?([^)]+)[\'\"]?\)');
		
		if($found)
		{
			foreach ($found[4] as $key => $value) 
			{
				$path = unquote($value);
								
				$absolute_img = CSS::resolve_path($path);
													
				# Check if it exists
				if(!file_exists($absolute_img))
					throw new Scaffold_User_Exception("Image Replace Plugin", "File does not exist - $path");
				
				# Make sure it's an image
				if(!is_image($absolute_img)) 
					throw new Scaffold_User_Exception("Image Replace Plugin", "File is not an image - $path");
																				
				// Get the size of the image file
				$size = GetImageSize($absolute_img);
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
					height:{$height}px;
					width:{$width}px;
					display:block;
					text-indent:-9999px;
					overflow:hidden;
				";

				CSS::replace($found[1][$key], $properties);
			}
			
			# Remove any left overs
			CSS::replace($found[1], '');
		}
	}

}