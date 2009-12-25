<?php

/**
 * Validate
 **/
class Validate extends Scaffold_Module
{

	/**
	 * Validation Errors
	 *
	 * @var array
	 */
	public static $errors;

	/**
	 * Outputs some logging information
	 *
	 * @author Anthony Short
	 * @return void
	 */
	public static function log()
	{
		$table = array();
		$table[] = array('Line', 'Description', 'Near');

		foreach(self::$errors as $key => $value)
		{
			$table[] = array($value['line'], $value['message'], $value['near'] );
		}
		
		FB::table('Validation Errors', $table);
	}

	public static function output($css)
	{
		if( CSScaffold::option('validate') )
		{					
			# Get the validator options from the config
			$validator_options = CSScaffold::config('Validate');
			
			# Add our options
			$validator_options['text'] = $css;
			$validator_options['output'] = 'soap12';
			
			# Encode them
			$validator_options = http_build_query($validator_options);
			
			$url = "http://jigsaw.w3.org/css-validator/validator?$validator_options";
			
			# The Curl options
			$options = array
			(
				CURLOPT_URL 			=> $url,
				CURLOPT_RETURNTRANSFER 	=> 1,
			);
			
			# Start CURL
			$handle = curl_init();
			curl_setopt_array($handle, $options);
			$buffer = curl_exec($handle);
			curl_close($handle);
			
			# If something was returned
			if (!empty($buffer))
			{
				# Simplexml doesn't like colons
				$buffer = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $buffer);
				
				# Let it be xml!
			    $results = simplexml_load_string($buffer);
			    $is_valid = (string)$results->envBody->mcssvalidationresponse->mvalidity;
				
				# Oh noes! Display the errors
			    if($is_valid == "false")
			    {
			    	$errors = $results->envBody->mcssvalidationresponse->mresult->merrors;
			    	
			    	foreach($errors->merrorlist->merror as $key => $error)
			    	{
			    		$line = (string)$error->mline;
			    		$message = trim((string)$error->mmessage);
			    		$near = (string)$error->mcontext;
			    		
			    		self::$errors[] = array('line' => $line, 'near' => $near, 'message' => $near);
			    		
			    		CSScaffold::log("Validation Error on line {$line} near {$near} => {$message}",2);
			    	}
			    }
			}
		}
	
		return $css;
	}
} 
