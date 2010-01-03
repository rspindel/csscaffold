<?php

/**
 * Pretty
 *
 * Reformats the CSS so that it is readable, rather than being minified.
 * 
 * @author Anthony Short
 */
class Pretty extends Scaffold_Module
{
	/**
	 * The prettified CSS
	 *
	 * @var string
	 */
	public static $css;

	/**
	 * Makes the CSS readable
	 */
	public static function output($css)
	{	
		if( CSScaffold::option('pretty') )
		{
			$css = preg_replace('#(/\*[^*]*\*+([^/*][^*]*\*+)*/|url\(data:[^\)]+\))#e', "'esc('.base64_encode('$1').')'", $css); // escape comments, data protocol to prevent processing
				
			$css = str_replace(';', ";\r\r", $css); // line break after semi-colons (for @import)
			$css = preg_replace('#([-a-z]+):\s*([^;}{]+);\s*#i', "$1: $2;\r\t", $css); // normalize property name/value space
			$css = preg_replace('#\s*\{\s*#', "\r{\r\t", $css); // normalize space around opening brackets
			$css = preg_replace('#\s*\}\s*#', "\r}\r\r", $css); // normalize space around closing brackets
			$css = preg_replace('#,\s*#', ",\r", $css); // new line for each selector in a compound selector
			// remove returns after commas in property values
			if (preg_match_all('#:[^;]+,[^;]+;#', $css, $m))
			{
				foreach($m[0] as $oops)
				{
					$css = str_replace($oops, preg_replace('#,\r#', ', ', $oops), $css);
				}
			}
			$css = preg_replace('#esc\(([^\)]+)\)#e', "base64_decode('$1')", $css); // unescape escaped blocks
			
			// indent nested @media rules
			if (preg_match('#@media[^\{]*\{(.*\}\s*)\}#', $css, $m))
			{
				$css = str_replace($m[0], str_replace($m[1], "\r\t".preg_replace("#\r#", "\r\t", trim($m[1]))."\r", $m[0]), $css);
			}
			
			header('Content-Type: text/css');
			CSScaffold::set_output($css);
		}
	}
}