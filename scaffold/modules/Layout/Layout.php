<?php

/**
 * Grid class
 *
 * @author Anthony Short
 * @dependencies None
 **/
class Layout
{

	/**
	 * Width of a single column
	 *
	 * @var string
	 */
	public static $column_width;
	
	/**
	 * Number of columns in the grid
	 *
	 * @var string
	 */
	public static $column_count;
	
	/**
	 * Total width of the gutters combined
	 *
	 * @var string
	 */
	public static $gutter_width;
	
	/**
	 * Left gutter width
	 *
	 * @var string
	 */
	public static $left_gutter_width;
	
	/**
	 * Right gutter width
	 *
	 * @var string
	 */
	public static $right_gutter_width;
	
	/**
	 * The total width of the grid
	 *
	 * @var string
	 */
	public static $grid_width;
	
	/**
	 * The baseline height
	 *
	 * @var string
	 */
	public static $baseline;
	
	/**
	 * The unit the grid is based on
	 */
	public static $unit;
	
	public static $columns;

	/**
	 * Parse the @grid rule and calculate the grid.
	 *
	 * @author Anthony Short
	 * @param $css
	 */
	public static function pre_process()
	{
		if( $settings = Scaffold::$css->find_at_group('grid') )
		{
			$groups = $settings['groups'];
			$settings = $settings['values'];
			
			# You really should only have one @grid
			if(count($groups) > 1)
			{
				Scaffold::error('Layout module can only use one @grid rule');
			}
	
			# Make sure the groups have all the right properties
			self::check_grid($groups[0],$settings);
			
			# The number of columns, baseline and unit
			$cc 	= $settings['column-count'];
			$unit 	= (isset($settings['unit'])) ? trim($settings['unit']) : 'px';
			$bl 	= (isset($settings['baseline'])) ? $settings['baseline'] : 18;
			$lgw 	= (isset($settings['left-gutter-width'])) ? $settings['left-gutter-width'] : 0;
			$rgw 	= (isset($settings['right-gutter-width'])) ? $settings['right-gutter-width'] : 0;
			$gw		= $settings['gutter-width'] = $lgw + $rgw;

			if(isset($settings['grid-width']))
			{
				$grid = $settings['grid-width'];

				$totalgutters = $cc * ($gw - 1);
				$netgridwidth = $grid - $totalgutters;
				
				$cw = floor($netgridwidth / $cc);
			}
			else
			{
				$cw = $settings['column-width'];
				$grid = ($cw + $gw) * $cc;
			}
			
			$grid_settings = array(
				'column_count' 			=> $cc,
				'column_width' 			=> $cw . $unit,
				'gutter_width' 			=> $gw . $unit,
				'left_gutter_width' 	=> $lgw . $unit,
				'right_gutter_width' 	=> $rgw . $unit,
				'grid_width' 			=> $grid . $unit,
				'baseline' 				=> $bl . $unit
			);

			# Set them as constants we can use in the css
			foreach($grid_settings as $key => $value)
			{
				Constants::set($key,$value);
			}
			
			if( Scaffold::$config['Layout']['grid_image'] )
			{
				# Generate the grid.png
				$img = self::create_grid_image($cw, $bl, $lgw, $rgw);
				Scaffold::$css->string .= "=grid{background:url('".$img."');}";
			}
			
			if( Scaffold::$config['Layout']['grid_classes'] )
			{
				for ($i = 1; $i <= $cc; $i++)
				{
					self::$columns[$i] = ($i * $cw) + (($i * $gw) - $gw);
				}	
				
				Scaffold::$css->string .= file_get_contents( Scaffold::find_file('Layout/css/grid.css') );
			}

			# Make each of the column variables a member variable
			self::$column_count = $cc;
			self::$column_width = $cw;
			self::$gutter_width = $gw;
			self::$left_gutter_width = $lgw;
			self::$right_gutter_width = $rgw;
			self::$grid_width = $grid;
			self::$baseline = $bl;
			self::$unit = $unit;
		}
	}
	
	public static function display()
	{
		if(Scaffold::option("grid") && isset(self::$column_width))
		{
			# Make sure we're sending HTML
			header('Content-Type: text/html');
			
			# Load the test suite markup
			$page = Scaffold::load_view('scaffold_grid.php');

			# Echo and out!
			echo($page); 
			exit;
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
	private static function create_grid_image($cw, $bl, $lgw, $rgw)
	{
		# Path to the image
		$file = Scaffold::$cache_path . "Layout/{$lgw}_{$cw}_{$rgw}_{$bl}_grid.png";
		$file = Scaffold::url_path($file);
			
		if(!file_exists($file))
		{
			Scaffold::cache_create('Layout');
			
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
	    
	    return $file;
	}
	
	/**
	 * Checks if all the needed settings are present in a group
	 *
	
	 * @param $group
	 * @return boolean
	 */
	private static function check_grid($group,$settings)
	{
		$code = '<pre><code>' . $group . '<pre></code>';

		# Make sure none of the required options are missing
		if(!isset($settings['column-count']) || ( !isset($settings['left-gutter-width']) && !isset($settings['right-gutter-width']) ) || ( !isset($settings['grid-width']) && !isset($settings['column-width']) ))
		{
			$error = "@grid rule requires the <strong>column-count, left-gutter-width or right-gutter-width and column-width or grid-width</strong> properties.\n\n$code";
			Scaffold::error($error);
		}

		elseif( isset($settings['column-width']) && isset($settings['grid-width']) )
		{
			Scaffold::error("You can only have either the column-width or grid-width property set.\n\n$code");
		}
		
		return true;
	}
}