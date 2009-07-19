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
		if( $this->can_base64( User_agent::$browser, User_agent::$version ) ) 
		{
			$this->flags['Base64'] = true;	
		}
	}
	
	/**
	 * The final process before it is cached. This is usually just
	 * formatting of css or anything else just before it's cached
	 *
	 * @author Anthony Short
	 * @param $css
	*/
	function post_process()
	{
		# Replace the image-replace properties
		$this->image_replace();
			
		# If we can do Base64, replace the embed() functions
		if (isset($this->flags['Base64']))
		{
			$this->embed_images();
		}
		
		# Otherwise the browser can't do base64 images, change them to plain urls
		else
		{
			CSS::replace("embed(", "url(");
		}
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
				$path =& $value;
								
				if(substr($path, 0, 1) != "/")
				{
					$absolute_img = $this->find_absolute_path($path);
				}
				else
				{
					$absolute_img = join_path(DOCROOT,$path);
				}
													
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
		$images = array();
		$embeds = CSS::find_functions('embed');

		foreach($embeds as $key => $value)
		{
			$relative_img =& $embeds[1][$key];
			$relative_img = unquote($relative_img);
						
			if(!is_image($relative_img)) continue;
			
			if(substr($relative_img, 0, 1) != "/")
			{
				$absolute_img = $this->find_absolute_path($relative_img);
			}
			else
			{
				$absolute_img = join_path(DOCROOT,$relative_img);
			}

			if (file_exists($absolute_img))
			{
				$img_data = 'url(data:image/'.extension($relative_img).';base64,'.base64_encode(file_get_contents($absolute_img)) . ')';		
				CSS::replace($embeds[0][$key], $img_data);
			}
		}
	}
	
	/**
	 * Finds the absolute path of an image in a url()
	 *
	 * @author Anthony Short
	 * @param $relative_img
	 * @return string
	 */
	function find_absolute_path($relative)
	{
		$up = substr_count($relative, '../');
		$image_path = preg_replace('#([^/]+/){'.$up.'}(\.\./){'.$up.'}#', '', join_path(Config::get('requested_dir'), unquote($relative)) );

		return  join_path(CSSPATH, $image_path);
	}
	
	/**
	 * Determine if the browser can do Base64
	 *
	 * @author Anthony Short
	 * @return boolean
	 */
	function can_base64($browser, $version)
	{		
		// Safari (WebKit), Firefox & Opera are known to support data: urls so embed base64-encoded images
		if
		(
			($browser == 'Safari' && $version >= 125) ||
			($browser == 'Firefox') ||
			($browser == 'Opera' && $version >= 7.2)
		)
		{
			return true;
		}
	}
}