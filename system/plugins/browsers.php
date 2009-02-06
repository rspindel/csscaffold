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
//		elseif($ua['browser'] == 'ie' && $ua['version'] == 6.0)
//		{
//			$this->flags['IE6'] = true;
//		}
		elseif($ua['browser'] == 'applewebkit' && $ua['version'] >= 525)
		{
			$this->flags['Safari3'] = true;
		}
		elseif($ua['browser'] == 'firefox' && $ua['version'] >= 3)
		{
			$this->flags['Firefox3'] = true;
		}
	}
	
	
	
//	function separateCSS($css, $class)
//	{
//		global $css_dir;
//		
//		if(preg_match_all("/(\.".$class.".*?)(\{.*?\})/sx",$css,$matches))
//		{
//			foreach($matches[1] as $key => $match)
//			{
//				
//				$ie6_selectors = array();
//				
//				$selectors = explode(",", $match);
//				
				// Remove the selectors that don't contain .ie6 from the string
//				foreach($selectors as $k => $selector)
//				{
//					if(stristr($selector, ".".$class) == TRUE) {
//    					array_push($ie6_selectors, $selector);
//    					unset($selectors[$k]);
//  					}
//				}
//								
//				$ie6_selectors 	= implode(",", $ie6_selectors);
//				$selectors 		= implode(",", $selectors);
//								
//				$properties 		= $matches[2][$key];
//				
//				$ie6_string 		.= $ie6_selectors.$properties;
//				$old_string 		= $matches[0][$key];
//				$new_string 		= $selectors.$properties;
//	
				//If theres other selectors in the string as well, put it back in without the ie stuff
//				if($selectors != "")
//				{
//					$css = str_replace($old_string,$new_string,$css);
//				}
				// Otherwise get rid of that property group with no selector!
//				else
//				{
//					$css = str_replace($old_string,"",$css);
//				}
//				
//			}
//			
//			$ie6_string = str_replace(".".$class." ",'',$ie6_string);	
//		}
//			
//		return $ie6_string;
//	}
	
	
	
	
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
		// Find each instance of opacity:x and make an ie equivilent 
		//$ie_opacity = $this -> getOpacity($css);
		
//		if (isset($this->flags['IE6']))
//		{	
			// Get the contents of the ie6.css and ie7.css
//			$ie6_file = file_get_contents("browser-specific/ie6.css");
//			
			// Get the targetted browser styles out of the css
//			$ie6_inline = $this -> separateCSS($css, 'ie6');
//			
//			$css = $css . $ie6_inline . $ie6_file . $opacity;
//			
//			return $css;
//		}
		
		
		if (isset($this->flags['IE7']))
		{
			$file = file_get_contents("browser-specific/ie7.css");
			
			$opacity = $this -> getOpacity($css);
			
			$css = $css . $inline . $file . $opacity;
			
			return $css;
		}
		
		
		elseif (isset($this->flags['Safari3']))
		{
			$file = file_get_contents("browser-specific/webkit.css");
						
			$css = $css . $file;
			
			return $css;
		}
		
		
		elseif (isset($this->flags['Firefox3']))
		{
			$file = file_get_contents("browser-specific/gecko.css");
			
			$css = $css .$file;
		
			return $css;
		}
		
		
		else
		{
			return $css;
		}
		
		
	}
	
	
	
}

?>