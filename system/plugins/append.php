<?php
/******************************************************************************
 Prevent direct access
 ******************************************************************************/
if (!defined('CSS_CACHEER')) { header('Location:/'); }

$plugin_class = 'Append';

class Append extends CacheerPlugin
{
	
	function post_process($css)
	{
		global $css_plugin_dir;
		
		
		if (is_dir($css_plugin_dir))
		{
			if ($dir_handle = opendir($css_plugin_dir)) 
			{
				while (($file = readdir($dir_handle)) !== false) 
				{
					if (substr($file, 0, 1) == '.' || substr($file, 0, 1) == '-')
					{ 
						continue; 
					}

					$append .= file_get_contents($css_plugin_dir.$file);

				}
				closedir($dir_handle);
			}
		}
		
		$css = $css . $append;
		
		return $css;
	}
}

?>