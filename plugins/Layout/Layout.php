<?php defined('SYSPATH') OR die('No direct access allowed.');

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
	public static function pre_process()
	{		
		# Find the @grid - this returns an array of 'groups' and 'values'		
		if( $settings = CSS::find_at_group('grid') )
		{
			# Remove it from the css
			CSS::replace($settings['groups'], array()); 
			
			# Store it so it's easier to grab
			$settings = $settings['values'];
			
			# The number of columns, baseline and unit
			$cc = $settings['column-count'];
			$unit = $settings['unit'];
			$bl = $settings['baseline'];
			
			# Get the gutters
			$lgw = (isset($settings['left-gutter-width'])) ? $settings['left-gutter-width'] : 0;
			$rgw = (isset($settings['right-gutter-width'])) ? $settings['right-gutter-width'] : 0;
			
			# Get the total gutter width
			$gw	= $settings['gutter-width'] = $lgw + $rgw;
			
			# Check whether we should use the column width or calculate it from the grid width
			if(isset($settings['grid-width'])) 
			{
				$grid = $settings['grid-width'];
				
				# Our awesome column width calculation
				$cw = ($grid - ($gw * ($cc-1)))/$cc;
			}
			else
			{
				$cw = $settings['column-width'];
				$grid = ($cw + $gw) * $cc - $gw;
			}
			
			$grid_settings = array(
				'column-count' 			=> $cc,
				'column-width' 			=> $cw . $unit,
				'gutter-width' 			=> $gw . $unit,
				'left-gutter-width' 	=> $lgw . $unit,
				'right-gutter-width' 	=> $rgw . $unit,
				'grid-width' 			=> $grid . $unit,
				'baseline' 				=> $bl . $unit
			);

			# Set them as constants we can use in the css
			foreach($grid_settings as $key => $value)
			{
				Constants::set($key,$value);
			}
			
			# Make a directory in the cache just for this plugin
			if(!is_readable(CACHEPATH.'Layout'))
				mkdir(CACHEPATH.'Layout');
			
			# Path to the image
			$img = CACHEPATH . "/Layout/{$lgw}_{$cw}_{$rgw}_{$bl}_grid.png";
			
			# Generate the grid.png
			self::create_grid_image($cw, $bl, $lgw, $rgw, $img);
			
			$img = str_replace(DOCROOT,'/',$img);
			
			CSS::append(".showgrid{background:url('".$img."');}");
			
			# Round to baselines
			self::round_to_baseline($bl);
			
			# Add mixins	
			$mixins = CSScaffold::config('Layout.support') . '/mixins/grid.css';
			$mixins = file_get_contents($mixins);
			CSS::append($mixins);
		}
	}
	
	/**
	 * Finds any round(n) and rounds the number 
	 * to the nearest multiple of the baseline
	 *
	 * @author Anthony Short
	 * @param $css
	 */
	private static function round_to_baseline($baseline)
	{
		if($found = CSS::find_functions('round'))
		{
			foreach($found[0] as $key => $match)
			{
				
				CSS::replace($match, round($found[1][$key]/$baseline)*$baseline."px");
			}
		}
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
	private static function create_grid_image($cw, $bl, $lgw, $rgw, $file)
	{		
		if(!file_exists($file))
		{
			$image = ImageCreate($cw + $lgw + $rgw,$bl);
			
			$colorWhite		= ImageColorAllocate($image, 255, 255, 255);
			$colorGrey		= ImageColorAllocate($image, 200, 200, 200);
			$colorBlue		= ImageColorAllocate($image, 240, 240, 255);
			
			# Draw left gutter
			Imagefilledrectangle($image, 0, 0, ($lgw - 1), $bl, $colorWhite);
			
			# Draw column
			Imagefilledrectangle($image, $lgw, 0, $cw + $lgw - 1, $bl, $colorBlue);
			
			# Draw right gutter
			Imagefilledrectangle($image, ($lgw + $cw + 1), 0, $lgw + $cw + $rgw, $bl, $colorWhite);
		
			# Draw baseline
			imageline($image, 0, ($bl - 1 ), $lgw + $cw + $rgw, ($bl - 1), $colorGrey);
			
			ImagePNG($image, $file);
		    
		    # Kill it
		    ImageDestroy($image);
	    }
	}
}