<?php
/******************************************************************************
 Prevent direct access
 ******************************************************************************/
if (!defined('CSS_CACHEER')) { header('Location:/'); }

$plugin_class = 'DesignOverlay';

class DesignOverlay extends CacheerPlugin
{	
	function post_process($css)
	{
		global $settings;
		
		$designs = array();
		$design_dir = "images/designs";

		if (is_dir($design_dir))
		{
			if ($dir_handle = opendir($design_dir)) 
			{
				while (($design_file = readdir($dir_handle)) !== false) 
				{
					if (substr($design_file, -4) == '.jpg' || substr($design_file, -4) == '.png')
					{ 
						array_push($designs, $design_dir.'/'.$design_file);	 
					}						
				}			
				closedir($dir_handle);
			}
		}
	
		
		for($i=1;$i < count($designs);$i++)
		{
			$design_class = ".showdesign-".$i."{ background:url(".$designs[$i - 1]."); }";
			$css .= $design_class;
		}
		
		return $css;
	}
}

?>