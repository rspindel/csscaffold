<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Pretty
 **/
class Pretty extends Plugins
{
	function formatting_process()
	{
		if(CSScaffold::config('pretty') === true)
		{
			CSS::pretty();			
		}
	}
} 
