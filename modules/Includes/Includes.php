<?php

/**
 * Includes
 *
 * This allows you to include files before processing for compiling
 * into a single file and later cached. 
 *
 * @author Anthony Short
 */
class Includes extends Scaffold_Module
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
	public static function import_process($css)
	{
		# Add the original file to the loaded array
		self::$loaded[] = $css->path;
		
		# Find all the @server imports
		$css->string = self::server_import($css->string,dirname($css->path));

		return $css;
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
				Scaffold::log('Invalid @include file - ' . $include,1);
				self::server_import($css,$base);
			}

			# Find the file
			if($path = Scaffold::find_file($include,$base))
			{
				# Make sure it hasn't already been included	
				if(!in_array($path, self::$loaded))
				{
					self::$loaded[] = $path;
					
					$contents = file_get_contents($path);
					$contents = Scaffold::remove_inline_comments($contents);
					
					# Check the file again for more imports
					$contents = self::server_import($contents, realpath(dirname($path)) . '/');
					
					$css = str_replace($matches[0][0], $contents, $css);
				}
	
				# It's already been included, we don't need to import it again
				else
				{
					$css = str_replace($matches[0][0], '', $css);
				}
				
			}
			else
			{
				Scaffold::error('Can\'t find the @include file - <strong>' . $unique[0] . '</strong>');
			}
			
			$css = self::server_import($css,$base);

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