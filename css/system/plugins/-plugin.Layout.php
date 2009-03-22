<?php if (!defined('CSS_CACHEER')) { header('Location:/'); }

/**
 * The class name
 * @var string
 */
$plugin_class = 'Grid';

/**
 * The plugin settings
 * @var string
 */
$settings = array(
	'create_grid_css' 	=> true,
	'push' 				=> true,
	'pull' 				=> true,
	'append' 			=> true,
	'prepend' 			=> true,
	'columns-x' 		=> true,
	'baseline-x' 		=> true,
	'baseline-push-x' 	=> true,
	'baseline-pull-x' 	=> true
);

/**
 * Grid class
 *
 * @package csscaffold
 **/
class Layout extends Plugins
{
		
	function process($css)
	{
		
		// Create a new GridCSS object and put the css into it
		$this->grid = new GridCSS($css);
		
		stop($this);
		
		// If there are settings, keep going
		if($this->grid->settings != FALSE)
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
		global $grid, $CFG;
					
		// If there are settings, keep going
		if($grid->getSettings() != FALSE)
		{
			if( $CFG->get('create_grid_css', 'Grid') == TRUE)
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