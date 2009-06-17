<?php defined('BASEPATH') OR die('No direct access allowed.');

/**
 * Browsers class
 *
 * @package csscaffold
 **/
class Browsers extends Plugins
{

	/**
	 * The plugin settings
	 * @var string
	 */
	var $settings = array(
		'path' => 'specific'
	);
	
	/**
	 * Construct function
	 *
	 * @return void
	 **/
	function __construct()
	{	
		// Set a flag for their browser, so it caches it for each
		// browser. If we don't set flags, then it would only cache
		// the css type of the first browser to request it. 
		$this->flags[Core::user_agent('browser')] = true;
	}

	/**
	 * pre_process function
	 *
	 * @return $css
	 **/
	function pre_process($css)
	{		
		if (Core::user_agent('browser') == "Internet Explorer")
		{
			$css .= file_get_contents(CSSPATH . $this->settings['path'] . "/ie.css");
			return $css;
		}
		elseif (Core::user_agent('browser') == "Safari")
		{
			$css .= file_get_contents(CSSPATH . $this->settings['path'] . "/safari.css");
			return $css;
		}
		elseif (Core::user_agent('browser') == "Firefox")
		{
			$css .= file_get_contents(CSSPATH . $this->settings['path'] . "/firefox.css");
			return $css;
		}
		elseif (Core::user_agent('browser') == "Opera")
		{
			$css .= file_get_contents(CSSPATH . $this->settings['path'] . "/opera.css");
			return $css;
		}
		else
		{
			return $css;
		}
	}
	
} // END Browsers