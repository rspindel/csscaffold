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
	 * Array of objects to use as processing hooks
	 *
	 * @var array
	 */
	private $hooks;

	/**
	 * Creates the hooking object and sets up the engine
	 *
	 * @param $hooks	Array	An array of objects which will have methods called
	 */
	public function __construct()
	{
		$this->hooks = Scaffold::modules();
	}

	/**
	 * Parse the CSS file. This takes an array of files, options and configs
	 * and parses the CSS, outputing the processed CSS string.
	 *
	 * @param 	string	Path to the file to parse
	 * @param 	string	(Optional) The target directory to save the combined file. MUST be an absolute URL. 
	 * @return	string	The processed css file as a string
	 */
	public function parse_file($file,$target = false)
	{		
		# Make sure this file is allowed
		if(substr($file, 0, 4) == "http" OR substr($file, -4, 4) != ".css")
		{
			Scaffold::error("Scaffold cannot parse the requested file - $file");
		}
		
		# Find the file on the server
		$file = Scaffold::find_file($file, false, true);
		
		# The file to output
		if($target === false)
		{
			$output = Scaffold::$output_path . basename($file);
		}
		else
		{
			$output = Scaffold::root() . $target;
		}

		# When the output file expires
		$expires = (Scaffold::$production) ? 0 : Scaffold::$lifetime + filemtime($output);
		
		# When the output file was last modified
		$modified = (Scaffold::$production) ? filemtime($output) : 0;

		if(!file_exists($output) OR time() >= $expires OR $modified < filemtime($file))
		{				
			# Allows Scaffold to find files in the directory of the CSS file
			Scaffold::add_include_path($file);
			
			# Load the original CSS file
			$css = file_get_contents($file);

			# This will return the parsed CSS
			$css = $this->compile( new Scaffold_CSS($css), $file );
			
			# Remove the include path
			Scaffold::remove_include_path($file);
			
			# Write it to the output directory
			file_put_contents($output, $css);
			chmod($output, 0777);
			touch($output, time());
		}

		return file_get_contents($output);
	}
	
	/**
	 * Parses a single string of CSS through the compiler
	 *
	 * @param $css
	 * @return string
	 */
	public function parse_string($css)
	{
		return $this->compile( new Scaffold_CSS($css) );
	}
	
	/**
	 * Parses an array of files and outputs the combined string
	 *
	 * @author your name
	 * @param $files
	 * @return string
	 */
	public function parse_array(array $files, $base = false)
	{
		# This combined CSS string
		$css = '';
		
		foreach($files as $file)
		{
			if($base !== false)
			{
				$file = $base . DIRECTORY_SEPARATOR . $file;
			}
			
			$css .= $this->parse_file($file);
		}
		
		return $css;
	}

	/**
	 * Parses the single CSS file
	 *
	 * @param $css		An instance of the Scaffold_CSS class
	 * @param $base		The base path to use for paths. Usually the directory of the CSS file
	 * @return $css 	string
	 */
	private function compile( Scaffold_CSS $css, $base = false )
	{
		/**
		 * The file the CSS is from. Used for any path functions dealing with the CSS
		 */
		if($base !== false)
		{
			$css->directory($base);
		}

		/**
		 * Import Process Hook
		 * This hook is for doing any type of importing/including in the CSS
		 */
		foreach($this->hooks as $hook)
		{
			$css = $hook->import($css);
		}
		
		/**
		 * Pre-process Hook
		 * There shouldn't be any heavy processing of the string here. Just pulling
		 * out @ rules, constants and other bits and pieces.
		 */
		foreach($this->hooks as $hook)
		{
			$css = $hook->pre_process($css);
		}
			
		/**
		 * Process Hook
		 * The main process. None of the processes should conflict in any of the modules
		 */
		foreach($this->hooks as $module)
		{
			$css = $hook->process($css);
		}
		
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
						    $css->string = substr_replace($css->string,$result,$pos,strlen($originals[$key]));
						}
					}
					else
					{
						$css->string = str_replace($originals[$key],$result,$css);
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
					
					$css->string = str_replace($originals[$key],$result,$css->string);
				}
			}
		}

		/**
		 * Post-process Hook
		 * After any non-standard CSS has been processed and removed. This is where
		 * the nested selectors are parsed. It's not perfectly standard CSS yet, but
		 * there shouldn't be an Scaffold syntax left at all.
		 */
		foreach($this->hooks as $hook)
		{
			$css = $hook->post_process($css);
		}

		/**
		 * Formatting Hook
		 * Stylise the string, rewriting urls and other parts of the string. No heavy processing.
		 */
		foreach($this->hooks as $hook)
		{
			$css = $hook->format($css);
		}
				
		/**
		 * Output Hook
		 * Hook that is only run in development mode. It's used for creating extra
		 * assets or changing what is displayed to the browser.
		 */
		if(Scaffold::$production === false)
		{
			foreach($this->hooks as $hook)
			{
				$css = $hook->output($css);
			}
		}

		return $css->string;
	}

}