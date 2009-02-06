<?php
/******************************************************************************
 Prevent direct access
 ******************************************************************************/
if (!defined('CSS_CACHEER')) { header('Location:/'); }

include '-grid.php';

$plugin_class = 'Grid';

class Grid extends CacheerPlugin
{
	
	function pre_process($css)
	{	
		global $grid;
		
		// Create a new GridCSS object and put the css into it
		$grid = new GridCSS($css);
		
		// If there are settings, keep going
		if($grid->isSettings() === TRUE)
		{
		
			// Generate the grid.css
			$grid -> generateGrid($css);
			
			// Generate the grid.png
			$grid -> generateGridImage($css);
			
			// Replace the grid() variables
			$css = $grid -> replaceGridVariables($css);
		
		}

		return $css;
	}
	
	function process($css)
	{
		global $grid;
		
		// If there are settings, keep going
		if($grid->isSettings() === TRUE)
		{
			// Create the layouts xml for use with the tests
			$grid -> generateLayoutXML($css);
		
			// Replace the columns:; properties
			$css = $grid -> replaceColumns($css);
		}
		
		return $css;
	}
	
	function post_process($css)
	{
		global $grid;
		
		// If there are settings, keep going
		if($grid->isSettings() === TRUE)
		{
			// Remove the settings
			$css = $grid -> removeSettings($css);
		}

		return $css;
	}
}

?>