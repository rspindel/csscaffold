<?php if (!defined('CSS_CACHEER')) { header('Location:/'); }

/**
 * The class name
 * @var string
 */
$plugin_class = 'Layout';

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
		$this->grid = new Grid($css);
				
		// If there are settings, keep going
		if($this->grid->config != FALSE)
		{
			// Generate the grid.png
			$this->grid->generateGridImage($css);
			
			// Replace the grid() variables
			$css = $this->grid->replaceGridVariables($css);
			
			// Create the layouts xml for use with the tests
			$this->grid->generateLayoutXML($css);
		
			// Replace the columns:; properties
			$css = $this->grid->replaceColumns($css);
		}
		
		return $css;
	}
	
	function post_process($css)
	{					
		// If there are settings, keep going
		if($this->grid->config != FALSE)
		{
			if($this->grid->config['create_grid_css'] == TRUE)
			{
				// Generate the grid.css
				$css = $this->grid->generateGridClasses($css);
			}
			// Remove the settings
			$css = $this->grid->removeSettings($css);
		}
		return $css;
	}
}



?>