<?php

/**
* -------------------------------------------------------------------------
*	CSS Parsing Functions
* -------------------------------------------------------------------------
*/

	
	/**
	 * Checks if a file is an image.
	 *
	 * @author Anthony Short
	 * @param $path string
	 */
	function is_image($path)
	{
		if (extension($path) == ('gif' || 'jpg' || 'jpeg' || 'png'))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Checks if a file is css.
	 *
	 * @author Anthony Short
	 * @param $path string
	 */	
	function is_css($path)
	{
		return (extension($path) == 'css') ? true : false;
	}
    
/**
* -------------------------------------------------------------------------
*	General Functions
* -------------------------------------------------------------------------
*/

    /**
	 * Rounds a number to the nearest multiple of another number
	 *
	 * @author Anthony Short
	 * @param $number int
	 * @param $multiple int
	 */
	function round_nearest($number,$multiple) 
	{ 
		return round($number/$multiple)*$multiple;
	}
  
	/**
	 * Prints out the value and exits
	 *
	 * @author Anthony Short
	 * @param $var
	 */
	function stop($var) 
	{
		header('Content-Type: text/plain');
		print_r($var);
		exit;
	}

	/**
	 * LOAD XML
	 * 
	 * A quicker way to load an XML file to an XML object
	 *
	 * @author Anthony Short
	 * @param $file string
	 */
	function load_xml($file)
	{		
		return simplexml_load_string(file_get_contents($file));
	}

/**
* -------------------------------------------------------------------------
*	String Functions
* -------------------------------------------------------------------------
*/
	/**
	 * Quick regex matching
	 *
	 * @author Anthony Short
	 * @param $regex
	 * @param $subject
	 * @param $i
	 * @return array
	 */
	function match($regex, $subject, $i = "")
	{
		if(preg_match_all($regex, $subject, $match))
		{
			return ($i == "") ? $match : $match[$i];
		}
		else
		{
			return array();
		}
	}

	/** 
	 * Removes all quotes from a string
	 *
	 * @author Anthony Short
	 * @param $str string
	 */
	function remove_all_quotes($str)
	{
		return str_replace(array('"', "'"), '', $str);
	}
	
	/** 
	 * Removes quotes surrounding a string
	 *
	 * @author Anthony Short
	 * @param $str string
	 */
	function unquote($str)
	{
		return preg_replace('#^("|\')|("|\')$#', '', $str);
	}
	    
     /**
	  * Outputs a filesize in a human readable format
	  *
	  * @author Anthony Short
	  * @param $val The filesize in bytes
	  * @param $round
	 */
	function readable_size($val, $round = 0)
	{
		$unit = array('','K','M','G','T','P','E','Z','Y');
		
		while($val >= 1000)
		{
			$val /= 1024;
			array_shift($unit);
		}
		
		return round($val, $round) . array_shift($unit) . 'B';
	}

/**
* -------------------------------------------------------------------------
*	Directory Functions
* -------------------------------------------------------------------------
*/

	/**
	 * Takes a relative path, gets the full server path, removes
	 * the www root path, leaving only the url path to the file/folder
	 *
	 * @author Anthony Short
	 * @param $relative_path
	 */
	function urlpath($relative_path) 
	{
		return  str_replace($_SERVER['DOCUMENT_ROOT'],'', realpath($relative_path) );
	}
	
	/** 
	 * Makes sure the string ends with a /
	 *
	 * @author Anthony Short
	 * @param $str string
	 */
    function add_end_slash($str)
    {
        return rtrim($str, '/') . '/';
    }
    
    /** 
	 * Makes sure the string starts with a /
	 *
	 * @author Anthony Short
	 * @param $str string
	 */
    function add_start_slash($str)
    {
        return ltrim($str, '/') . '/';
    }
	
	/** 
	 * Makes sure the string doesn't end with a /
	 *
	 * @author Anthony Short
	 * @param $str string
	 */
    function trim_slashes($str)
    {
        return trim($str, '/');
    }
    
    /** 
	 * Replaces double slashes in urls with singles
	 *
	 * @author Anthony Short
	 * @param $str string
	 */
	function reduce_double_slashes($str)
	{
		return preg_replace("#([^:])//+#", "\\1/", $str);
	}

    /**
	 * Joins any number of paths together
	 *
	 * @param $path
	 */
	function join_path()
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
		
		return reduce_double_slashes($path);
	}
	
	function fix_path($path)
	{
		return dirname($path . './');
	}

/**
* -------------------------------------------------------------------------
*	File Functions
* -------------------------------------------------------------------------
*/
    	
	// Loads and returns a file
	function load($f)
	{
		if(!file_exists($f))
		{
			error("Cannot load file: $f");
			exit;
		}
		elseif(is_dir($f))
		{
			return load_dir($f);
		}
		else
		{
			return file_get_contents($f);
		}
	}
	
	// Loads every file in a directory to a string
	function load_dir_to_string($directory)
	{	
		$loaded = "";
		
		if ($dir_handle = opendir($directory)) 
		{
			while (($file = readdir($dir_handle)) !== false) 
			{
				if (!check_prefix($file))
				{ 
					continue; 
				}
				
				$loaded .= file_get_contents($directory . "/" .$file);
			}
			
			closedir($dir_handle);
		}
		return $loaded;
	}
	
	// Returns the files of a directory as a string
	function read_dir($directory)
	{
		$files = array();
		
		if(is_dir($directory))
		{
			if ($listing = scandir($directory)) 
        	{
            	foreach($listing as $file)
            	{
					if (!is_enabled($file))
					{ 
						continue; 
					}
					
					$files[$file] = join_path($directory, $file);
				}
			}
		}
		else
		{
			error("Cannot read directory - ". $directory);
			exit;
		}
		return $files;
	}

	/**
	 * Returns the extension of the file
	 *	
	 * @param $path
	 */
	function extension($path) 
	{
	  $qpos = strpos($path, "?");
	
	  if ($qpos!==false) $path = substr($path, 0, $qpos);
	
	  return pathinfo($path, PATHINFO_EXTENSION);;
	} 

	/**
	 * Checks if a file starts with a dot or dash. If so, it
	 * isn't activated and should be ignored
	 *
	 * @author Anthony Short
	 * @param $file
	 */
	function is_enabled($file)
	{
		if(substr($file, 0, 1) == '.' || substr($file, 0, 1) == '-')
		{
			return false;
		}
		else
		{
			return true;
		}
	}
	
