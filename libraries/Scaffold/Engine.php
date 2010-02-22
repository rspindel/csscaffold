<?php

/**
 * Scaffold_Engine
 *
 * Handles the processing of a CSS file
 * 
 * @author Anthony Short
 */
class Scaffold_Engine
{
	/**
	 * Parse the CSS file. This takes an array of files, options and configs
	 * and parses the CSS, outputing the processed CSS string.
	 *
	 * @param string Path to the file to parse
	 * @param string The file to output the parsed CSS
	 * @return string The processed css file as a string
	 */
	public function parse_file($file)
	{
		/**
		 * Make sure this file is allowed
		 */
		if(substr($file, 0, 4) == "http" OR substr($file, -4, 4) != ".css")
		{
			Scaffold::error("Scaffold cannot the requested file - $file");
		}
		
		/**
		 * Find the file on the server
		 */
		$file = Scaffold::find_file($file, false, true);
		
		/**
		 * The file to output
		 */
		$output = Scaffold::$output_path . basename($file);

		/** 
		 * When the output file expirex
		 */
		$expires = (Scaffold::$production) ? 0 : Scaffold::$lifetime + filemtime($output);
		
		/**
		 * When the output file was last modified
		 */
		$modified = (Scaffold::$production) ? filemtime($output) : 0;
		
		/**
		 * In development mode, it will always reparse the file.
		 */
		if(!file_exists($output) OR time() >= $expires OR $modified < filemtime($file))
		{				
			/**
			 * This allows Scaffold to find files in the directory of the CSS file
			 */
			Scaffold::add_include_path($file);
			
			/**
			 * Load the original CSS file
			 */
			$css = file_get_contents($file);

			/**
			 * This will return the parsed CSS
			 */
			$css = $this->process( new Scaffold_CSS($css), dirname($file) );
			
			/**
			 * Remove the include path
			 */
			Scaffold::remove_include_path($file);
			
			/**
			 * Write it to the output directory
			 */
			file_put_contents($output, $css);
			
			/**
			 * Set its permissions
			 */
			chmod($output, 0777);
			touch($output, time());
		}

		return file_get_contents($output);
	}
	
	/**
	 * Parses a single string of CSS through the parser
	 *
	 * @author your name
	 * @param $css
	 * @return string
	 */
	public function parse_string($css)
	{
		return $this->process( new Scaffold_CSS($css) );
	}

	/**
	 * Parses the single CSS file
	 *
	 * @param $css		An instance of the Scaffold_CSS class
	 * @param $base		The base path to use for paths. Usually the directory of the CSS file
	 * @return $css 	string
	 */
	public function process( Scaffold_CSS $css, $base = false )
	{
		/**
		 * The base directory to use for any directory handling
		 * in the css.
		 */
		if($base !== false)
			$css->directory($base);

		/**
		 * Import Process Hook
		 * This hook is for doing any type of importing/including in the CSS
		 */
		Scaffold::hook('import_process',$css);
		
		/**
		 * Pre-process Hook
		 * There shouldn't be any heavy processing of the string here. Just pulling
		 * out @ rules, constants and other bits and pieces.
		 */
		Scaffold::hook('pre_process',$css);
			
		/**
		 * Process Hook
		 * The main process. None of the processes should conflict in any of the modules
		 */
		Scaffold::hook('process',$css);
		
		/**
		 * Replace custom functions
		 */
		foreach(Scaffold::$extensions['functions'] as $name => $values)
		{
			if($found = $css->find_functions($name))
			{
				// Make the list unique or not
				$originals = ($values['unique'] === false) ? array_unique($found[0]) : $found[0];
	
				// Loop through each found instance
				foreach($originals as $key => $value)
				{
					$result = call_user_func_array($values['callback'],explode(',',$found[2][$key]));
	
					// Run the user callback										
					if($result === false)
					{
						Scaffold::error('Invalid Custom Function Syntax - <strong>' . $originals[$key] . '</strong>');
					}
					
					// Just replace the first match if they are unique
					elseif($values['unique'] === true)
					{
						$pos = strpos(Scaffold::$css->string,$originals[$key]);
	
						if($pos !== false)
						{
						    Scaffold::$css->string = substr_replace(Scaffold::$css->string,$result,$pos,strlen($originals[$key]));
						}
					}
					else
					{
						Scaffold::$css->string = str_replace($originals[$key],$result,Scaffold::$css->string);
					}
				}
			}
		}
		
		/**
		 * Replace custom properties
		 */
		foreach(Scaffold::$extensions['properties'] as $name => $values)
		{
			if($found = $css->find_property($name))
			{
				$originals = array_unique($found[0]);
	
				foreach($originals as $key => $value)
				{
					$result = call_user_func($values['callback'],$found[2][$key]);
	
					if($result === false)
					{
						Scaffold::error('Invalid Custom Property Syntax - <strong>' . $originals[$key] . '</strong>');
					}
					
					Scaffold::$css->string = str_replace($originals[$key],$result,Scaffold::$css->string);
				}
			}
		}

		/**
		 * Post-process Hook
		 * After any non-standard CSS has been processed and removed. This is where
		 * the nested selectors are parsed. It's not perfectly standard CSS yet, but
		 * there shouldn't be an Scaffold syntax left at all.
		 */
		Scaffold::hook('post_process',$css);

		/**
		 * Formatting Hook
		 * Stylise the string, rewriting urls and other parts of the string. No heavy processing.
		 */
		Scaffold::hook('formatting_process',$css);
				
		/**
		 * Output Hook
		 * Hook that is only run in development mode. It's used for creating extra
		 * assets or changing what is displayed to the browser.
		 */
		if(Scaffold::$production === false) Scaffold::hook('output',$css);

		return $css->string;
	}

}