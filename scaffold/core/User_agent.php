<?php defined('BASEPATH') OR die('No direct access allowed.');

/**
 * User_agent
 *
 * Determines the user agent of the user. Borrowed from the
 * Kohana PHP framework.
 *
 */
abstract class User_agent
{
	/**
	 * The browser of the user
	 *
	 * @var string
	 */
	public static $browser = null;
	
	/**
	 * The version of the browser
	 *
	 * @var string
	 */
	public static $version = null;
	
	/**
	 * The rendering engine of the browser
	 *
	 * @var string
	 */
	public static $engine = null;
	
	/**
	 * Is the browser able to use box-sizing:; natively?
	 *
	 * @var string
	 **/
	static $can_boxsize = null;

	/**
	 * Is the browser able to embed images in the CSS using base64 encoding?
	 *
	 * @var string
	 **/
	static $can_base64 = null;

	/**
	* Retrieves current user agent information:
	*
	* @return null
	*/
	public static function setup()
	{
		$user_agent = ( !empty($_SERVER['HTTP_USER_AGENT']) ? trim($_SERVER['HTTP_USER_AGENT']) : '');
		
		// The order of this array should NOT be changed. Many browsers return
		// multiple browser types so we want to identify the sub-type first.
		$config['browser'] = array
		(
			'Opera'             => 'Opera',
			'MSIE'              => 'Internet Explorer',
			'Internet Explorer' => 'Internet Explorer',
			'Shiira'            => 'Shiira',
			'Firefox'           => 'Firefox',
			'Chimera'           => 'Chimera',
			'Phoenix'           => 'Phoenix',
			'Firebird'          => 'Firebird',
			'Camino'            => 'Camino',
			'Netscape'          => 'Netscape',
			'OmniWeb'           => 'OmniWeb',
			'Safari'            => 'Safari',
			'Konqueror'         => 'Konqueror',
			'Epiphany'          => 'Epiphany',
			'Galeon'            => 'Galeon',
			'icab'              => 'iCab',
			'lynx'              => 'Lynx',
			'links'             => 'Links',
			'hotjava'           => 'HotJava',
			'amaya'             => 'Amaya',
			'IBrowse'           => 'IBrowse',
		);
		
		$config['engine'] = array
		(
			'Opera'             => 'Presto',
			'MSIE'              => 'Trident',
			'Internet Explorer' => 'Trident',
			'Shiira'            => 'Webkit',
			'Firefox'           => 'Gecko',
			'Chimera'           => 'Gecko',
			'Phoenix'           => 'Gecko',
			'Firebird'          => 'Gecko',
			'Camino'            => 'Gecko',
			'OmniWeb'           => 'Webkit',
			'Safari'            => 'Webkit',
			'Konqueror'         => 'KHTML'
		);
		
		$config['mobile'] = array
		(
			'mobileexplorer' => 'Mobile Explorer',
			'openwave'       => 'Open Wave',
			'opera mini'     => 'Opera Mini',
			'operamini'      => 'Opera Mini',
			'elaine'         => 'Palm',
			'palmsource'     => 'Palm',
			'digital paths'  => 'Palm',
			'avantgo'        => 'Avantgo',
			'xiino'          => 'Xiino',
			'palmscape'      => 'Palmscape',
			'nokia'          => 'Nokia',
			'ericsson'       => 'Ericsson',
			'blackBerry'     => 'BlackBerry',
			'motorola'       => 'Motorola',
			'iphone'         => 'iPhone',
			'android'        => 'Android',
		);

		foreach ($config as $type => $data)
		{
			foreach ($data as $agent => $name)
			{
				if (stripos($user_agent, $agent) !== FALSE)
				{
					if ($type === 'browser' AND preg_match('|'.preg_quote($agent).'[^0-9.]*+([0-9.][0-9.a-z]*)|i', $user_agent, $match))
					{
						// Set the browser version
						$info['version'] = $match[1];
					}

					// Set the agent name
					$info[$type] = $name;
				}
			}
		}
		
		self::$browser = $info['browser'];
		self::$version = $info['version'];
		self::$engine = $info['engine'];
		
		unset($info);
	}
	
	/**
	 * Determines if the user agent is capable of using box-sixing
	 *
	 * @author Anthony Short
	 * @return boolen
	 */
	public static function can_boxsize()
	{
		# If we've already set it, just return it.
		if(self::$can_boxsize != null) return self::$can_boxsize;
		
		if (( self::$browser == 'Internet Explorer' && self::$version < 8 ) || self::$version == "" )
		{
			self::$can_boxsize = false;
		}
		else
		{
			self::$can_boxsize = true;
		}
		
		return self::$can_boxsize;
	}
	
	/**
	 * Determine if the browser can do Base64
	 *
	 * @author Anthony Short
	 * @return boolean
	 */
	public static function can_base64()
	{
		# If we've already set it, just return it.
		if(self::$can_base64 != null) return self::$can_base64;
			
		// Safari (WebKit), Firefox & Opera are known to support data: urls so embed base64-encoded images
		if
		(
			(self::$browser == 'Safari' && self::$version >= 125) ||
			(self::$browser == 'Firefox') ||
			(self::$browser == 'Opera' && self::$version >= 7.2)
		)
		{
			self::$can_base64 = true;
		}
		else
		{
			self::$can_base64 = false;
		}
		
		return self::$can_base64;
	}
}