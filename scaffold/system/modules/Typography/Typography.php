<?php

/**
 * Type_suite
 *
 * Outputs a HTML page of every type element using the parsed CSS
 */
class Typography extends Scaffold_Module
{
	public static function output($css)
	{
		if( CSScaffold::option('typography') )
		{
			# Make sure we're sending HTML
			header('Content-Type: text/html');
			
			# Load the test suite markup
			CSScaffold::load_view('typography.php');
		}
	}
}