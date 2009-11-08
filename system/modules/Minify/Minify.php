<?php

/**
 * Minify Plugin
 **/
class Minify extends Module
{
	public static function compress()
	{
		self::load_library('Minify_Compressor');
		
		CSS::$css = Minify_CSS_Compressor::process(CSS::$css);
	}
} 
