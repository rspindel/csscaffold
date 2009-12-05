<?php

/**
 * Gradient class
 *
 * @author Paul Clark
 * @version 1.0
 * @dependencies gradientgd.class.php
 **/
class Gradient extends Scaffold_Module
{
	public static function post_process($css)
	{		
		
		if($found = CSS::find_properties_with_value('gradient')) 
		{
			self::cache_create('gradients');

			// Get our Gradient Generation Class
			if (!class_exists('GradientGD')) {
				include(dirname(__FILE__).'/libraries/gradientgd.class.php');
			}

			foreach ($found[3] as $key => $value) // found[3] = CSS value without Regexp applied
			{
				$vals = explode(',', $value);
				
				// Extract colors and dimensions from the css values
				$val_count = count($vals);
				for ($i=0;$i<$val_count;$i++) { // Keeps crashing if I use a foreach here. Can't figure it out.
				
					$vals[$i] = trim($vals[$i]);
					
					// if this value is a hex color
					if ( substr($vals[$i], 0, 1) == '#' ) { 
						// Remove hash, invalid hex characters
						$color = preg_replace('/[^a-fA-F0-9]/', '', $vals[$i]);
						#FB::log($color);
						// Account for 3-character shorthand, ie: #000
						if (strlen($color) == 3) {	
							$color .= $color;
						}
						
						// Add it to a color array if it's valid hex
						if (strlen($color) == 6) {
							$colors[] = (string)$color;
						}else {
							#FB::error($color, 'Invalid Hex code in ('.$value.')');
						}
					}else {
						// Doesn't start with #, so must be either width, height, or orientation (left-to-right?)
						$size[] = $vals[$i];
					}	
				}
				
				$filename = implode('-', $colors).'-'.implode('x', $size).'.jpg';
				if (strlen($gcache.'/'.$filename) > 255) {
					// If you hit this, you are completely bonkers
					$cachefile = $gcache.'/'.md5($filename).'.jpg';
				}else {
					$cachefile = $gcache.'/'.$filename;
				}
				$cacheurl = str_replace(DOCROOT, '/', $cachefile);

				$w = $size[0];
				$h = $size[1];
				
				if (!empty($size[2]) && ($size[2] == 'l2r' || $size[2] == 'true')) {
					$left2right = true;
					$repeaet = 'repeat-y';
				}else {
					// if our final variable is set to 't2b', false, empty, or anything else
					// we'll default to top-to-bottom
					$left2right = false;
					$repeat = 'repeat-x';
				}
				
				// We need at least as many pixels as we have colors b/c of limitations in the gradient class
				$color_count = count($colors);
				if ($left2right) {
					if ($w < $color_count) $w = $color_count;
				}else { // top2bottom
					if ($h < $color_count) $h = $color_count;
				}
				
				// If the file doesn't exist, make the gradient. Otherwise, just use the cached file
				if(!file_exists($cachefile) ) {
					
					$gradient = new GradientGD($w, $h);
					$gradient->set_option('imagetype', 'jpg');
					$gradient->set_option('colorhandler', 'HEX');	// convert2rgb() had to be patched for HEX to work. Shouldn't return an associative array
					$gradient->set_option('reverse', $left2right);
					$gradient->set_option('saveimage', $cachefile);
					
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
					background-color: #{$colors[0]};
				    background-image: url($cacheurl);
				";
				
				// Clear vars for next gradient in loop
				unset($size,$colors, $middle, $gradient);

				CSS::replace($found[2][$key], $properties);
			}
			
			# Remove any leftovers
			CSS::replace($found[1], '');
		}
	}
}