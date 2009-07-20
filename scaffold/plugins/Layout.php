<?php defined('BASEPATH') OR die('No direct access allowed.');

/**
 * Grid class
 *
 * @author Anthony Short
 * @dependencies User_agent, Constants, CSS3_helper
 **/
class Layout extends Plugins
{
	/**
	 * The construct is important for plugins. It is where flags MUST 
	 * be set. For each flag that exists, a seperate file will be cached
	 * and only be sent to users that meet the conditions of those flags
	 *
	 * @author Anthony Short
	 */
	function __construct()
	{	
		# Set a flag for their browser, so it caches it for each
		# browser. If we don't set flags, then it would only cache
		# the css once, using the first browser to request it as the
		# user agent.  
		if(User_agent::can_boxsize()) 
		{
			Cache::flag('Box-sizing');	
		}
	}
	
	/**
	 * The pre-processing function occurs after the importing,
	 * but before any real processing. This is usually the stage
	 * where we set variables and the like, getting the css ready
	 * for processing.
	 *
	 * @author Anthony Short
	 * @param $css
	 */
	function pre_process()
	{		
		# Find the @grid - this returns an array of 'groups' and 'values'		
		if( $settings = CSS::find_at_group('grid') )
		{
			# Remove it from the css
			CSS::replace($settings['groups'], array()); 
			
			# Store it so it's easier to grab
			$settings = $settings['values'];			
		}
		
		# If there's no grid settings, we'll create some default ones, just in case
		else
		{
			$settings = array(
				'column-width' => 60,
				'gutter-width' => 10,
				'column-count' => 12,
				'baseline' => "18px"
			);
		}
		
		# A lot easier to write and read
		$cw 	=& $settings['column-width'];
		$gw 	=& $settings['gutter-width'];
		$cc 	=& $settings['column-count'];
		$bl		=& $settings['baseline'];
			
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
		
		# Set them as constants we can use in the css
		Constants::set($settings);
		
		# Remove the unit 
		$bl = preg_replace('/[a-zA-Z]*/', '', $bl);
		$gw = preg_replace('/[a-zA-Z]*/', '', $gw);
		
		# Generate the grid.png
		self::create_grid_image($cw, $bl, $gw);

		# Create grid classes (.column-1 etc) and add them to the css		
		$this->create_grid_classes($cw, $bl, $gw, $cc);
	
		# Replace the columns:; properties
		$this->replaceColumns($cw, $gw, $cc);
		
		# Round to baselines
		$this->round_to_baseline($bl);
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
		$found = CSS::find_functions('round');
		
		foreach($found as $key => $match)
		{
			CSS::replace($match, round_nearest($found[1][$key],$baseline)."px");
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
			$s .= ".columns-$i {width:".($width - ($gw * 2))."px;}";
			
			# Make the .span-x classes
			$s .= ".span-$i{width:".($width - ($gw * 2))."px;}";
			
			# Make the row/line/group/parent class
			$s .= ".group{padding:0 {$gw}px;margin:".($bl/2)."px 0;}";
			
			# Make the .push classes
			$s .= ".push-$i{left:{$width}px;margin-right:".($width + $gw)."px;}";
			
			# Make the .pull classes
			$s .= ".pull-$i{left:-".($width)."px;margin-right:".-($width - $gw)."px;}";
			
			# Make the .shift-down classes
			$s .= ".baseline-down-$i {top:".($bl * $i).";}";
			
			# Make the .shift-up classes
			$s .= ".baseline-up-$i {top:".-($bl * $i).";}";
			
			# Make the .baseline-x classes
			$s .= ".baseline-$i{height:".($bl * $i)."px;}";
			
			# Make the .append classes
			$s .= ".append-$i{padding-right:{$width}px;}";
			
			# Make the .prepend classes
			$s .= ".prepend-$i{padding-left:{$width}px;}";
			
			$pullselectors[] = ".pull-$i";
			$pushselectors[] = ".push-$i";
			$columns[] = ".columns-$i";
			Constants::set("span-".$i, ($width - ($gw * 2)));
		}
		
		# If we add our classes here they are more managable and won't
		# be added until the very end, saving memory. 
		CSS::add('.first', 			'margin-left:0 !important;');
		CSS::add('.last', 			'margin-right:0 !important;');
		CSS::add('.solo', 			'margin-left:0 !important;margin-right:0 !important;');
		CSS::add('.group', 			'margin-left:0 !important; margin-right:0 !important;');
		CSS::add('.group.last', 	'padding-right:0 !important;');
		CSS::add('.group.first', 	'padding-left:0 !important;');
	
		CSS::add(implode(",", $columns), "float:left;margin:".($bl/2)."px {$gw}px;position:relative;");

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
		Constants::set('grid_image_data', $img_data);
	    
	    # Kill it
	    ImageDestroy($image);
	}
	 
	/**
	* Builds the columns:x; properties
	*
	* @param   string   css file string
	* @return  string	css file string
	*/
	private function replaceColumns($cw, $gw, $cc)
	{		
		# We'll loop through each of the columns properties by looking for each columns:x; property.
		# This means we'll only loop through $columnscount number of times which could be better
		# or worse depending on how many columns properties there are in your css
		
		for ($i=1; $i <= $cc; $i++) 
		{ 
			# Matches all selectors (just the properties) which have a columns property
			while($match = CSS::find_properties_with_value('columns', $i)) 
			{
				# For each of the selectors with columns properties...
				foreach ($match[0] as $key => $properties)
				{
					$styles = "";
					
					$properties 		= $match[1][0]; # First match is all the properties				
					$columnsproperty 	= $match[2][0]; # Second match is just the columns property
					$numberofcolumns	= $match[3][0]; # Third match is just number of columns

					# Calculate the width of the column
					$width = ($cw*$i)-($gw*2);
					
					# If the browser doesn't support box-sizing, minus the padding and border
					# We'll see if the flags have been set from the browser plugin
					if(User_agent::can_boxsize())
					{
						$styles .= "	
							-moz-box-sizing:border-box;
							-webkit-box-sizing:border-box;
							-ms-box-sizing:border-box;
							box-sizing:border-box;
							behavior:url(\"".BASEURL."/behaviours/boxsizing.htc\");";
					}
					else
					{
						# Calculate the width of the column with adjustments for padding and border
						$width = $width - (CSS::get_padding($properties) + CSS::get_border($properties));
					}
										
					# Add the rest of the properties
					$styles .= "width:{$width}px;float:left;margin-right:{$gw}px; margin-left:{$gw}px;";
						
					# Fix up the retarded bugs in IE
					if( User_agent::$browser == 'Internet Explorer' && User_agent::$version < 7 )
					{
						$styles .= "display:inline;overflow:hidden;";
					}
					
					# Insert into property string
					$newproperties = str_replace($columnsproperty, $styles, $properties);

					# Insert this new string into CSS string
					CSS::replace($properties, $newproperties);
				
				}
			}
		}
	}
}