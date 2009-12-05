<?php

/**
 * Minify Plugin
 **/
class Minify extends Scaffold_Module
{
	public static function compress($css)
	{		
		require_once( CSScaffold::find_file('Minify_Compressor.php', 'minify/libraries', true) );
		
		return Minify_CSS_Compressor::process($css);
	}
} 
