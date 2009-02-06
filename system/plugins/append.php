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
		$append_dir = 'assets/plugins/';
		
		if (is_dir($append_dir))
		{
			if ($dir_handle = opendir($append_dir)) 
			{
				while (($file = readdir($dir_handle)) !== false) 
				{
					if (substr($file, 0, 1) == '.' || substr($file, 0, 1) == '-')
					{ 
						continue; 
					}
					
					$append .= file_get_contents($append_dir.$file);
				
				}
				closedir($dir_handle);
			}
		}
		
		$css = $css . $append;
		
		return $css;
	}
}

?>