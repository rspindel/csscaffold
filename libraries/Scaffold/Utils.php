<?php

/**
 * Utils
 *
 * Holds various utility functions used by CSScaffold
 * 
 * @author Anthony Short
 */
class Scaffold_Utils
{
	/**
	 * Preg quote. But better. Does the - character too. Like it should.
	 *
	 * @author Anthony Short
	 * @param $str
	 * @return string
	 */
	public static function preg_quote($str)
	{
		$str = preg_quote($str);
		
		# PHP 5.3 does this, but any version lower doesn't
		if (version_compare(PHP_VERSION, '5.3.0') < 0)
		{
   			$str = str_replace('-','\-',$str);
		}		
		
		$str = preg_replace('#\s+#','\s*',$str);
		$str = str_replace('#','\#',$str);
		$str = str_replace('/','\/',$str);

		return $str;
	}
	
	/** 
	 * Removes quotes surrounding a string
	 *
	 * @author Anthony Short
	 * @param $str string
	 */
	public static function unquote($str)
	{
		return trim($str, "'\" ");
	}
	
	/** 
	 * Makes sure the string ends with a /
	 *
	 * @author Anthony Short
	 * @param $str string
	 */
	public static function right_slash($str)
	{
	    return rtrim($str, '/') . '/';
	}
	
	/** 
	 * Makes sure the string starts with a /
	 *
	 * @author Anthony Short
	 * @param $str string
	 */
	public static function left_slash($str)
	{
	    return '/' . ltrim($str, '/');
	}
	
	/** 
	 * Makes sure the string doesn't end with a /
	 *
	 * @author Anthony Short
	 * @param $str string
	 */
	public static function trim_slashes($str)
	{
	    return trim($str, '/');
	}
	
	/** 
	 * Replaces double slashes in urls with singles
	 *
	 * @author Anthony Short
	 * @param $str string
	 */
	public static function reduce_double_slashes($str)
	{
		return preg_replace("#//+#", "/", $str);
	}
	
	/**
	 * Joins any number of paths together
	 *
	 * @param $path
	 */
	public static function join_path()
	{
		$num_args = func_num_args();
		$args = func_get_args();
		$path = $args[0];
		
		if( $num_args > 1 )
		{
			for ($i = 1; $i < $num_args; $i++)
			{
				$path .= DIRECTORY_SEPARATOR.$args[$i];
			}
		}
		
		return self::reduce_double_slashes($path);
	}
	
	/**
	 * Returns the size of a string as human readable
	 *
	 * @author Anthony Short
	 * @param $string
	 * @return string Size of string
	 */
	public static function readable_size($string)
	{
		$units = explode(' ','bytes KB MB GB TB PB');
		$size = strlen($string);
		$mod = 1000;
		
		for ($i = 0; $size > $mod; $i++) 
		{
			$size /= $mod;
		}
		
		return round($size, 2) . ' ' . $units[$i];
	}

}