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
		
		$append = "";
				
		if (is_dir($options['Append']['path']))
		{
			if ($dir_handle = opendir($options['Append']['path'])) 
			{
				while (($file = readdir($dir_handle)) !== false) 
				{
					if (substr($file, 0, 1) == '.' || substr($file, 0, 1) == '-' || substr($file, -3) != 'css')
					{ 
						continue; 
					}

					$append .= file_get_contents($options['Append']['path']."/".$file);
				}
				closedir($dir_handle);
			}
		}
		
		// Add them all to our css
		$css = $css . $append;			
		return $css;
	}
} // END Append

?>