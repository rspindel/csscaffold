<?php defined('BASEPATH') OR die('No direct access allowed.');

/**
 * Append class
 *
 * @package csscaffold
 **/
class Append extends Plugins
{
	/**
	 * The settings for this plugin
	 * @var string
	 */
	var $settings = array(
		'path' => 'plugins/'
	);

	function import($css)
	{	
		$path = CSSPATH . $this->settings['path'];
		
		foreach(read_dir($path) as $file)
		{			
			// Check for css files and files beginning with - or . 
			if (!check_prefix($file) || !check_type($file, array('css'))) { continue; }
			
			// Add it to our css
			$css .= load($file);
		}
		return $css;
	}

} // END Append

?>