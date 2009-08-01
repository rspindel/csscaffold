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
		# Get the user agent
		$user_agent = ( !empty($_SERVER['HTTP_USER_AGENT']) ? trim($_SERVER['HTTP_USER_AGENT']) : '');
	
		# Set the browser
		self::get_browser($user_agent);
		
		# If it's set as a url param, override it
		if(isset(Config::$configuration['agent']) && isset(Config::$configuration['agent_version']))
		{
			self::$browser = Config::get('agent');
			self::$version = Config::get('agent_version');
		}
		
		# Round the version number to one decimal place
		$i = strpos(self::$version, '.');
		if($i > 0)
		{
			self::$version = substr(self::$version, 0, $i+2);
		}
		
		# Flag them
		Cache::flag(self::$browser);
		Cache::flag(self::$version);
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
	public function process()
	{
		# Find all @browsers
		if($found = CSS::find_selectors("@browser\s*(!|lte|lt|gt|gte)?\s*(IE|Webkit|Gecko|Other|iPhone)\s*(\d+)?", 5))
		{	
			# parse them
			foreach($found[0] as $key => $value)
			{
				$scope 			= $found[2][$key];
				$browser 		= $found[3][$key];
				$version 		= $found[4][$key];
				$properties 	= $found['properties'][$key];
				
				# @browser !name
				if($scope == "!")
				{
					if(self::$browser != $browser)
					{
						CSS::replace($value, $properties);
					}
					else
					{
						CSS::replace($value, '');
					}
				}
				
				# @browser name
				elseif($version == "")
				{
					if(self::$browser == $browser)
					{
						CSS::replace($value, $properties);
					}
					else
					{
						CSS::replace($value, '');
					}
				}
				
				# @browser condition name version
				elseif(self::$browser == $browser && $version != "")
				{
					if(
						($scope == "lt" && self::$version < $version)		||
						($scope == "lte" && self::$version <= $version)		||
						($scope == "gt" && self::$version > $version)		||
						($scope == "gte" && self::$version >= $version)		||
						($scope == "" && self::$version == $version)
					)
					{
						CSS::replace($value, $properties);
					}
					else
					{
						CSS::replace($value, '');
					}
				}
				else
				{					
					CSS::replace($value, '');
				}
			}
		}
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
			self::$browser = "iPhone";
			self::$version = $match[1];
		}
		
		# Webkit (Safari, Shiira etc)
		else if ( preg_match( '/mozilla.*applewebkit\/([0-9a-z\+\-\.]+).*/si', $user_agent, $match ) )
		{
			self::$browser = "Webkit";
			self::$version = $match[1];
		}
		
		# Opera
		else if ( preg_match( '/mozilla.*opera ([0-9a-z\+\-\.]+).*/si', $user_agent, $match ) 
		  || preg_match( '/^opera\/([0-9a-z\+\-\.]+).*/si', $user_agent, $match ) )
		{
			self::$browser = "Opera";
			self::$version = $match[1];
    	}
		
		# Gecko (Firefox, Mozilla, Camino etc)
		else if ( preg_match( '/mozilla.*rv:([0-9a-z\+\-\.]+).*gecko.*/si', $user_agent, $match ) )
		{
			self::$browser = "Gecko";
			self::$version = $match[1];
		}
		
		# MSIE
		else if( preg_match( '/mozilla.*MSIE ([0-9a-z\+\-\.]+).*/si', $user_agent, $match ) )
		{
			self::$browser = "IE";
			self::$version = $match[1];
		}
	}
}