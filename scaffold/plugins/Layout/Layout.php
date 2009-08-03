<?php defined('BASEPATH') OR die('No direct access allowed.');

/**
 * Grid class
 *
 * @author Anthony Short
 * @dependencies None
 **/
class Layout extends Plugins
{
	/**
	 * The pre-processing function occurs after the importing,
	 * but before any real processing. This is usually the stage
	 * where we set variables and the like, getting the css ready
	 * for processing.
	 *
	 * @author Anthony Short
	 * @param $css
	 */
	public function pre_process()
	{		
		# Find the @grid - this returns an array of 'groups' and 'values'		
		if( $settings = CSS::find_at_group('grid') )
		{
			# Remove it from the css
			CSS::replace($settings['groups'], array()); 
			
			# Store it so it's easier to grab
			$settings = $settings['values'];
			
			# A lot easier to write and read
			$cw 	=& $settings['column-width'];
			$gw 	=& $settings['gutter-width'];
			$cc 	=& $settings['column-count'];
			$bl		=& $settings['baseline'];
			
			# Set them as constants we can use in the css
			if(class_exists('Constants'))
			{
				Constants::set($settings);
			}
			
			# Remove the unit 
			$bl = preg_replace('/[a-zA-Z]*/', '', $bl);
			$gw = preg_replace('/[a-zA-Z]*/', '', $gw);
			$cw = preg_replace('/[a-zA-Z]*/', '', $cw);
				
			# Check whether we should use the column width or calculate it from the grid width
			if(isset($settings['grid-width'])) 
			{			
				# Our awesome column width calculation
				$cw = ($grid_w - ($gw * ($cc-1)))/$cc + $gw;
			}
			else
			{
				$cw = $cw + ($gw*2);
				$grid_w = $cw * $cc;
			}
			
			# Add grid width to the settings
			$settings['grid-width'] = $grid_w . "px";
			
			if(class_exists('Constants'))
			{
				Constants::set('grid-width', $settings['grid-width']);
			}
			
			# Generate the grid.png
			self::create_grid_image($cw, $bl, $gw);
	
			# Create grid classes (.column-1 etc) and add them to the css		
			$this->create_grid_classes($cw, $bl, $gw, $cc);
			
			# Round to baselines
			$this->round_to_baseline($bl);
		}
	}
	
	/**
	 * Finds any round(n) and rounds the number 
	 * to the nearest multiple of the baseline
	 *
	 * @author Anthony Short
	 * @param $css
	 */
	function round_to_baseline($baseline)
	{
		if($found = CSS::find_functions('round'))
		{
			foreach($found[0] as $key => $match)
			{
				CSS::replace($match, round_nearest($found[1][$key],$baseline)."px");
			}
		}
	}

	/**
	* Generates the grid classes similar to Blueprints grid.css
	* and appends them to the css string.
	*
	* @param   string   css file string
	* @return  string	css file string
	*/	
	public function create_grid_classes($cw, $bl, $gw, $cc)
	{
		$s = "";
		
		for ($i=1; $i <= $cc; $i++) 
		{
			$width = $cw * $i;
			
			# Make the .columns-x classes
			$s .= "

				.columns-$i 		{width:".($width - ($gw * 2))."px;}
				.span-$i			{width:".($width - ($gw * 2))."px;}
				.group				{padding:0 {$gw}px;margin:".($bl/2)."px 0;}
				.push-$i			{left:{$width}px;margin-right:".($width + $gw)."px !important;}
				.pull-$i			{left:-".($width)."px;margin-right:".-($width - $gw)."px !important;}
				.baseline-down-$i 	{top:".($bl * $i)."px;}
				.baseline-up-$i 	{top:".-($bl * $i)."px;}
				.baseline-$i		{height:".($bl * $i)."px;}
				.append-$i			{padding-right:{$width}px;}
				.prepend-$i			{padding-left:{$width}px;}
			
			";
			
			$pullselectors[] 	= ".pull-$i";
			$pushselectors[] 	= ".push-$i";
			$columns[] 			= ".columns-$i";
		}
		
		# If we add our classes here they are more managable and won't
		# be added until the very end, saving memory. 
		$s .= 
			implode(",", $columns)."{float:left;margin:0 {$gw}px {$bl}px;position:relative;}
			".implode(",", $pullselectors)."{position:relative;}
			".implode(",", $pushselectors)."{position:relative;}
		";

		# Append it to the css
		CSS::append($s);
	}

	/**
	* Generates the background grid.png
	*
	* @author Anthony Short
	* @param $cl Column width
	* @param $bl Baseline
	* @param $gw Gutter Width
	* @return null
	*/
	public function create_grid_image($cw, $bl, $gw)
	{		
		$image = ImageCreate($cw,$bl);
		
		$colorWhite		= ImageColorAllocate($image, 255, 255, 255);
		$colorGrey		= ImageColorAllocate($image, 200, 200, 200);
		$colorBlue		= ImageColorAllocate($image, 240, 240, 255);

		# Draw column
		Imagefilledrectangle($image, 0, 0, ($cw - $gw - 1), ($bl - 1), $colorBlue);
		
		# Draw right gutter
		Imagefilledrectangle($image, ($cw - $gw + 1), 0, ($cw), ($bl - 1), $colorWhite);
		
		# Draw left gutter
		Imagefilledrectangle($image, 0, 0, ($gw), ($bl - 1), $colorWhite);
	
		# Draw baseline
		imageline($image, 0, ($bl - 1 ), $cw, ($bl - 1 ), $colorGrey);
		
		# ImagePNG outputs it to the buffer. We grab this,
		# store it in a variable and empty the buffer.
		ob_start();
		ImagePNG($image);
		$img_data = (string)ob_get_contents();
		ob_end_clean();	
		
		# Encode it as base64
		$img_data = 'data:image/PNG;base64,'.base64_encode($img_data);
		
		# Set it as a constant
		CSS::add('.showgrid', "background:url($img_data);");
	    
	    # Kill it
	    ImageDestroy($image);
	}
}