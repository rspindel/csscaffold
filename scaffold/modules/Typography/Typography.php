<?php

/**
 * Type_suite
 *
 * Outputs a HTML page of every type element using the parsed CSS
 */
class Typography
{
	public static function display()
	{
		if( Scaffold::option('typography') )
		{
			# Make sure we're sending HTML
			header('Content-Type: text/html');
			
			# Load the test suite markup
			Scaffold::load_view('scaffold_typography.php');
			exit;
		}
	}
}