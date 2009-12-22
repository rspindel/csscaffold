<?php

/**
 * Import
 *
 * This allows you to import files before processing for compiling
 * into a single file and later cached. This is done via @import ''
 *
 * @author Anthony Short
 * @dependencies None
 **/
class Import extends Scaffold_Module
{

	/**
	 * Stores which files have already been included
	 *
	 * @var array
	 */
	public static $loaded = array();

	/**
	 * This function occurs before everything else
	 *
	 * @author Anthony Short
	 * @param $css
	 */
	public static function parse($css)
	{
		# Add the original file to the loaded array
		self::$loaded[] = CSScaffold::config('current.file');
		
		# Find all the @server imports
		return self::server_import($css, CSScaffold::config('current.path') );
	}
	
	/**
	 * Imports css via @import statements
	 * 
	 * @author Anthony Short
	 * @param $css
	 */
	public static function server_import($css,$base)
	{					
		if(preg_match_all('/\@include\s+(?:\'|\")([^\'\"]+)(?:\'|\")\;/', $css, $matches))
		{
			$unique = array_unique($matches[1]);
			$include = str_replace("\\", "/", Scaffold_Utils::unquote($unique[0]));
			
			# If they haven't supplied an extension, we'll assume its a css file
			if(pathinfo($include, PATHINFO_EXTENSION) == "")
				$include .= '.css';
			
			# Make sure it's a CSS file
			if(pathinfo($include, PATHINFO_EXTENSION) != 'css')
			{
				$css = str_replace($matches[0][0], '', $css);
				self::$errors['Invalid'][] = $include;
				self::server_import($css,$base);
			}

			# Find the file
			if($include = CSScaffold::find_file($include,$base))
			{		
				# Make sure it hasn't already been included	
				if(!in_array($include, self::$loaded))
				{
					self::$loaded[] = $include;
					$css = str_replace($matches[0][0], file_get_contents($include), $css);
				}
	
				# It's already been included, we don't need to import it again
				else
				{
					$css = str_replace($matches[0][0], '', $css);
				}
				
				# Removes any commented out @imports
				$css = Scaffold_CSS::remove_comments($css);
	
				# Check the file again for more imports
				$css = self::server_import($css, realpath(dirname($include)) . '/');		
			}
			else
			{
				self::$errors['Missing'][] = $include;
			}
		}

		return $css;
	}
	
	/**
	 * Resets the loaded array
	 *
	 * @author Anthony Short
	 * @return return type
	 */
	public static function reset()
	{
		self::$loaded = array();
	}
}