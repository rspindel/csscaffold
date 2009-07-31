<?php defined('BASEPATH') OR die('No direct access allowed.');

/**
 * ImageReplacement class
 *
 * @author Anthony Short
 * @dependencies None
 **/
class Image_replace extends Plugins
{

	/**
	 * The second last process, should only be getting everything
	 * syntaxically correct, rather than doing any heavy processing
	 *
	 * @author Anthony Short
	 * @return $css string
	 */
	function post_process()
	{
		# Replace the image-replace properties
		$this->image_replace();
	}

	/**
	 * Image-replaced
	 *
	 * @author Anthony Short
	 * @return null
	 */
	private function image_replace()
	{	
		$found = CSS::find_properties_with_value('image-replace', 'url\([\'\"]?([^)]+)[\'\"]?\)');
		
		if($found)
		{
			foreach ($found[4] as $key => $value) 
			{
				$path = unquote($value);
								
				$absolute_img = find_absolute_path($path);
													
				# Check if it exists
				if (!file_exists($absolute_img) || !is_image($absolute_img)) 
				{
					continue;
				}
																				
				// Get the size of the image file
				$size = GetImageSize($absolute_img);
				$width = $size[0];
				$height = $size[1];
				
				// Make sure theres a value so it doesn't break the css
				if(!$width && !$height)
				{
					$width = $height = 0;
				}
				
				$url = str_replace(DOCROOT, '', $absolute_img);
				
				// Build the selector
				$properties = "
					background:url($url) no-repeat 0 0;
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