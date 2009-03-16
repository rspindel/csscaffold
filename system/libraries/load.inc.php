<?php if (!defined('CSS_CACHEER')) { header('Location:/'); }

/**
 * Load a file
 * @param string Path to the file
 */
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

/**
 * Load every file in a directory
 * @param string Path to the directory
 */
function load_dir($directory)
{	
	$loaded = "";
	
	if ($dir_handle = opendir($directory)) 
	{
		while (($file = readdir($dir_handle)) !== false) 
		{
			if (substr($file, 0, 1) == '.' || substr($file, 0, 1) == '-')
			{ 
				continue; 
			}
			
			$loaded .= file_get_contents($directory . "/" .$file);
		}
		
		closedir($dir_handle);
	}
	return $loaded;
}

/**
 * Returns every file in a directory in an array
 * @param string Path to the directory
 */
function read_dir($directory)
{
	if(is_dir($directory))
	{
		if ($dir_handle = opendir($directory)) 
		{
			while (($file = readdir($dir_handle)) !== false) 
			{
				if (substr($file, 0, 1) == '.' || substr($file, 0, 1) == '-')
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
		error("Cannot read directory: ". $directory);
		exit;
	}
	
	return $files;
}


/**
 * Checks filetype
 * @param array of filetype
 * @param string filename to check
 */
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
	