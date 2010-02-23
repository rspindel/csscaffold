<?php

/**
 * Constants
 *
 * Allows you to use constants within your css by defining them
 * within @constants and then using a property list.
 *
 * You can set CSScaffold constants using XML. This allows you to create
 * constants using a CMS or by any other means to tie it in with your CSS.
 *
 * XML must be in this format:
 
 	<?xml version="1.0" ?>
 	<constants>
 	
 		<constant>
 			<name>Foo</name>
 			<value>Bar</value>
 		</constant>
 	
 	</constants>
 *
 * By default, it requires a constants.xml file in the root of the CSS directory.
 * You can change this in the plugins config.
 *
 * @author Anthony Short
 */
class Constants extends Scaffold_Module
{
	/**
	 * Stores all of the constants for the app
	 *
	 * @var array
	 */
	public $constants = array();

	/**
	 * The pre-processing function occurs after the importing,
	 * but before any real processing. This is usually the stage
	 * where we set variables and the like, getting the css ready
	 * for processing.
	 *
	 * @author Anthony Short
	 * @param $css
	 */
	public function pre_process($css)
	{
		# Global Constants
		$this->set_global_constants();
		
		# XML Constants
		$this->load_xml_constants( $this->config['xml_path'] );

		# If there are some constants, let do it.
		if( $found = $css->find_at_group('constants') )
		{
			# Create our template style constants
			foreach($found['values'] as $key => $value)
			{				
				# Check if this contains other constants
				$value = $this->replace($value);
				
				# Set it
				$this->set($key, $value);
			}	
		}
		
		return $css;
	}
	
	/**
	 * Replaces the constants
	 *
	 * @return void
	 */
	public function process($css)
	{
		$css->string = $this->replace($css->string);
	}

	/**
	 * Sets the global constants
	 *
	 * @return void
	 */
	private function set_global_constants()
	{
		foreach($this->config['global'] as $key => $value)
		{
			$this->set($key,$value);
		}
	}

	/**
	 * Sets constants
	 *
	 * @author Anthony Short
	 * @param $key
	 * @param $value
	 * @return null
	 */
	public function set($key, $value = "")
	{
		# So we can pass through a whole array
		# and set them all at once
		if(is_array($key))
		{
			foreach($key as $name => $val)
			{
				$this->constants[$name] = $val;
			}
		}
		else
		{
			$this->constants[$key] = $value;
		}	
	}
	
	/**
	 * Unsets a constant
	 *
	 * @param $key
	 * @return void
	 */
	public function remove($key)
	{
		unset($this->constants[$key]);
	}
	
	/**
	 * Returns the constant value
	 *
	 * @author Anthony Short
	 * @param $key
	 * @return string
	 */
	public function get($key)
	{
		return $this->$constants[$key];
	}
	
	/**
	 * Replaces all of the constants in a CSS string
	 * with the constants defined in the member variable $constants
	 * using PHP's interpolation.
	 */
	public function replace($css)
	{
		# Pull the constants into the local scope as variables
		extract($this->constants, EXTR_SKIP);
		
		if( $found = preg_match_all('/\{?\$([A-Za-z0-9_-]+)\}?/', $css, $found) )
		{
			# Remove unset variables from the string, so errors aren't thrown
			foreach(array_unique($found[1]) as $value)
			{
				if(!isset($$value))
				{
					Scaffold::error('Missing constant - ' . $value);
				}
			}
		}

		$css = stripslashes( eval('return "' . addslashes($css) . '";') );
		
		# Replace the variables within the string like a normal PHP string
		return $css;
	}
	
	/**
	 * Loads constants from an XML file
	 *
	 * @param $param
	 * @return return type
	 */
	private function load_xml_constants($file)
	{
		if($file === false)
			return;

		# If the xml file doesn't exist
		if(!file_exists($file))
		{
			return Scaffold::$log->add("Missing constants XML file. The file ($file) doesn't exist.",1);
		}
		
		# Load the xml
		$xml = simplexml_load_file($file);
		
		# Loop through them and set them as constants
		foreach($xml->constant as $key => $value)
		{
			$this->set((string)$value->name, (string)$value->value);
		}
	}
		
}