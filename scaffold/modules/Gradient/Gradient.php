<?php

/**
 * Gradient class
 *
 * @author Paul Clark
 * @version 1.0
 * @dependencies gradientgd.class.php
 */
class Gradient
{
	/**
	 * List of created gradients and their locations
	 *
	 * @var array
	 */
	public static $gradients = array();

	public static function create_gradient($direction, $size, $from, $to, $stops = false )
	{
		if (!class_exists('GradientGD'))
			include(dirname(__FILE__).'/libraries/gradientgd.php');
		
		$file = Scaffold::$config['cache'] . "gradients/{$direction}_{$size}_{$from}_{$to}.png";

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
			Scaffold_Cache::create('gradients');
			$gradient = new GradientGD($width,$height,$direction,$from,$to,$stops);
			$gradient->save($file);
		}
		
		self::$gradients[] = array
		(
			$direction,
			$size,
			$from,
			$to,
			$file
		);
		
		$properties = "
			background-position: top left;
		    background-repeat: repeat-$repeat;
		    background-image: url(".Scaffold::url_path($file).");
		";
		
		return $properties;

	}
}