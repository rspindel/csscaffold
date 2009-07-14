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
	function import_process($css)
	{
		
		# Find all the @server imports
		$css = $this->server_import($css);
			
		# Append any css in the plugins folder
		# $css = $this->add_plugins($css);

		return $css;
	}
	
	/**
	 * Imports css via @import statements
	 * 
	 * @author Anthony Short
	 * @param $css
	 */
	function server_import($css, $previous = "")
	{		
		if (preg_match_all('/\@import\s+(?:\'|\")([^\'\"]+)(?:\'|\")\;/', $css, $matches))
		{
			$unique = array_unique($matches[1]);
			$include = $unique[0];
			
			if($include == $previous)
			{
				stop("Error: Recursion in imports. You are importing the css file into itself");
			}
						
			if(is_css($include))
			{
				$css = str_replace($matches[0][0], file_get_contents(join_path(DOCROOT,$include)), $css);
			}
			
			$css = $this->server_import($css, $include);
		}
		
		return $css;
	}
	
	/**
	 * Automatically appends files in a folder to the css
	 *
	 * @author Anthony Short
	 * @param $css
	 */
	function add_plugins($css)
	{		
		foreach(scandir(CSSPATH . 'plugins') as $file)
		{
			if (!is_css($file)) { continue; }
			
			// Add it to our css
			$css .= file_get_contents($file);
		}
		
		return $css;
	}

}