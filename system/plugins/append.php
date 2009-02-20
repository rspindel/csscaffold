<?php
/******************************************************************************
 Prevent direct access
 ******************************************************************************/
if (!defined('CSS_CACHEER')) { header('Location:/'); }

$plugin_class = 'Append';

class Append extends CacheerPlugin
{
	
	function pre_process($css)
	{
		global $path;
		
		$append = "";
		
		if (is_dir($path['plugins']))
		{
			if ($dir_handle = opendir($path['path'])) 
			{
				while (($file = readdir($dir_handle)) !== false) 
				{
					if (substr($file, 0, 1) == '.' || substr($file, 0, 1) == '-')
					{ 
						continue; 
					}

					$append .= file_get_contents($path['path']."/".$file);
					
				}
				closedir($dir_handle);
			}
		}
		
		$css = $css . $append;
						
		return $css;
	}
}

?>