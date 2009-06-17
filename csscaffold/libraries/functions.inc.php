<?php 
    
/**
* -------------------------------------------------------------------------
*	General Functions
* -------------------------------------------------------------------------
*/

    // Round a number to the nearest multiple
	function round_nearest($number,$multiple) 
	{ 
		return round($number/$multiple)*$multiple;
	}
    
	
	// Quick wrapper for preg_match
	function match($regex, $str, $i = 0)
	{
	    if(preg_match($regex, $str, $match) == 1)
	        return $match[$i];
	    else
	        return false;
	}
	
	// Displays an error message
	function error($message) 
	{
		print "ERROR : $message\n";
	}
	
	// Debug information
	function dump($var)
	{
		var_dump($var);
		die();
	}
	
	// Print out and exit
	function stop($var) 
	{
		header('Content-Type: text/plain');
		print_r($var);
		exit;
	}
	
	// Log a message
	function log_message($message)
	{
		$f = "system/logs/plugin_report.txt";
		$log = file_get_contents($f);
		$log .= gmdate('r') . "\n" . $message . "\n"; 
		file_put_contents($f,$log);
	}

/**
* -------------------------------------------------------------------------
*	String Functions
* -------------------------------------------------------------------------
*/

	// Removes slashes from the end of a string
	function trim_slashes($str)
	{
		return trim($str, '/');
	} 
	
	// Removes slashes from the start and end of a string
	function strip_slashes($str)
	{
		if (is_array($str))
		{	
			foreach ($str as $key => $val)
			{
				$str[$key] = strip_slashes($val);
			}
		}
		else
		{
			$str = stripslashes($str);
		}
	
		return $str;
	}
	
	// Removes quotes from a string
	function strip_quotes($str)
	{
		return str_replace(array('"', "'"), '', $str);
	}
	
	// Converts quotes to entities
	function quotes_to_entities($str)
	{	
		return str_replace(array("\'","\"","'",'"'), array("&#39;","&quot;","&#39;","&quot;"), $str);
	}
	
	// Replaces double slashes in urls with singles
	function reduce_double_slashes($str)
	{
		return preg_replace("#([^:])//+#", "\\1/", $str);
	}
	
	// Ensures $str ends with a single /
    function slash($str)
    {
        return rtrim($str, '/') . '/';
    }
    
    // Ensures $str DOES NOT end with a /
    function unslash($str)
    {
        return rtrim($str, '/');
    }

/**
* -------------------------------------------------------------------------
*	File Functions
* -------------------------------------------------------------------------
*/
     
    // Outputs a filesize in human readable format.
    function bytes2str($val, $round = 0)
    {
        $unit = array('','K','M','G','T','P','E','Z','Y');
        while($val >= 1000)
        {
            $val /= 1024;
            array_shift($unit);
        }
        return round($val, $round) . array_shift($unit) . 'B';
    }
    	
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
	function load_dir($directory)
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
		if(is_dir($directory))
		{
			if ($dir_handle = opendir($directory)) 
			{
				while (($file = readdir($dir_handle)) !== false) 
				{
					if (!check_prefix($file))
					{ 
						continue; 
					}
					
					$files[$file] = $directory . "/" . $file;
				}
				closedir($dir_handle);
			}
		}
		else
		{
			error("Cannot read directory - ". $directory);
			exit;
		}
		
		return $files;
	}
	
	// Checks the filetype against an array of filetypes
	function check_type($file, $ext)
	{
		if(in_array(substr($file, -3), $ext))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	// Checks if a file starts with a . or -
	function check_prefix($file)
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
