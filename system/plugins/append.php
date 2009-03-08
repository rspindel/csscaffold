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
		
		if (is_dir($path['append_dir']))
		{
			if ($dir_handle = opendir($path['append_dir'])) 
			{
				while (($file = readdir($dir_handle)) !== false) 
				{
					if (substr($file, 0, 1) == '.' || substr($file, 0, 1) == '-' || substr($file, -3) != 'css')
					{ 
						continue; 
					}

					$append .= file_get_contents($path['append_dir']."/".$file);
					
					echo $append;
					exit;
				}
				closedir($dir_handle);
			}
		}
		
		$css = $css . $append;
						
		return $css;
	}
}

?>