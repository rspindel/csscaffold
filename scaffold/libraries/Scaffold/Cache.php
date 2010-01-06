<?php

/**
 * Scaffold_Cache
 *
 * Handles the file caching
 * 
 * @author Anthony Short
 * @package CSScaffold
 */
class Scaffold_Cache
{
	/**
	 * The server path to the cache directory
	 *
	 * @var string
	 */
	private $cache_path;
	
	/**
	 * Cache lifetime
	 *
	 * @var string
	 */
	private $lifetime = 0;
	
	/**
	 * Is the cache frozen/locked?
	 *
	 * @var boolan
	 */
	private $frozen = false;

	/**
	 * Sets up the cache path
	 *
	 * @return return type
	 */
	public function __construct($path,$lifetime,$frozen)
	{
		if (!is_dir($path))
			Scaffold_Log::log("Cache path does not exist. $path",0);
			
		if (!is_writable($path))
			Scaffold_Log::log("Cache path is not writable. $path",0);

		$this->cache_path = $path;
		$this->lifetime($lifetime);
		$this->freeze($frozen);
	}
	
	/**
	 * Freeze/lock the cache
	 *
	 * @param $locked
	 * @return return type
	 */
	public function freeze($locked)
	{
		$this->frozen = $locked;
	}
	
	/**
	 * Return whether or not a file is still fresh and can be used
	 *
	 * @param $file
	 * @return boolan
	 */
	public function recache($file,$time)
	{
		if( 
			$this->frozen === false OR 
			( $this->frozen === true AND ( $this->exists($file) === false OR $this->modified($file) <= $time ) )
		)
		{
			return true;
		}

		return false;
	}
	
	/**
	 * Sets the lifetime of the cache
	 *
	 * @param $time
	 * @return void
	 */
	public function lifetime($time)
	{
		$this->lifetime = $time;
	}
	
	/**
	 * Returns the particular cache file as a string
	 *
	 * @param $file
	 * @return string $string The contents of the file
	 */
	public function open($file)
	{
		if($this->exists($file))
		{
			return file_get_contents($this->find($file));
		}
		
		return false;
	}
	
	/**
	 * Returns the full path of the cache file, if it exists
	 *
	 * @param $file
	 * @return string
	 */
	public function exists($file)
	{
		if(is_file($this->cache_path.$file))
			return true;

		if(is_dir($this->cache_path.$file))
		{
			return true;
		}
			
		return false;
	}
	
	/**
	 * Returns the last modified date of a cache file
	 *
	 * @param $file
	 * @return int
	 */
	public function modified($file)
	{
		return ( $this->exists($file) ) ? (int) filemtime($this->find($file)) : 0 ;
	}
	
	/**
	 * Finds a file inside the cache and returns it's full path
	 *
	 * @param $file
	 * @return string
	 */
	public function find($file)
	{
		if($this->exists($file))
			return $this->cache_path.$file;
			
		return false;
	}
	
	/**
	 * Load data file from the cache. Use for storing configs and
	 * other temporary internal data. Only lasts for the lifetime of the cache
	 * which by default, is an hour.
	 *
	 * @param   string   unique name of cache
	 * @param   integer  expiration in seconds
	 * @return  mixed
	 */
	public function temp($name)
	{
		if ($this->lifetime > 0)
		{
			if($this->exists($name))
			{
				# If the file is older than the cache lifetime (eg an hour)
				if ( (time() - filemtime($this->find($name))) < $this->lifetime )
				{
					return $this->open($name);
				}
				else
				{
					$this->remove($name);
				}
			}
		}

		return null;
	}
	
	/**
	 * Get a cache file, or delete it if it's older than the original
	 *
	 * @param $name
	 * @return mixed
	 */
	public function fetch($name,$time = 0)
	{
		if($this->exists($name))
		{
			if ( $this->recache($name,$time) === false )
			{
				return $this->open($name);
			}
			else
			{
				# Cache is invalid, delete it
				$this->remove($name);
			}
		}

		return NULL;
	}

	/**
	 * Write to the set cache
	 *
	 * @return void
	 * @author Anthony Short
	 */
	public function write( $data, $target = '', $append = false )
	{	
		# Create the cache file
		$this->create(dirname($target));

		$target = $this->cache_path.$target;
		
		if(is_array($data))
			$data = serialize($data);

		# Put it in the cache
		if($append)
		{
			file_put_contents($target, $data, FILE_APPEND);
		}
		else
		{
			file_put_contents($target, $data);
		}
		
		# Set its parmissions
		chmod($target, 0777);
		touch($target, time());
		
		return true;
	}
	
	/**
	 * Creates an ID for using the cache
	 *
	 * @param $input
	 * @return string The md5 hashed string
	 */
	public static function id($input)
	{
		if( is_array($input) )
		{
			$input = serialize($input);
		}
		
		return md5($input);
	}
	
	/**
	 * Removes a cache item
	 *
	 * @param $file
	 * @return boolean
	 */
	public function remove($file)
	{
		if($this->find($file))
		{
			unlink($this->find($file));
			return true;
		}
			
		return false;
	}
	
	/**
	 * Remove a cache directory and sub-directories
	 *
	 * @param $dir
	 * @return boolean
	 */
	public function remove_dir($dir)
	{
		if(!is_dir($dir))
		{
			$dir = $this->find($dir);
		}

		$files = glob( $dir . '*', GLOB_MARK ); 
		
		foreach( $files as $file )
		{ 
			if( is_dir($file) )
			{ 
				$this->remove_dir( $file );
				rmdir( $file ); 
			}
			else 
			{
				unlink( $file ); 
			}
		}
		
		return true;
	}

	/**
	 * Create the cache file directory
	 */
	public function create($path)
	{	
		# If it already exists
		if(is_dir($this->cache_path.$path))
			return true;

		# Create the directories inside the cache folder
		$next = "";
				
		foreach(explode('/',$path) as $dir)
		{
			$next = '/' . $next . '/' . $dir;

			if(!is_dir($this->cache_path.$next)) 
			{
				mkdir($this->cache_path.$next);
				chmod($this->cache_path.$next, 0777);
			}
		}
		
		return true;
	}
}