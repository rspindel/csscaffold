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
	 * The construct is important for plugins. It is where flags MUST 
	 * be set. For each flag that exists, a seperate file will be cached
	 * and only be sent to users that meet the conditions of those flags
	 *
	 * @author Anthony Short
	 */
	function __construct()
	{	
		# Set a flag for their browser, so it caches it for each
		# browser. If we don't set flags, then it would only cache
		# the css once, using the first browser to request it as the
		# user agent. 
		Cache::flag(User_agent::$browser);
		Cache::flag(User_agent::$version);
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
	function pre_process()
	{
		Constants::set('browser', User_agent::$browser);
		Constants::set('version', User_agent::$version);
	}
	
}