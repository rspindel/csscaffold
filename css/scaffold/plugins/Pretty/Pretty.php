<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Pretty
 **/
class Pretty extends Plugins
{
	public static function formatting_process()
	{
		if(CSScaffold::config('core.url_params.pretty') === true)
		{
			CSS::pretty();			
		}
	}
} 
