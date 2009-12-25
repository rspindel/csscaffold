<?php

/**
 * Gradient class
 *
 * @author Paul Clark
 * @version 1.0
 * @dependencies gradientgd.class.php
 */
class Gradient extends Scaffold_Module
{
	public static function create_gradient($type, $start, $end, $from, $to, $stops = array())
	{
		CSScaffold::cache_create('gradients');

		// Get our Gradient Generation Class
		if (!class_exists('GradientGD'))
			include(dirname(__FILE__).'/libraries/gradientgd.class.php');
		
		$to 	= trim($vals[0]);
		$from 	= trim($vals[1]);
		$width 	= str_replace('px','',trim($vals[2]));
		$height = str_replace('px','',trim($vals[3]));
		$angle 	= trim($vals[4]);
		
		foreach(array($to,$from) as $color_key => $color_value)
		{
			// Remove hash, invalid hex characters
			$color = preg_replace('/[^a-fA-F0-9]/', '', $color_value);
			
			// Account for 3-character shorthand, ie: #000
			if (strlen($color) == 3) 
			{	
				$color .= $color;
			}
			
			// Add it to a color array if it's valid hex
			if (strlen($color) == 6) 
			{
				$colors[] = (string)$color;
			}
			else 
			{
				CSScaffold::log('Invalid Hex code in ('.$value.')',1);
			}
		}
		
		$filename = md5( array( func_get_args() ) ) . '.png';

		if (strlen($filename) > 255) 
		{
			// If you hit this, you are completely bonkers
			$filename = md5($filename).'.png';
		}
		
		if (!empty($angle) && ($angle == 'l2r' || $angle == 'true')) 
		{
			$left2right = true;
			$repeaet = 'repeat-y';
		}
		else 
		{
			// if our final variable is set to 't2b', false, empty, or anything else we'll default to top-to-bottom
			$left2right = false;
			$repeat = 'repeat-x';
		}
		
		// We need at least as many pixels as we have colors b/c of limitations in the gradient class
		$color_count = count($colors);
		if ($left2right) {
			if ($width < $color_count) $width = $color_count;
		}else { // top2bottom
			if ($height < $color_count) $height = $color_count;
		}
		
		$cache = CSScaffold::$cache_path . 'gradients/' . $filename;
		
		// If the file doesn't exist, make the gradient. Otherwise, just use the cached file
		if(!file_exists($cache) ) 
		{
			
			$gradient = new GradientGD($width, $height);
			$gradient->set_option('imagetype', 'jpg');
			$gradient->set_option('colorhandler', 'HEX');	// convert2rgb() had to be patched for HEX to work. Shouldn't return an associative array
			$gradient->set_option('reverse', $left2right);
			$gradient->set_option('saveimage', $cache);
			
			for ($i=0;$i<$color_count;$i++) {
				if ($i == 0) {
					$gradient->set_color($colors[$i], 'start');
				}else if ($i == $color_count-1) {
					$gradient->set_color($colors[$i], 'end');
				}else {
					$middle[] = $colors[$i];
				}
			}
			
			if (!empty($middle)) {
				$gradient->set_color($middle, 'middle');
			}

			$gradient->generate();
		}
		
		$properties = "
			background-position: top left;
		    background-repeat: $repeat;
			background-color: #".$colors[count($colors) - 1].";
		    background-image: url(".Scaffold_Utils::urlpath($cache).");
		";
		
		return $properties;

	}
}