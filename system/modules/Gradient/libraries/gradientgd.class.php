<?php
	/**
	 * Gradient GD Image Generator - tk_gradientgd-2007-04-27
	 * =========================================================================
	 *
	 *	@File:		gradientgd.class.php
	 *	@Version:	1.0.0
	 *	@Authors:	Kalle Sommer Nielsen <TerrorKalle@ClanTemplates.com>
	 *  @Authors:   Heavily modified by Paul Clark to work with PHP5 and output images correctly
	 *
	 *
	 * =========================================================================
	 *
	 *		(C) 1989 Kalle Technologies
	 *
	 *
	 *		Parts of this sourcecode is based on code by:
	 *			repley@freemail.it
	 *			root@it.dk
	 *
	 * =========================================================================
	 */
	
	error_reporting(1);
	
	class GradientGD
	{
		var $options;
		var $colors;
		var $colorhandlers;
		var $imagetypes;

		#function GradientGD($x = 2, $y = 2)
		#{
		#	$this->__construct($x, $y);
        #
		#	if(substr(PHP_VERSION, 0, 1) == 4)
		#	{
		#		register_shutdown_function(Array(&$this, '__destruct'));
		#	}
		#}

		function __construct($x = 2, $y = 2)
		{
			if(!is_numeric($x) || !is_numeric($y) || $x < 2 || $y < 2)
			{
				#trigger_error('Invalid image dimensions, must be atleast 2x2 pixels!', E_USER_ERROR);
			}

			if(!function_exists('gd_info'))
			{
				trigger_error('GD Extension is not loaded!', E_USER_ERROR);
			}

			$this->options 			= Array();
			$this->options['imagetype'] 	= 'png';
			$this->options['colorhandler']	= 'RGB';
			$this->options['linear']	= false;
			$this->options['reverse']	= false;	// Left out -- pdclark
			$this->options['saveimage']	= false;
			$this->options['quality']	= 100;

			$this->colors 			= Array();
			$this->colors['start'] 		= Array();
			$this->colors['end'] 		= Array();
			$this->colors['middle']		= Array();

			$gdinfo = gd_info();

			$this->imagetypes		= Array();
			$this->imagetypes['jpg']	= $gdinfo['JPG Support'];
			$this->imagetypes['jpeg']	= $gdinfo['JPG Support'];
			$this->imagetypes['png']	= $gdinfo['PNG Support'];
			$this->imagetypes['gif']	= $gdinfo['GIF Create Support'];
			$this->imagetypes['wbmp']	= $gdinfo['WBMP Support'];
			$this->imagetypes['xbm']	= $gdinfo['XBM Support'];

			$this->colorhandlers		= Array();
			$this->colorhandlers['RGB']	= new GradientGD_ColorHandler_RGB();
			$this->colorhandlers['HEX']	= new GradientGD_ColorHandler_HEX();

			$this->height			= $y;
			$this->width			= $x;

			$falsetypes 			= 0;

			foreach($this->imagetypes as $type => $supported)
			{
				if(!$supported)
				{
					++$falsetypes;
				}
			}

			if(sizeof(array_values($this->imagetypes)) == $falsetypes)
			{
				trigger_error('There was not detected supported image types in your GD configuration!', E_USER_ERROR);
			}
		}

		function __destruct()
		{
			$this->options 			= Array();
			$this->colors 			= Array();
			$this->colorhandlers 		= Array();
			$this->imagetypes		= Array();
			
			$this->colors 			= Array();
			$this->colors['start'] 		= Array();
			$this->colors['end'] 		= Array();
			$this->colors['middle']		= Array();

			$this->height			= 2;
			$this->width			= 2;
		}

		function set_option($varname, $value)
		{
			if(array_key_exists($varname, $this->options))
			{
				switch($varname)
				{
					case('imagetype'):
						$value = strtolower($value);

						if(!in_array($value, $this->imagetypes) || !$this->imagetypes[$value])
						{
							trigger_error('Invalid image type specified or image type isn\'t supported!', E_USER_ERROR);
						}

						if($value == 'jpg')
						{
							$value = 'jpeg';
						}
					break;
					case('colorhandler'):
						$value = strtoupper($value);

						if(!array_key_exists($value, $this->colorhandlers))
						{
							trigger_error('Invalid color handler specified!', E_USER_ERROR);
						}
					break;
					case('linear'):
						if(!is_bool($value))
						{
							trigger_error('Linear option must be a boolean!', E_USER_ERROR);
						}
					break;
					case('quality'):
						if(!is_numeric($value) || $value < 1 || $value > 100)
						{
							trigger_error('Quality must be numeric!', E_USER_ERROR);
						}
					break;
				}

				$this->options[$varname] = $value;
				return(true);
			}
			
			#trigger_error('Invalid option specified!', E_USER_ERROR);
		
		}

		function set_color($colors, $where = '', $handler = '')
		{
			#FB::log($colors);
			#FB::log($where,'where');
			#FB::log($handler, '$handler');
			
			if(!in_array($handler, $this->colorhandlers))
			{
				$handler = $this->options['colorhandler'];
			}

			if(!in_array(strtolower($where), Array('start', 'end', 'middle')))
			{
				trigger_error('Invalid color point!', E_USER_ERROR);
			}

			if(strtolower($where) == 'middle' && is_array($colors[0]) && strtoupper($this->options['colorhandler']) == 'RGB')
			{
				#FB::log('convert 1');
				foreach($colors as $color)
				{
					$this->set_color($color, 'middle', $handler);
				}

				return(true);
			}
			
			// This moron didn't have a check for multiple hex values coming in for 'middle'. Arg! 
			if (strtolower($where) == 'middle' && is_array($colors) && strtoupper($this->options['colorhandler']) == 'HEX') {
				#FB::log('is array');
				foreach($colors as $color)
				{
					$this->set_color($color, 'middle', $handler);
				}
				return(true);
			}

			#FB::log('switch');
			switch(strtoupper($this->options['colorhandler']))
			{
				case('RGB'):
					#FB::log('rgb');
					if(!is_array($colors) || sizeof($colors) != 3 || !is_numeric($colors[0]) || !is_numeric($colors[1]) || !is_numeric($colors[2]))
					{
						trigger_error('Invalid RGB values specified!', E_USER_ERROR);
					}

					if($colors[0] >= 0 && $colors[0] < 256 && $colors[1] >= 0 && $colors[1] < 256 && $colors[2] >= 0 && $colors[2] < 256)
					{
						if(strtolower($where) == 'middle')
						{
							$this->colors['middle'][] = $colors;
						}
						else
						{
							$this->colors[strtolower($where)] = $colors;
						}

						return(true);
					}
				break;
				case('HEX'):
					#FB::log( 'hex');

					if($rgb = $this->colorhandlers['HEX']->convert('RGB', $colors))
					{
						if(strtolower($where) == 'middle')
						{
							$this->colors['middle'][] = $rgb;
						}
						else
						{
							
							$this->colors[strtolower($where)] = $rgb;
						}

						return(true);
					}
				break;
			}

			trigger_error('Invalid color values specified!', E_USER_ERROR);
		}

		function fade($hex_array, $steps)
		{
			if(!is_array($hex_array) || sizeof($hex_array) < 2 || !is_numeric($steps) || $steps < 2 || sizeof($hex_array) > $steps)
			{
				trigger_error('Fading color array does not have valid values or pixel steps were too low!', E_USER_ERROR);
			}

			$tot = sizeof($hex_array);
			$gradient = array();
			$fixend = 2;
			$passages = $tot - 1;

			$stepsforpassage = floor($steps / $passages);
			$stepsremain = $steps - ($stepsforpassage * $passages);

			for($pointer = 0; $pointer < $tot - 1 ; $pointer++)
			{
				$hexstart = $hex_array[$pointer];
				$hexend = $hex_array[$pointer + 1];

				if($stepsremain > 0)
				{
           				if($stepsremain--)
					{
						$stepsforthis = $stepsforpassage + 1;
					}
				}
				else
				{
					$stepsforthis = $stepsforpassage;
				}

				if($pointer > 0)
				{
					$fixend = 1;       
				}
  
				$start['r'] = hexdec(substr($hexstart, 0, 2));
				$start['g'] = hexdec(substr($hexstart, 2, 2));
				$start['b'] = hexdec(substr($hexstart, 4, 2));

				$end['r'] = hexdec(substr($hexend, 0, 2));
				$end['g'] = hexdec(substr($hexend, 2, 2));
				$end['b'] = hexdec(substr($hexend, 4, 2));

				$step['r'] = ($start['r'] - $end['r']) / ($stepsforthis);
				$step['g'] = ($start['g'] - $end['g']) / ($stepsforthis);
				$step['b'] = ($start['b'] - $end['b']) / ($stepsforthis);

				for($i = 0; $i <= $stepsforthis - $fixend; $i++)
				{
					$rgb['r'] = floor($start['r'] - ($step['r'] * $i));
					$rgb['g'] = floor($start['g'] - ($step['g'] * $i));
					$rgb['b'] = floor($start['b'] - ($step['b'] * $i));

					$hex['r'] = sprintf('%02x', ($rgb['r']));
					$hex['g'] = sprintf('%02x', ($rgb['g']));
					$hex['b'] = sprintf('%02x', ($rgb['b']));

					$gradient[] = strtoupper(implode(',', $rgb));
				}
			}

			$gradient[] = implode(',', $this->colorhandlers['HEX']->convert('RGB', $hex_array[$tot - 1]));

			return($gradient);
		}

		function generate($get_image = false)
		{
			$im = ImageCreateTrueColor($this->width, $this->height);

			if(!sizeof($this->colors['middle']))
			{
				$fade = $this->fade(Array($this->colorhandlers['RGB']->convert('HEX', $this->colors['start']), $this->colorhandlers['RGB']->convert('HEX', $this->colors['end'])), $this->height);
			}
			else
			{
				$str = '';
				$colors = Array();
				$colors[] = $this->colors['start'];

				foreach($this->colors['middle'] as $i => $color)
				{
					$colors[] = $this->colors['middle'][$i];
				}

				$colors[] = $this->colors['end'];

				foreach($colors as $color)
				{
					#$str .= '\'' . $this->colorhandlers['RGB']->convert('HEX', $color) . '\', '; // Are you retarded?
					$final_colors[] = $this->colorhandlers['RGB']->convert('HEX', $color);
				}
				#FB::log($colors);
				#$str = substr($str, 0, (strlen($str) - 2)); // Are you retarded?
				#eval('$fade = $this->fade(Array(' . $str . '), $this->height);'); // Are you retarded?
				
				### pdclark fix: allows for output to be filled correctly regardless of dimension or orientation
				### ps: "reverse" is the worse name ever. You really mean left-to-right or top-to-bottom, or at least "rotate90"
				if ($this->options['reverse'] === false) {
					// Reverse being false means we're going to fade from top to bottom
					// So, our fade steps will be equal to our height, and we'll repeat those pixels horizontally
					$fade_steps = $this->height;
				}else {
					// Reverse being true means that we're going to fade from left to right
					// So, our fade steps will be equal to our width, and we'll repeat those pixels vertically
					$fade_steps = $this->width;
				}
				
				$fade = $this->fade($final_colors, $fade_steps);
				#FB::log($fade);
			}

			// Idiot. This only works if the output is wide.
			#for($x = 0; $x < $this->width; ++$x)
			#{
			#	for($y = 0; $y < $this->height; ++$y)
			#	{
			#		$colorline = explode(',', $fade[$x]);
			#		$direction = ($this->options['reverse']) ? '$x, $y, ' : '$y, $x, ';
            #
			#		eval('ImageSetPixel($im, ' . $direction . ' ImageColorAllocate($im, $colorline[0], $colorline[1], $colorline[2]));');
			#	}
			#}
			
			if ($this->options['reverse'] === false) {
				// Reverse being false means we're going to fade from top to bottom
				// So, our fade steps will be equal to our height, and we'll repeat those pixels horizontally
				foreach ($fade as $y => $line) {
					for($x=0;$x < $this->width; ++$x) {
						$colorline = explode(',', $line);
						ImageSetPixel($im, $x, $y, ImageColorAllocate($im, $colorline[0], $colorline[1], $colorline[2]));
					}
				}
			}else {
				// Reverse being true means that we're going to fade from left to right
				// So, our fade steps will be equal to our width, and we'll repeat those pixels vertically
				foreach ($fade as $x => $line) {
					for($y=0;$y < $this->height; ++$y) {
						$colorline = explode(',', $line);
						ImageSetPixel($im, $x, $y, ImageColorAllocate($im, $colorline[0], $colorline[1], $colorline[2]));
					}
				}
			}

			if($get_image)
			{
				return($im);
			}
			elseif($this->options['saveimage'])
			{
				$filename = explode('.', $this->options['saveimage']);

				if(sizeof($filename) == 1)
				{
					$filename = $filename .'.' . ($this->options['imagetype'] == 'jpeg') ? 'jpg' : $this->options['imagetype'];
				}
				else
				{
					$filename = implode('.', $filename);
				}
				
				eval('Image' . $this->options['imagetype'] . '($im, $filename' . (($this->options['imagetype'] == 'jpeg' && $this->options['quality']) ? ', ' . $this->options['quality'] : '') . ');');
			}
			else
			{
				@Header('Content-Type: image/' . $this->options['imagetype']);

				eval('Image' . $this->options['imagetype'] . '($im, \'\'' . (($this->options['imagetype'] == 'jpeg' && $this->options['quality']) ? ', ' . $this->options['quality'] : '') . ');');
			}

			eval('ImageDestroy($im);');
		}
	}

	class GradientGD_ColorHandler_Abstract
	{
		var $supports;

		#function GradientGD_ColorHandler_Abstract()
		#{
		#	$this->__construct();
        #
		#	if(substr(PHP_VERSION, 0, 1) == 4)
		#	{
		#		register_shutdown_function(Array(&$this, '__destruct'));
		#	}
		#}

		function __construct()
		{
			$this->supports			= Array();
		}

		function convert($to, $colorval)
		{
			if(!in_array(strtoupper($to), $this->supports))
			{
				trigger_error('Invalid converting to type specified!', E_USER_ERROR);
			}

			eval('$colorval = $this->convert2' . strtolower($to) . '($colorval);');

			return($colorval);
		}

		function __destruct()
		{
			$this->supports 		= Array();
		}
	}

	class GradientGD_ColorHandler_RGB extends GradientGD_ColorHandler_Abstract
	{
		#function GradientGD_ColorHandler_RGB()
		#{
		#	$this->__construct();
		#}

		function __construct()
		{
			$this->supports 		= Array();
			$this->supports[]		= 'HEX';
		}

		function convert2hex($rgbarray)
		{
			if(!is_array($rgbarray) || sizeof($rgbarray) != 3)
			{
				trigger_error('RGB Array is corrupt; Either it isn\'t an array or the length of the RGB Array doesn\'t equal 3!', E_USER_ERROR);
			}

			for($x = 0; $x < sizeof($rgbarray); ++$x)
			{
				if(strlen($rgbarray[$x]) < 1 || strlen($rgbarray[$x]) > 3)
				{
					trigger_error('RGB value isn\'t 3 characters long!', E_USER_ERROR);
				}
				elseif(eregi('[^0-9]', $rgbarray[$x]))
				{
					trigger_error('RGB value isn\'t numeric!', E_USER_ERROR);
				}
				elseif((intval($rgbarray[$x]) < 0) || (intval($rgbarray[$x]) > 255))
				{
					trigger_error('Integer value of the RGB value is either lower than 0 or higher than 255!', E_USER_ERROR);
				}
				else
				{
					$rgbarray[$x] = strtoupper(str_pad(dechex($rgbarray[$x]), 2, 0, STR_PAD_LEFT));
				}
			}

			return(implode('', $rgbarray));
		}
	}

	class GradientGD_ColorHandler_HEX extends GradientGD_ColorHandler_Abstract
	{
		#function GradientGD_ColorHandler_HEX()
		#{
		#	$this->__construct();
		#}

		function __construct()
		{
			$this->supports 		= Array();
			$this->supports[]		= 'RGB';
		}

		function convert2rgb($hexcode)
		{
			$hexcode = (string)$hexcode; // bugfix - pdclark. Was casting as an array.

			$hexcode = eregi_replace("[^a-fA-F0-9]", "", $hexcode);
			
			if(strlen($hexcode) != 6)
			{
				trigger_error('Hex color isn\'t 6 characters!', E_USER_ERROR);
			}

			$temp = explode(' ', chunk_split($hexcode, 2, ' '));
			$temp = array_map('hexdec', $temp);

			##	return(Array(
			##			'r' => $temp[0], 
			##			'g' => $temp[1], 
			##			'b' => $temp[2]
			##			));
			
			// bugfix - pdclark: associated array not expected!!!
			return(Array(
				$temp[0], 
				$temp[1], 
				$temp[2]
			));
		}
	}
?>