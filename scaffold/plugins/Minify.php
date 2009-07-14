<?php defined('BASEPATH') OR die('No direct access allowed.');

require join_path(BASEPATH,'libraries/Minify_Compressor.php');

/**
 * Minify
 *
 * @author Anthony Short
 * @dependencies Minify_compressor
 **/
class Minify extends Plugins
{
	/**
	 * We remove unnescessary characters from the css
	 * string to make it lighter and quick to process.
	 *
	 * @author Anthony Short
	 * @param $css
	 * @return string
	 */
	function pre_process($css)
	{
		# Remove comments
		$css = remove_css_comments($css);
		
		# Remove extra white space
		$css = preg_replace('/\s+/', ' ', $css);
		
		# Remove line breaks
		$css = preg_replace('/\n|\r/', '', $css);
		
		return $css;
	}
	
	function formatting_process($css)
	{		
		#return Minify_CSS_Compressor::process($css);
		return $css;
	}
}