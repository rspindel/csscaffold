<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Type_suite
 *
 * Outputs a HTML page of every type element using the parsed CSS
 **/
class Type_suite extends Plugins
{
	public static function output()
	{
		if(CSScaffold::config('core.options.output') == "type_suite")
		{
			header('Content-Type: text/html');
			
			$type = CSScaffold::load_view('Type_suite_typography');
			
			echo($type);
			exit;
		}
	}
} 
