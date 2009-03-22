<?php if (!defined('CSS_CACHEER')) { header('Location:/'); }

/**
 * The class name
 * @var string
 */
$plugin_class = 'Browsers';

/**
 * The plugin settings
 * @var string
 */
$settings = array(
	'path' => 'specific'
);


/**
 * Browsers class
 *
 * @package csscaffold
 **/
class Browsers extends Plugins
{	
	/**
	 * Construct function
	 *
	 * @return void
	 **/
	function Browsers()
	{
		global $UA;
		
		if($UA->browser == 'ie' && $UA->version == 7.0)
		{
			$this->flags['IE7'] = true;
		}
		elseif($UA->browser == 'ie' && $UA->version == 6.0)
		{
			$this->flags['IE7'] = true;
		}
		elseif($UA->browser == 'ie' && $UA->version == 8.0)
		{
			$this->flags['IE8'] = true;
		}
		
		elseif($UA->browser == 'applewebkit' && $UA->version >= 525)
		{
			$this->flags['Safari3'] = true;
		}
		elseif($UA->browser == 'applewebkit' && $UA->version >= 528)
		{
			$this->flags['Safari4'] = true;
		}
		
		elseif($UA->browser == 'firefox' && $UA->version >= 2)
		{
			$this->flags['Firefox2'] = true;
		}
		elseif($UA->browser == 'firefox' && $UA->version >= 3)
		{
			$this->flags['Firefox3'] = true;
		}
		
		elseif($UA->browser == 'opera')
		{
			$this->flags['Opera'] = true;
		}
		
		else
		{
			$this->flags['UnknownBrowser'] = true;
		}		
	}

	/**
	 * pre_process function
	 *
	 * @return $css
	 **/
	function pre_process($css)
	{		
		
		if (isset($this->flags['IE7']) || isset($this->flags['IE6']))
		{
			$file 		= file_get_contents($options['Browsers']['path'] . "/ie.css");
			$css 		= $css . $file;
	
			return $css;
		}
		elseif (isset($this->flags['Safari3']))
		{
			$file 		= file_get_contents($options['Browsers']['path'] . "/safari.css");		
			$css 		= $css . $file;
			
			return $css;
		}
		elseif (isset($this->flags['Firefox3']))
		{
			$file 		= file_get_contents($options['Browsers']['path'] . "/firefox.css");
			$css 		= $css .$file;
		
			return $css;
		}
		else
		{
			return $css;
		}
	}
	
} // END Browsers

?>