<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Minify Plugin
 **/
class Minify extends Plugins
{
	public static function formatting_process()
	{
		CSS::$css = Minify_CSS_Compressor::process(CSS::$css);
	}
} 
