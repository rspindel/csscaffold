<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Browsers class
 *
 * @package csscaffold
 * @dependencies None
 **/
class Browsers extends Plugins
{
	/**
	 * The browser of the user
	 *
	 * @var string
	 */
	public static $browser = false;
	
	/**
	 * The version of the browser
	 *
	 * @var string
	 */
	public static $version = false;

	/**
	 * Set cache flags
	 *
	 * @author Anthony Short
	 */
	public static function flag()
	{
		# Set the browser
		self::get_browser(CSScaffold::$user_agent);
		
		# If it's set as a url param, override it
		if(CSScaffold::config('core.url_params.browser') != '')
		{
			self::$browser = CSScaffold::config('core.url_params.browser');
			self::$version = CSScaffold::config('core.url_params.version');
		}
		
		# Round the version number to one decimal place
		$i = strpos(self::$version, '.');
		
		if($i > 0)
			self::$version = substr(self::$version, 0, $i+2);
		
		# Flag them if it's IE
		if(self::$browser == "IE")
		{
			CSScaffold::flag(self::$browser);
			CSScaffold::flag(self::$version);
		}
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
	public static function process()
	{
		if(self::$browser = "IE")
		{
			# Find all @browsers
			if($found = CSS::find_selectors("@browser\s*(!|lte|lt|gt|gte)?\s*(IE)\s*(\d+)?", 5))
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
	}

	/**
	 * Determines the user agent
	 *
	 * @author Anthony Short
	 * @param $useragent
	 * @return null
	 */
	private static function get_browser($user_agent)
	{
		if(self::$version = CSScaffold::config('Browsers.version')) 
		{ 
			self::$browser = "IE";
		}
		else
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
}