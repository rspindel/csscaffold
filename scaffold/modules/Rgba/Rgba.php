<?php

/**
 * Rgba class
 *
 * @author Olivier Gorzaka
 * @version 1.0
 * @dependencies rgbagd.php
 */
class Rgba
{
	/**
	 * List of created rgba background and their locations
	 *
	 * @var array
	 */
	public static $rgba = array();

	public static function create_rgba($r,$g,$b,$a)
	{
		if (!class_exists('RgbaGd'))
			include(dirname(__FILE__).'/libraries/rgbagd.php');
		
		$file = "color_r{$r}_g{$g}_b{$b}_a{$a}.png";

		$alpha = intval(127 - 127 * $a);
		  
		if(!Scaffold_Cache::exists('rgba/'.$file)) 
		{
			Scaffold_Cache::create('rgba');
			$file = Scaffold_Cache::find('rgba') . '/' . $file;
			$rgba = new RgbaGd($r,$g,$b,$alpha);
			$rgba->save($file);
		}
		$file = Scaffold_Cache::find('rgba') . '/' . $file;

		self::$rgba[] = array
		(
      $r,
      $g,
      $b,
      $alpha
		);

		$properties = "
			background-position: top left;
		  background-repeat: repeat;
		  background-image: url(".Scaffold::url_path($file).") !important;
		  background-image:none;
		  background: rgba($r,$g,$b,$a);
		  filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src=".Scaffold::url_path($file).", sizingMethod=scale);
		";
		
		return $properties;

	}
}