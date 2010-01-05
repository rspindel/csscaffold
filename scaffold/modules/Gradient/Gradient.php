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
	public static function create_gradient($direction, $size, $from, $to, $stops = false )
	{
		if (!class_exists('GradientGD'))
			include(dirname(__FILE__).'/libraries/gradientgd.php');
			
		$file = Scaffold::$cache_path . 'gradients/' . md5( serialize( func_get_args() )) . '.png';

		if($direction == 'horizontal')
		{
			$height = 50;
			$width = $size;
			$repeat = 'y';
		}
		else
		{
			$height = $size;
			$width = 50;
			$repeat = 'x';
		}

		if(!file_exists($file)) 
		{
			Scaffold::cache_create('gradients');
			$gradient = new GradientGD($width,$height,$direction,$from,$to,$stops);
			$gradient->save($file);
		}
		
		$properties = "
			background-position: top left;
		    background-repeat: repeat-$repeat;
		    background-image: url(".Scaffold::url_path($file).");
		";
		
		return $properties;

	}
}