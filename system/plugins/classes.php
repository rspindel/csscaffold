<?php
/******************************************************************************
 Prevent direct access
 ******************************************************************************/
if (!defined('CSS_CACHEER')) { header('Location:/'); }

$plugin_class = 'Classes';

class Classes extends CacheerPlugin
{
	function post_process($css)
	{
		// Put all the selectors into an array
		preg_match_all("/([\w#,.@\-\+\s:]+)\s*\{(.*?)\}/sx", $css, $selector);
	
		foreach ($selector[2] as $key => $properties)
		{		
			// Find selectors with the class property
			if(preg_match_all('/class:(.*?)\;/sx', $properties, $classproperty))
			{	
				$classes = explode(",", $classproperty[1][0]);
				
				foreach ($classes as $num => $class)
				{ 
					// Find the original class that we're adding our selector to
					if(preg_match_all("/(\.".$class.".*?)\s*\{(.*?)\}/sx", $css, $base))
					{
						$selectors = explode(",", $base[1][0]);

						// Add our new selector to the selector array
						array_push($selectors, $selector[1][$key]);
						
						$selectors = implode(",", $selectors);
						
						$newselector = $selectors."{".$base[2][0]."}";
						$oldselector = $base[0][0];
						$css = str_replace($oldselector, $newselector, $css);
					}
				}
				
				// Remove the class properties
				$css = str_replace($classproperty[0][0],"",$css);
			}
		}
		
		return $css;
	}
}

?>