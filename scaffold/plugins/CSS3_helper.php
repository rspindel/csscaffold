<?php defined('BASEPATH') OR die('No direct access allowed.');

/**
 * CSS3Helper
 *
 * This class provides helpers for CSS3 properties to make
 * them easier to use. The border-radius function is now depreciated
 * due to mixins.
 *
 * @author Anthony Short
 * @dependencies None
 **/
class CSS3_helper extends Plugins
{

	/**
	 * The final process before it is cached. This is usually just
	 * formatting of css or anything else just before it's cached
	 *
	 * @author Anthony Short
	 * @param $css
	*/
	function post_process($css)
	{		
		/**
		* @todo @font-face helper. Any font you put in assets/fonts you 
		* can just use in your css without any @font-face rules
		*/
		
		return $css;
	}
	
}