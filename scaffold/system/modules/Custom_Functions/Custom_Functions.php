<?php

/**
 * Custom_Functions
 *
 * Allows you to drop new functions into a folder and 
 * have access to that function from within your CSS.
 * 
 * @author Anthony Short
 */
class Custom_Functions extends Scaffold_Module
{	
	/**
	 * List of available functions
	 *
	 * @var array
	 */
	public static $functions = array();
	
	/**
	 * Outputs some logging information
	 *
	 * @author Anthony Short
	 * @return void
	 */
	public static function log()
	{
		FB::group('Invalid');
		foreach(self::$invalid as $name => $value)
		{
			Scaffold_Logger::log($name, $value, 1);
		}
		FB::groupEnd();
	}

	/**
	 * Find and load all of the functions
	 *
	 * @author Anthony Short
	 * @param $css
	*/
	public static function process($css)
	{
		$functions = CSScaffold::list_files('extensions/functions');
		
		foreach ($functions as $path)
		{
			require_once $path;
			
			$function_name = pathinfo($path, PATHINFO_FILENAME);
			self::$functions[] = $function_name;
			
			if($found = Scaffold_CSS::find_functions($function_name, $css))
			{				
				$originals 		= array_unique($found[0]);
				$params 		= array_unique($found[2]);
				
				foreach($originals as $key => $value)
				{					
					$result = call_user_func_array('Scaffold_'.$function_name, explode(',',$params[$key]) );
										
					if($result)
					{
						$css = str_replace($originals[$key],$result,$css);
					}
					else
					{
						self::$errors['Invalid'][$function_name][] = $originals[$key]; 
					}
				}
			}
		}

		return $css;
	}
}