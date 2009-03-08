<?php
/******************************************************************************
 Prevent direct access
 ******************************************************************************/
if (!defined('CSS_CACHEER')) { header('Location:/'); }

$plugin_class = 'Browsers';

class Browsers extends CacheerPlugin
{


	function Browsers()
	{
		$ua = parse_user_agent($_SERVER['HTTP_USER_AGENT']);
		
		if($ua['browser'] == 'ie' && $ua['version'] == 7.0)
		{
			$this->flags['IE7'] = true;
		}
		elseif($ua['browser'] == 'ie' && $ua['version'] == 6.0)
		{
			$this->flags['IE7'] = true;
		}
		elseif($ua['browser'] == 'applewebkit' && $ua['version'] >= 525)
		{
			$this->flags['Safari3'] = true;
		}
		elseif($ua['browser'] == 'firefox' && $ua['version'] >= 3)
		{
			$this->flags['Firefox3'] = true;
		}
	}

	
	function pre_process($css)
	{
		global $path;		

		if (isset($this->flags['IE7']) || isset($this->flags['IE6']))
		{
			$file 		= file_get_contents($path['browsers'] . "/ie.css");
			$css 		= $css . $file;
	
			return $css;
		}
		elseif (isset($this->flags['Safari3']))
		{
			$file 		= file_get_contents($path['browsers'] . "/safari.css");		
			$css 		= $css . $file;
			
			return $css;
		}
		elseif (isset($this->flags['Firefox3']))
		{
			$file 		= file_get_contents($path['browsers'] . "/firefox.css");
			$css 		= $css .$file;
		
			return $css;
		}
		else
		{
			return $css;
		}
	}
	
	
	
}

?>