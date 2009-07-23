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
	 * The construct is important for plugins. It is where flags MUST 
	 * be set. For each flag that exists, a seperate file will be cached
	 * and only be sent to users that meet the conditions of those flags
	 *
	 * @author Anthony Short
	 */
	function __construct()
	{
		if(User_agent::can_base64()) 
		{
			Cache::flag('Base64');	
		}
	}
	
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
	 * The final process before it is cached. This is usually just
	 * formatting of css or anything else just before it's cached
	 *
	 * @author Anthony Short
	 * @param $css
	*/
	function formatting_process()
	{	
		# If we can do Base64, replace the embed() functions
		$this->embed_images();
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
	
	/**
	 * Replaces all of the embed() properties with image data
	 *
	 * @author Anthony Short
	 * @return string
	 */
	function embed_images()
	{
		if (User_agent::can_base64())
		{
			$images = array();
			$embeds = CSS::find_functions('embed');

			foreach($embeds as $key => $value)
			{
				$relative_img =& $embeds[1][$key];
				$relative_img = unquote($relative_img);
						
				if(!is_image($relative_img)) continue;
			
				$absolute_img = find_absolute_path($relative_img);

				if (file_exists($absolute_img))
				{
					$img_data = 'url(data:image/'.extension($relative_img).';base64,'.base64_encode(file_get_contents($absolute_img)) . ')';		
					CSS::replace($embeds[0][$key], $img_data);
				}
			}
		}
		else
		{
			CSS::replace("/embed\s*\(/", "url(", true);
		}
	}
}