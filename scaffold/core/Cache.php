<?php defined('BASEPATH') OR die('No direct access allowed.');

/**
 * Cache
 *
 * Handles setting, writing and reading from the cache
 * 
 * @author Anthony Short
 */
abstract class Cache
{
	/**
	* The singleton instance of the controller
	*
	* @var object
	**/
	public static $instance;
	
	/**
	* The location of the cache file
	*
	* @var string
	**/
	public static $cached_file; 
	
	/**
	* The modified time of the cached file
	*
	* @var string
	**/
	public static $cached_mod_time;
		
	/**
	 * Stores the flags
	 *
	 * @var array
	 */
	public static $flags = array();

	/**
	* Returns the instance of the core
	*
	* @return self
	**/
	public static function & get_instance()
	{
		return self::$instance;
	}

	/**
	* Clear the set cached file
	*
	* @return void
	* @author Anthony Short
	**/
	public static function remove()
	{		
		unlink(self::$cached_file);
	}
	
	/**
	 * Sets a flag
	 *
	 * @author Anthony Short
	 * @param $flag_name
	 * @return null
	 */
	public static function flag($flag_name)
	{
		self::$flags[$flag_name] = true;
	}
	
	/**
	* Empty the entire cache, removing every cached css file.
	*
	* @return void
	* @author Anthony Short
	**/
	public static function empty_cache($path = CACHEPATH)
	{	
		$f = read_dir($path);

		foreach($f as $file)
		{
			if(substr($file, -3) == 'css')
			{
				unlink($file);
			}
			elseif(is_dir($file))
			{
				self::empty_cache($file);
			}
		}
	}
	
	/**
	* Write to the set cache
	*
	* @return void
	* @author Anthony Short
	**/
	public static function write($data)
	{	   	
	   	# Make sure the cache exists
		self::cache_exists();		
		
		# Put it in the cache
		file_put_contents(self::$cached_file, $data, 0777);
		
		# Set its properties
		chmod(self::$cached_file, 0777);
		touch(self::$cached_file, time());
		
		# Set the config file mod time
		Config::set('cached_mod_time', time());
	}
		
	/**
	* Create hash to allow variables to be cached
	*
	* @return string
	* @author Anthony Short
	**/
	public static function generate_hash($args = array())
	{
		ksort($args);
		return md5(serialize($args));
	}

	/**
	* Make sure the cache exists
	*
	* @return boolean
	* @author Anthony Short
	**/
	public static function cache_exists()
	{
		$cache_info = pathinfo(self::$cached_file);

		if ($cache_info['dirname'] . "/" != CACHEPATH || !is_dir(CACHEPATH))
		{
			$path = CACHEPATH;
			$dirs = explode('/', Config::get('relative_dir'));
						
			foreach ($dirs as $dir)
			{
				$path = join_path($path, $dir);
				
				if (!is_dir($path)) { mkdir($path, 0777); }
			}
		}
		return TRUE;
	}
	
	/**
	* Set the cache file which will be used for this process
	*
	* @return boolean
	* @author Anthony Short
	**/
	public static function set($recache = FALSE)
	{		
		# Generate checksum based on plugin flags
		$checksum = self::generate_hash(self::$flags);
		
		# Determine the name of the cache file
		$cached_file = CACHEPATH.preg_replace('#(.+)(\.css)$#i', "$1-{$checksum}$2", Config::get('relative_file'));
		
		# Save it
		self::$cached_file = $cached_file;

		# Turn off recaching if the cache is locked
		if (Config::get('cache_lock') === TRUE)
		{
			$recache = FALSE;
		}
		
		# Check to see if we should delete the cache file
		if ($recache === TRUE && file_exists($cached_file))
		{
			// Empty out the cache
			self::empty_cache();
		}
		
		# When was the cache last modified
		if (file_exists(self::$cached_file))
		{
			$cached_mod_time =  (int) filemtime(self::$cached_file);
		}
		else
		{
			$cached_mod_time = 0;
		}
		
		Config::set('cached_mod_time', $cached_mod_time);

		return TRUE;
	}
}