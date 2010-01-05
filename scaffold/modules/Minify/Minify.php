<?php

/**
 * Minify Plugin
 **/
class Minify extends Scaffold_Module
{
	public static function compress($css)
	{		
		require_once( Scaffold::find_file('Minify_Compressor.php', 'minify/libraries', true) );
		
		# Run the Minify compressor
		$css = Minify_CSS_Compressor::process($css);
		
		return $css;
	}
} 
