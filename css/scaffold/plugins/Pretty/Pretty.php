<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Pretty
 **/
class Pretty extends Plugins
{
	public static function formatting_process()
	{		
		if(CSScaffold::config('core.options.pretty') === true)
		{
			CSS::pretty();			
		}
	}
} 
