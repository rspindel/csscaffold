<?php if (!defined('CSS_CACHEER')) { header('Location:/'); }

/**
 * The class name
 * @var string
 */
$plugin_class = 'Grid';

/**
 * Include the settings
 */
include $config['system_dir'] . '/config/plugins/grid-layout.config.php';

/**
 * Include the CSSTidy class
 */
include $config['system_dir'] . '/classes/Grid.php';

/**
 * Grid class
 *
 * @package csscaffold
 **/
class Grid extends CacheerPlugin
{
		
	function process($css)
	{
		global $grid;
		
		// Create a new GridCSS object and put the css into it
		$grid = new GridCSS($css);
		
		// If there are settings, keep going
		if($grid->getSettings() != FALSE)
		{
			// Generate the grid.png
			$grid -> generateGridImage($css);
			
			// Replace the grid() variables
			$css = $grid -> replaceGridVariables($css);
			
			// Create the layouts xml for use with the tests
			$grid -> generateLayoutXML($css);
		
			// Replace the columns:; properties
			$css = $grid -> replaceColumns($css);
		}
		
		return $css;
	}
	
	function post_process($css)
	{
		global $grid,$options;
			
		// If there are settings, keep going
		if($grid->getSettings() != FALSE)
		{
			if($options['Grid']['create_grid_css'] == TRUE)
			{
				// Generate the grid.css
				$css = $grid -> generateGridClasses($css);
			}
		
			// Remove the settings
			$css = $grid -> removeSettings($css);
		}

		return $css;
	}
}



?>