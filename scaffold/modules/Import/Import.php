<?php defined('BASEPATH') OR die('No direct access allowed.');

/**
 * Import
 *
 * This allows you to import files before processing for compiling
 * into a single file and later cached. This is done via @import ''
 *
 * @author Anthony Short
 * @dependencies None
 **/
class Import extends Plugins
{
	
	/**
	 * This function occurs before everything else
	 *
	 * @author Anthony Short
	 * @param $css
	 */
	function import_process()
	{		
		# Find all the @server imports
		CSS::$css = $this->server_import(CSS::$css);
	}
	
	/**
	 * Imports css via @import statements
	 * 
	 * @author Anthony Short
	 * @param $css
	 */
	function server_import($css, $previous = "")
	{		
		if (preg_match_all('/\@include\s+(?:\'|\")([^\'\"]+)(?:\'|\")\;/', $css, $matches))
		{
			$unique = array_unique($matches[1]);
			$include = $unique[0];
			
			if($include == $previous)
			{
				stop("Error: Recursion in imports. You are importing the css file into itself");
			}
			
			# This is the path of the css file that requested the 
			$requested_dir = pathinfo(Config::get('server_path'), PATHINFO_DIRNAME);
			
			# Get the file path to the include
			$path = find_absolute_path($include);
						
			if(is_css($include) AND file_exists($path))
			{
				$css = str_replace($matches[0][0], file_get_contents($path), $css);
			}
			else
			{
				stop("Error: Import > File is not a css file, or cannot be found - " . $path);
			}
			
			$css = $this->server_import($css, $include);
		}
		
		return $css;
	}
}