<?php defined('BASEPATH') OR die('No direct access allowed.');

/**
 * Base64Plugin class
 *
 * @author Anthony Short
 * @dependencies User_agent
 **/
class Base64 extends Plugins
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
	function post_process($css)
	{	
		
		# If we can do Base64, replace the embed() functions
		if (isset($this->flags['Base64']))
		{
			$css = $this->replace_embeds($css);
		}
		
		# Otherwise the browser can't do base64 images, change them to plain urls
		else
		{
			$css = str_replace("embed(", "url(", $css);
		}
		
		return $css;
	}
	
	/**
	 * Replaces all of the embed() properties with image data
	 *
	 * @author Anthony Short
	 * @return string
	 */
	function replace_embeds($css)
	{
		$images = array();
		
		$embeds = match('/embed\(([^\)]+)\)/', $css, 1);

		foreach($embeds as $relative_img)
		{
			if(!is_image($relative_img)) continue;
			
			$absolute_img = $this->find_absolute_path($relative_img);
			
			if (file_exists($absolute_img))
			{
				$img_data = 'data:image/'.file_type($relative_img).';base64,'.base64_encode(file_get_contents($absolute_img));
				$css = str_replace("embed({$relative_img})", "url({$img_data})", $css);
			}
		}
		
		return $css;
	}
	
	/**
	 * Finds the absolute path of an image in a url()
	 *
	 * @author Anthony Short
	 * @param $relative_img
	 * @return string
	 */
	function find_absolute_path($relative_img)
	{
		$up = substr_count($relative_img, '../');
		$relative_img_loc = strip_quotes($relative_img);
		$absolute_img = CSSPATH.preg_replace('#([^/]+/){'.$up.'}(\.\./){'.$up.'}#', '', $requested_dir.'/'.$relative_img_loc);
		
		return $absolute_img;
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