<?php defined('BASEPATH') OR die('No direct access allowed.');

/**
 * Base64Plugin class
 *
 * @package Cacheer
 **/
class Base64 extends Plugins
{
	
	function __construct()
	{
		// Safari (WebKit), Firefox & Opera are known to support data: urls so embed base64-encoded images
		if
		(
			(Core::user_agent('browser') == 'Safari' && Core::user_agent('version') >= 125) || // Safari and ilk
			(Core::user_agent('browser') == 'Firefox') || // Firefox et al
			(Core::user_agent('browser') == 'Opera' && Core::user_agent('version') >= 7.2) // quell vociferous Opera evangelists
		)
		{
			$this->flags['Base64'] = true;
		}
	}
	
	function post_process($css)
	{		
		if (isset($this->flags['Base64']))
		{
			$images = array();
			if (preg_match_all('#embed\(([^\)]+)\)#i', $css, $matches))
			{
				foreach($matches[1] as $relative_img)
				{
					if (!preg_match('#\.(gif|jpg|png)#', $relative_img, $ext))
					{
						continue;
					}

					$images[$relative_img] = $ext[1];
				}

				foreach($images as $relative_img => $img_ext)
				{
					$up = substr_count($relative_img, '../');
					$relative_img_loc = preg_replace('/[\'|\"]/', "",$relative_img);
					$absolute_img = CSSPATH.preg_replace('#([^/]+/){'.$up.'}(\.\./){'.$up.'}#', '', $requested_dir.'/'.$relative_img_loc);
					if (file_exists($absolute_img))
					{
						$img_raw = file_get_contents($absolute_img);
						$img_data = 'data:image/'.$img_ext.';base64,'.base64_encode($img_raw);
						$css = str_replace("embed({$relative_img})", "url({$img_data})", $css);
					}
				}
			}
		}
		
		// If the browser can't do base64 images, change them to plain urls
		else
		{
			$css = str_replace("embed(", "url(", $css);
		}

		return $css;
	}
} // END Base64Plugin