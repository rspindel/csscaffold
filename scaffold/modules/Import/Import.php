<?php defined('SYSPATH') OR die('No direct access allowed.');

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
	 * Stores which files have already been included
	 *
	 * @var array
	 */
	private static $loaded = array();
	
	/**
	 * This function occurs before everything else
	 *
	 * @author Anthony Short
	 * @param $css
	 */
	public static function parse()
	{
		# Find all the @server imports
		CSS::$css = self::server_import(CSS::$css);
	}
	
	/**
	 * Imports css via @import statements
	 * 
	 * @author Anthony Short
	 * @param $css
	 */
	public static function server_import($css, $previous = "")
	{		
		if (preg_match_all('/\@include\s+(?:\'|\")([^\'\"]+)(?:\'|\")\;/', $css, $matches))
		{
			$unique = array_unique($matches[1]);
			$include =  unquote($unique[0]);
			$include_info = pathinfo($include);

			# This is the path of the css file that requested the 
			$requested_dir = pathinfo(CSScaffold::config('core.request.path'), PATHINFO_DIRNAME);
			
			# Resolve the CSS-style path
			$path = CSS::resolve_path($include);
			
			# Make sure recursion isn't happening
			if($include == $previous)
				throw new Scaffold_Exception("Import.recursion", $include);
			
			# Make sure it's a CSS file
			if(!is_css($include))
				throw new Scaffold_Exception("Import.not_css", $include);
			
			# Make sure the file exists	
			if(!file_exists($path))
				throw new Scaffold_Exception("Import.doesnt_exist", $include);
			
			# Make sure it hasn't already been included	
			if(!in_array($path, self::$loaded))
			{
				self::$loaded[] = $path;
				$css = str_replace($matches[0][0], file_get_contents($path), $css);
			}
			
			$css = self::server_import($css, $include);
		}
		
		return $css;
	}
}