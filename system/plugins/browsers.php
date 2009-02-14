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
		elseif($ua['browser'] == 'applewebkit' && $ua['version'] >= 525)
		{
			$this->flags['Safari3'] = true;
		}
		elseif($ua['browser'] == 'firefox' && $ua['version'] >= 3)
		{
			$this->flags['Firefox3'] = true;
		}
	}

	
	
	
	function getOpacity($css)
	{
		if(preg_match_all('/([\s.#a-z,-]*)\s*\{[^}]*opacity\:\s*(\d\.\d).*?\}/sx', $css, $matches))
		{
			foreach($matches[0] as $key => $match)
			{
				$selectors			= $matches[1][$key];
				$opacity_value 	= $matches[2][$key];
				
				// Convert it for the filter 
				$opacity_value = $opacity_value * 100;
				
				// Get rid of excess whitespace
				$selectors = trim($selectors);
				
				$ie_string .= $selectors . "{filter:alpha(opacity='".$opacity_value."'); zoom:1;}";	
			}
		}		
		
		return $ie_string;
	}
	

	
	
	function pre_process($css)
	{		

		if (isset($this->flags['IE7']))
		{
			$file 		= file_get_contents("specific/ie7.css");
			$opacity 	= $this -> getOpacity($css);
			$css 		= $css . $inline . $file . $opacity;
	
			return $css;
		}
		elseif (isset($this->flags['Safari3']))
		{
			$file 		= file_get_contents("specific/safari.css");		
			$css 		= $css . $file;
			
			return $css;
		}
		elseif (isset($this->flags['Firefox3']))
		{
			$file 		= file_get_contents("specific/firefox.css");
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