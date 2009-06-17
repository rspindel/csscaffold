<?php defined('BASEPATH') OR die('No direct access allowed.');

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
		
		if ($this->grid->active === TRUE)
		{
			// Build everything
			$css = $this->grid->buildGrid($css);
			
			// the round() function
			if(preg_match_all('/round\((\d+)\)/', $css, $matches))
			{
				foreach($matches[1] as $key => $match)
				{
					$num = round_nearest($match,Core::config('baseline', 'Layout'));
					$css = str_replace($matches[0][$key],$num."px",$css);
				}
			}	
			
			// Remove the settings
			$css = $this->grid->removeSettings($css);
		}
		
		return $css;
	}
	
	function post_process($css)
	{
		if(Core::config('create_grid_css', 'Layout') === TRUE && $this->grid->active === TRUE)
		{
			// Generate the grid.css
			$css = $this->grid->generateGridClasses($css);
		}

		return $css;
	}
}



?>