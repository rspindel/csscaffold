<?php defined('BASEPATH') OR die('No direct access allowed.');

/**
 * Browsers class
 *
 * @package csscaffold
 * @dependencies User_agent, Constants
 **/
class Browsers extends Plugins
{
	/**
	 * The browser of the user
	 *
	 * @var string
	 */
	public static $browser = "Other";
	
	/**
	 * The version of the browser
	 *
	 * @var string
	 */
	public static $version = "";

	/**
	 * The construct is important for plugins. It is where flags MUST 
	 * be set. For each flag that exists, a seperate file will be cached
	 * and only be sent to users that meet the conditions of those flags
	 *
	 * @author Anthony Short
	 */
	public function __construct()
	{	
		$user_agent = ( !empty($_SERVER['HTTP_USER_AGENT']) ? trim($_SERVER['HTTP_USER_AGENT']) : '');
		
		# Set the browser
		$this->get_browser($user_agent);
		
		# If it's set as a url param, override it
		if(Config::$configuration['browser'])
		{
			$this->browser = Config::get('browser');
		}
		
		# Set cache flags
		Cache::flag($this->browser);
		Cache::flag($this->version);
	}
	
	/**
	 * The pre-processing function occurs after the importing,
	 * but before any real processing. This is usually the stage
	 * where we set variables and the like, getting the css ready
	 * for processing.
	 *
	 * @author Anthony Short
	 * @param $css
	 */
	public function pre_process()
	{
		# Find all @browsers
		# parse them
		# Replace them with the properties if it is that browser
		# Otherwise remove it.
	}

	/**
	 * Determines the user agent
	 *
	 * @author Anthony Short
	 * @param $useragent
	 * @return null
	 */
	private function get_browser($user_agent)
	{
		# Safari Mobile
		if ( preg_match( '/mozilla.*applewebkit\/([0-9a-z\+\-\.]+).*mobile.*/si', $user_agent, $match ) )
		{
			$this->browser = "iPhone";
			$this->version = $match[1];
		}
		
		# Webkit (Safari, Shiira etc)
		else if ( preg_match( '/mozilla.*applewebkit\/([0-9a-z\+\-\.]+).*/si', $user_agent, $match ) )
		{
			$this->browser = "Webkit";
			$this->version = $match[1];
		}
		
		# Opera
		else if ( preg_match( '/mozilla.*opera ([0-9a-z\+\-\.]+).*/si', $user_agent, $match ) 
		  || preg_match( '/^opera\/([0-9a-z\+\-\.]+).*/si', $user_agent, $match ) )
		{
			$this->browser = "Opera";
			$this->version = $match[1];
    	}
		
		# Gecko (Firefox, Mozilla, Camino etc)
		else if ( preg_match( '/mozilla.*rv:([0-9a-z\+\-\.]+).*gecko.*/si', $user_agent, $match ) )
		{
			$this->browser = "Gecko";
			$this->version = $match[1];
		}
		
		# MSIE
		else if( preg_match( '/mozilla.*MSIE ([0-9a-z\+\-\.]+).*/si', $user_agent, $match ) )
		{
			$this->browser = "IE";
			$this->version = $match[1];
		}
	}
}