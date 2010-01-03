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
	 * Find and load all of the functions
	 *
	 * @author Anthony Short
	 * @param $css
	*/
	public static function post_process($css)
	{
		$functions = CSScaffold::list_files('extensions/functions');
		
		foreach ($functions as $path)
		{
			require_once $path;
			
			$function_name = pathinfo($path, PATHINFO_FILENAME);
			self::$functions[] = $function_name;

			if($found = Scaffold_CSS::find_functions($function_name,$css))
			{			
				$originals = array_unique($found[0]);

				foreach($originals as $key => $value)
				{					
					$result = call_user_func_array('Scaffold_'.$function_name, explode(',',$found[2][$key]) );
										
					if($result)
					{
						$css = str_replace($originals[$key],$result,$css);
					}
					else
					{
						CSScaffold::error('Invalid function - <strong>' . $originals[$key] . '</strong>');
					}
				}
			}
		}

		return $css;
	}
}