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
	 * The pre-processing function occurs after the importing,
	 * but before any real processing. This is usually the stage
	 * where we set variables and the like, getting the css ready
	 * for processing.
	 *
	 * @author Anthony Short
	 * @param $css
	 */
	function pre_process($css)
	{		
		# Find the @grid - this returns an array of 'groups' and 'values'		
		if( $settings = find_at_group('grid', $css) )
		{
			# Remove it from the css
			$css = str_replace($settings['groups'], array(), $css); 
			
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
					
		# Check whether we should use the column width or calculate it from the grid width
		if(!isset($settings['column-width'])) 
		{			
			# Our awesome column width calculation
			$settings['column-width'] = ($settings['grid-width'] - ($settings['gutter-width'] * ($settings['column-count']-1)))/$settings['column-count'] + $$settings['gutter-width'];
		}
		else
		{
			$settings['column-width'] 	= $settings['column-width'] + ($settings['gutter-width']*2);
			$settings['grid-width'] 	= $settings['column-width'] * $settings['column-count'];
		}
		
		# Set them as constants we can use in the css
		Constants::set($settings);
		
		# Remove the unit 
		$settings['baseline'] = preg_replace('/[a-zA-Z]*/', '', $settings['baseline']);
		$settings['gutter-width'] = preg_replace('/[a-zA-Z]*/', '', $settings['gutter-width']);
		
		# Generate the grid.png
		self::create_grid_image($settings['column-width'], $settings['baseline'], $settings['gutter-width']);

		# Create grid classes (.column-1 etc) and add them to the css		
		$css .= $this->create_grid_classes($settings['column-width'], $settings['baseline'], $settings['gutter-width'], $settings['column-count']);
	
		# Replace the columns:; properties
		$css = $this->replaceColumns($css);
		
		# Round to baselines
		$css = $this->round_to_baseline($settings['baseline'], $css);

		return $css;
	}
	
	/**
	 * Finds any round(n) and rounds the number 
	 * to the nearest multiple of the baseline
	 *
	 * @author Anthony Short
	 * @param $css
	 */
	function round_to_baseline($baseline, $css)
	{
		if(preg_match_all('/round\((\d+)\)/', $css, $matches))
		{
			foreach($matches[1] as $key => $match)
			{
				$num = round_nearest($match,$baseline);
				$css = str_replace($matches[0][$key],$num."px",$css);
			}
		}
		
		return $css;
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
			$s .= ".columns-$i{width:".($width - ($gw * 2))."px;float:left;margin-right:{$gw}px;margin-left:{$gw}px;}";
			
			# Make the .span-x classes
			$s .= ".span-$i{width:".($width - ($gw * 2))."px;}";
			
			# Make the .push classes
			$s .= ".push-$i{margin-right:".-($width)."px;}";
			
			# Make the .pull classes
			$s .= ".pull-$i{ margin-left:-".($width)."px;}";
			
			# Make the .baseline-x classes
			$s .= ".baseline-$i{height:".($bl * $i)."px;}";
			
			# Make the .append classes
			$s .= ".append-$i{padding-right:{$width}px;}";
			
			# Make the .prepend classes
			$s .= ".prepend-$i{padding-left:{$width}px;}";
			
			$pullselectors[] = ".pull-$i";
			$pushselectors[] = ".push-$i";
			Constants::set("span-".$i, ($width - ($gw * 2)));
		}
		
		$s .= substr_replace(implode(",", $pushselectors),"",-1) . "{float:left;position:relative;}";
		$s .= substr_replace(implode(",", $pullselectors),"",-1) . "{float:left;position:relative;}";

		return $s;
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
	public function replaceColumns($css)
	{
		$cw 		= Constants::get('column-width');
		$gw 		= Constants::get('gutter-width');
		$cc 		= Constants::get('column-count');
		
		# We'll loop through each of the columns properties by looking for each columns:x; property.
		# This means we'll only loop through $columnscount number of times which could be better
		# or worse depending on how many columns properties there are in your css
		
		for ($i=1; $i <= $cc; $i++) 
		{ 
			# Matches all selectors (just the properties) which have a columns property
			while($match = find_properties_with_value('columns', $i, $css)) 
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
					if($this->can_boxsize())
					{		
						$styles .= "box-sizing:border-box;";
						
						switch (User_agent::$browser)
						{
						    case 'Safari':
						    	$styles .= "-webkit-box-sizing:border-box;";
						    break;
						        
						    case 'Firefox':
						    	$styles .= "-moz-box-sizing:border-box;";
						    break;
						    
						    case 'Internet Explorer':
						    	$styles .= "-ms-box-sizing:border-box;";
						    break;
						}
					} 
					else
					{	
						# Calculate the width of the column with adjustments for padding and border
						$width = $width - $this->extrawidth($properties);
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
					$css = str_replace($properties, $newproperties, $css);
				
				}
			}
		}
		return $css;
	}

	/**
	* Calculates the total amount of padding present
	* in a selector. This doesn't factor in cascading.
	*
	* @param   string   All of the properties of a selector
	* @return  string	The total amount of left and right padding combined
	*/
	public function getPadding($properties)
	{
		$padding = $paddingleft = $paddingright = 0;
		
		# Get the padding (in its many different forms)

		if (preg_match_all('/padding\:(.+?)\;/x', $properties, $matches))
		{
			$padding = preg_split('/\s/', $matches[1][0]);
			$padding = str_replace("px", "", $padding);
			
			if (sizeof($padding) == 1)
			{
				$paddingright = $paddingleft = $padding[0];
			} 
			elseif (sizeof($padding) == 2 || sizeof($padding) == 3)
			{
				$paddingleft = $paddingright = $padding[1];
			}
			elseif (sizeof($padding) == 4)
			{
				$paddingright = $padding[1];
				$paddingleft = $padding[3];
			}
		}
	
		if (preg_match_all('/padding\-left\:(.+?)\;/x', $properties, $paddingl))
		{
			$paddingleft = str_replace('px', '', $paddingl[1][0]);
		}
		
		if (preg_match_all('/padding\-right\:(.+?)\;/x', $properties, $paddingr))
		{
			$paddingright = str_replace('px', '', $paddingr[1][0]);
		}

		return $paddingleft + $paddingright;
		
	}

	/**
	* Calculates the total amount of border present
	* in a selector. This doesn't factor in cascading.
	*
	* @param   string   All of the properties of a selector
	* @return  string	The total amount of left and right border combined
	*/
	public function getBorder($properties)
	{		
		$border = $borderleft = $borderright = 0;

		if (preg_match_all('/border\:.+?\;/x', $properties, $matches))
		{
			if (preg_match_all('/\d.?px/', $matches[0][0], $match))
			{
				$borderw = str_replace('px','',$match[0][0]);
				
				$borderleft = $borderright = $borderw;
			}
		}	
		if (preg_match_all('/border\-left\:.+?\;/x', $properties, $matches))
		{
			if (preg_match_all('/\d.?px/', $matches[0][0], $match))
			{
				$borderleft = str_replace('px','',$match[0][0]);
			}
		}
		
		if (preg_match_all('/border\-right\:.+?\;/x', $properties, $matches))
		{
			if (preg_match_all('/\d.?px/', $matches[0][0], $match))
			{
				$borderright = str_replace('px','',$match[0][0]);
			}
		}
			
		return $borderleft + $borderright;
		
	}
	
	/**
	 * Determines if the user agent is capable of using box-sixing
	 *
	 * @author Anthony Short
	 * @return boolen
	 */
	public static function can_boxsize()
	{
		if (( User_agent::$browser == 'Internet Explorer' && User_agent::$version < 8 ) || User_agent::$version == "" )
		{
			return false;
		}
		else
		{
			return true;
		}
	}
	
	/**
	 * Quicker way to get the extra width of an element
	 *
	 * @author Anthony Short
	 * @param $properties
	 * @return string
	 */
	private function extrawidth($properties)
	{
		# Send the properties through the functions to get the padding and border from them  
		$padding = $this->getPadding($properties);
		$border = $this->getBorder($properties);
		
		return $padding + $border;
	}

}