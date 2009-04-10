<?php if (!defined('CSS_CACHEER')) { header('Location:/'); }

require BASEPATH . 'libraries/class.grid.php';

/**
 * Grid class
 *
 * @package csscaffold
 **/
class Layout extends Plugins
{
	/**
	 * Hold the grid object
	 * @var string
	 */
	var $grid;
	
	/**
	 * The plugin settings
	 * @var string
	 */
	var $settings = array(
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

	function process($css)
	{
		// Create a new Grid object and put the css into it
		$this->grid = new Grid($css);
				
		// Build everything
		$css = $this->grid->buildGrid($css);
		
		// Remove the settings
		$css = $this->grid->removeSettings($css);
		
		return $css;
	}
	
	function post_process($css)
	{
		if(Core::config('create_grid_css', 'Layout') == TRUE)
		{
			// Generate the grid.css
			$css = $this->grid->generateGridClasses($css);
		}

		return $css;
	}
}



?>