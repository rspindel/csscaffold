<?php defined('BASEPATH') OR die('No direct access allowed.');

/**
 * CSSTidyPlugin
 *
 * @package csscaffold
 * @dependencies CSSTidy
 **/
class Optimizer extends Plugins
{
	function formatting_process()
	{
		$css =& CSS::$css;
					
		$tidy = new csstidy();
							
		$tidy->set_cfg('preserve_css',false);
		$tidy->set_cfg('sort_selectors',false);
		$tidy->set_cfg('sort_properties',true);
		$tidy->set_cfg('merge_selectors',2);
		$tidy->set_cfg('optimise_shorthands',1);
		$tidy->set_cfg('compress_colors',true);
		$tidy->set_cfg('compress_font-weight',false);
		$tidy->set_cfg('lowercase_s',true);
		$tidy->set_cfg('case_properties',1);
		$tidy->set_cfg('remove_bslash',false);
		$tidy->set_cfg('remove_last_;',true);
		$tidy->set_cfg('discard_invalid_properties',false);
		$tidy->set_cfg('css_level','CSS2.1');
		$tidy->set_cfg('time_stamp','false');
		
		$tidy->load_template('highest_compression');
		
		$result = @$tidy->parse($css);
				
		$css = $tidy->print->plain();
		
		if(Config::get('pretty') === true)
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
		}
		else
		{
			CSS::$css = Minify_CSS_Compressor::process(CSS::$css);
		}
	}
} 
