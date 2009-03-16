<?php if (!defined('CSS_CACHEER')) { header('Location:/'); }

/**
 * The class name
 * @var string
 */
$plugin_class = 'Append';

/**
 * Include the settings
 */
include $config['system_dir'] . '/config/plugins/append.config.php';

/**
 * Append class
 *
 * @package csscaffold
 **/
class Append extends CacheerPlugin
{
	function pre_process($css)
	{
		global $options;
		
		foreach(read_dir($options['Append']['path']) as $file)
		{
			// Check for css files and files beginning with - or . 
			if (!check_prefix($file) || !check_type($file, array('css')))
			{ 
				continue; 
			}
			
			// Add it to our css
			$css .= load($file);
		}
		
		return $css;
	}
} // END Append

?>