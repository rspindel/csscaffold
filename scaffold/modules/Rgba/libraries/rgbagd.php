<?php
class RgbaGd
{
	// $image = new RgbaGd($r,$g,$b,$a)
	
	// Constructor. Creates returns an image
	function __construct($r,$g,$b,$a) 
	{
		$this->red = $r;
		$this->green = $g;
		$this->blue = $b;
		$this->alpha = $a;

		// Attempt to create a blank image in true colors
		if(function_exists('imagecreatetruecolor')) 
		{
			$this->image = imagecreatetruecolor(10, 10);
			// Alpha process to give transparency to the image
    	imagealphablending($this->image, false);
    	imagesavealpha($this->image, true);
    	
    	// Allocate the requested color
    	$this->color = imagecolorallocatealpha($this->image, $this->red, $this->green, $this->blue, $this->alpha);

    	// Fill the image
    	imagefill($this->image, 0, 0, $this->color);
		}
		else 
		{
			return false;
		}
		
		return $this->image;
	}
	
	/**
	 * Saves the image to a file
	 *
	 * @param $file
	 * @return void
	 */
	function save($file)
	{
		imagepng($this->image, $file);
	}
}