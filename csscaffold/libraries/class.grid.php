<?php defined('BASEPATH') OR die('No direct access allowed.');

/**
 * Grid
 *
 * Does all of the grid calculations. Creates the grid png file,
 * the grid.css file, the grid.xml file and builds the grid
 * from the grid settings within the css.
 *
 * @package csscaffold
 * @author Anthony Short
 **/
class Grid
{

	/**
	* Gets the grid settings from the css, and stores them.
	*
	* @param   string   css file string
	* @return  none
	*/
	function __construct($css)
	{	
		global $cc, $cw, $gw, $bl, $gridw;

		// Make sure there are settings, if so, grab them
		if (preg_match_all('/@grid.*?\}/sx', $css, $match)) 
		{
			$cc = $this->getParam('column-count', $match[0][0]);
			$cw = $this->getParam('column-width', $match[0][0]);
			$gw = $this->getParam('gutter-width', $match[0][0]);
			$bl = $this->getParam('baseline', $match[0][0]);
						
			// Check whether we should use the column width or calculate it from the grid width
			if ($cw == "") 
			{
				$gridw	= $this->getParam('grid-width', $match[0][0]);
				
				// Our awesome column width calculation
				$cw = ($gridw - ($gw * ($cc-1)))/$cc; + $gw;
			}
			else 
			{
				$cw = $cw + ($gw*2);
				$gridw = ($cw * $cc);
			}
			
			// Set them in the config so other plugins can access this info
			$this->config = array(
				'columncount' => $cc, 
				'columnwidth' => $cw, 
				'gutterwidth' => $gw, 
				'baseline' => $bl, 
				'gridwidth' => $gridw
			);
			
			// Merge the old Layout config with this new one and save it to the core config
			Core::config_set(
				'Layout', 
				array_merge($this->config, Core::config('Layout'))
			);
			
			// Enable the plugin
			$this->active = TRUE;

		}
	}

	/**
	* Generates the grid classes similar to Blueprints grid.css
	* and appends them to the css string.
	*
	* @param   string   css file string
	* @return  string	css file string
	*/	
	public function generateGridClasses($css)
	{
		global $cc, $cw, $gw, $bl, $gridw; 

		$s = $pushselectors = $pullselectors = "";
		
		if(Core::config('columns-x', 'Layout') == TRUE)
		{
			// Make the .columns-x classes
			for ($i=1; $i < $cc + 1; $i++) { 
				$w = $cw * $i - ($gw*2);
				$s .= ".columns-$i{width:".$w."px;float:left;margin-right:".$gw."px;margin-left:".$gw."px;}";
			}
		}
	
		if(Core::config('push', 'Layout') == TRUE)
		{
			// Make the .push classes
			for ($i=1; $i < $cc; $i++) { 
				$w = $cw * $i + $gw;
				$s .= ".push-$i{margin-left:".$w."px;}";
				$pushselectors .= ".push-$i,";
			}
			$s .= substr_replace($pushselectors,"",-1) . "{float:left;position:relative;}";
		}
		
		if(Core::config('pull', 'Layout') == TRUE)
		{
			// Make the .pull classes
			for ($i=1; $i < $cc; $i++) { 
				$w = $cw * $i + $gw;
				$s .= ".pull-$i{ margin-left:-".$w."px; }";
				$pullselectors .= ".pull-$i,";
			}
			$s .= substr_replace($pullselectors,"",-1) . "{float:left;position:relative;}";
		}
		
		if(Core::config('baseline-x', 'Layout') == TRUE)
		{
			// Make the .baseline-x classes
			for ($i=1; $i < 51; $i++) { 
				$h = $bl * $i;
				$s .= ".baseline-$i{height:".$h."px;}";
			}
		}
		
		if(Core::config('baseline-pull-x', 'Layout') == TRUE)
		{
			// Make the .baseline-pull-x class
			for ($i=1; $i < 51; $i++) { 
				$h = $bl * $i;
				$s .= ".baseline-pull-$i{margin-top:-".$h."px;}";
			}
		}
		
		if(Core::config('baseline-push-x', 'Layout') == TRUE)
		{
			// Make the .baseline-push-x classes
			for ($i=1; $i < 51; $i++) { 
				$h = $bl * $i;
				$s .= ".baseline-push-$i{margin-top:".$h."px;}";
			}
		}
		
		if(Core::config('append', 'Layout') == TRUE)
		{
			// Make the .append classes
			for ($i=1; $i < $cc; $i++) { 
				$w = $cw * $i;
				$s .= ".append-$i{padding-right:".$w."px;}";
			}
		}
		
		if(Core::config('prepend', 'Layout') == TRUE)
		{
			// Make the .prepend classes
			for ($i=1; $i < $cc; $i++) { 
				$w = $cw * $i;
				$s .= ".prepend-$i{padding-left:".$w."px;}";
			}
		}
		
		return $css . $s;
	}

	/**
	* Generates the background grid.png
	*
	* @return  none
	*/
	public function generateGridImage()
	{
		global $cc, $cw, $gw, $bl, $gridw;
		
		$image = ImageCreate($cw, $bl);
		
		$colorWhite		= ImageColorAllocate($image, 255, 255, 255);
		$colorGrey		= ImageColorAllocate($image, 200, 200, 200);
		$colorBlue		= ImageColorAllocate($image, 240, 240, 255);

		// Draw column
		Imagefilledrectangle($image, 0, 0, ($cw - $gw - 1), ($bl - 1), $colorBlue);
		
		// Draw gutter
		Imagefilledrectangle($image, ($cw - $gw + 1), 0, ($cw), ($bl - 1), $colorWhite);
	
		// Draw baseline
		imageline($image, 0, ($bl - 1 ), $cw, ($bl - 1 ), $colorGrey);
		
		// Check the folder
		if (!is_dir(ASSETPATH . "/backgrounds/")) mkdir(ASSETPATH . "/backgrounds/", 0777);
		
		// Create it
	    ImagePNG($image, ASSETPATH . "/backgrounds/grid.png") or die("Can't save the grid.png file");
	    
	    // Kill it
	    ImageDestroy($image);
	}

	/**
	* Generates an xml file about the layouts 
	* in the css file based on the format ".layout-layoutname"
	*
	* @param   string   css file string
	* @return  none
	*/	
	public function generateLayoutXML($css)
	{
		global $cc, $cw, $gw, $bl, $gridw;
		
		// Build the initial xml string
		$xml = "<?xml version=\"1.0\" ?>\n";
		$xml .= "<grid>\n";
		$xml .= "<column-width>".($cw - $gw)."</column-width>\n<column-count>$cc</column-count>\n<grid-width>$gridw</grid-width>\n<gutter-width>$gw</gutter-width>\n<baseline>$bl</baseline>\n";
		
		// Grab the layouts from the css
		$list = "<layouts>\n";
		$layoutnames = array();

		if(preg_match_all('/\.layout\-([a-zA-Z0-9\-]*)/',$css,$matches))
		{
			foreach($matches[1] as $match)
			{
				array_push($layoutnames, $match);
			}					
		}
		
		$layouts = array_unique($layoutnames);
		
		foreach($layouts as $layout)
		{
			$node = "<layout>layout-".$layout."</layout>\n";
			$list .= $node;
		}
		
		$list .= "\n</layouts>";
		
		// Join them together
		$xml = $xml . $list . "</grid>"; 
				
		// Write it to the file
		file_put_contents(ASSETPATH . "/xml/layouts.xml", $xml);
	}
	
	/**
	* Builds all of the grid by running each of the
	* functions in order. It's just a shortcut.
	*
	* @param   string   css file string
	* @return  string	css file string
	*/
	public function buildGrid($css) 
	{	
		// Generate the grid.png
		//$this->generateGridImage();
		
		// Create the layouts xml for use with the tests
		$this->generateLayoutXML($css);
		
		// Replace the grid() variables
		$css = $this->replaceGridVariables($css);
	
		// Replace the columns:; properties
		$css = $this->replaceColumns($css);

		return $css;
	}
	 
	/**
	* Builds the columns:x; properties
	*
	* @param   string   css file string
	* @return  string	css file string
	*/
	public function replaceColumns($css)
	{
		global $cc, $cw, $gw, $bl, $gridw;
		
		// We'll loop through each of the columns properties by looking for each columns:x; property.
		// This means we'll only loop through $columnscount number of times which could be better
		// or worse depending on how many columns properties there are in your css
		
		for ($i=1; $i <= $cc; $i++) { 
		
			// Matches all selectors (just the properties) which have a columns property
			while (preg_match_all('/\{([^\}]*(columns\:\s*('.$i.'!?)\s*\;).*?)\}/sx', $css, $match)) {
			
				// For each of the selectors with columns properties...
				foreach ($match[0] as $key => $properties)
				{
					$styles = "";
					
					$properties 		= $match[1][0]; // First match is all the properties				
					$columnsproperty 	= $match[2][0]; // Second match is just the columns property
					$numberofcolumns	= $match[3][0]; // Third match is just number of columns
					
					// If there is an ! after the column number, we don't want the properties included.
					$showproperties = (substr($numberofcolumns, -1) == "!") ? false : true;

					// Calculate the width of the column
					$width = (($cw*$i)-($gw*2));
					
					// If the browser doesn't support box-sizing, minus the padding and border
					// We'll see if the flags have been set from the browser plugin
					if
					(
						(isset(CSScaffold::$flags['Internet Explorer']) && Core::user_agent('version') < 8) ||
						Core::user_agent('version') == ""
					)
					{		
						// Send the properties through the functions to get the padding and border from them  
						$padding = $this->getPadding($properties);
						$border = $this->getBorder($properties);
								
						// Calculate the width of the column with adjustments for padding and border
						$width = $width - ($padding + $border);
					}
					
					// Otherwise, the browser is a *good* browser and supports it. So we'll use that instead. 
					else
					{
						if ( CSScaffold::$flags['Safari'] )
						{
							$styles .= "box-sizing:border-box;-webkit-box-sizing:border-box;";
						}
						
						elseif ( CSScaffold::$flags['Firefox'] )
						{
							$styles .= "box-sizing:border-box;-moz-box-sizing:border-box;";
						}
						
						elseif ( CSScaffold::$flags['Opera'] )
						{
							$styles .= "box-sizing:border-box;";
						}
						
						elseif ( CSScaffold::$flags['Internet Explorer'] )
						{
							$styles .= "box-sizing:border-box;-ms-box-sizing:border-box;";
						}
					}
										
					// Create the width property
					$styles .= "width:" . $width . "px;";
					
					if ($showproperties) 
					{
						$styles .= "float:left;"; 
						
						// Fix up the retarded bugs in IE
						if(CSScaffold::$flags['Internet Explorer'] === true && Core::user_agent('version') < 7)
						{
							$styles .= "display:inline;overflow:hidden;";
						}
						
						// Add the gutters
						$styles .= "margin-right:" . $gw . "px; margin-left:".$gw."px;";
					}
					
					// Insert into property string
					$newproperties = str_replace($columnsproperty, $styles, $properties);

					// Insert this new string into CSS string
					$css = str_replace($properties, $newproperties, $css);
				
				}
			}
		}
		return $css;
	}
	
	/**
	* Removes the @grid settings from the css string
	*
	* @param   string   css file string
	* @return  string	css file string
	*/
	public function removeSettings($css)
	{
		return preg_replace('/\@grid\s*\{.*?\}/sx', '', $css);
	}

	/**
	* Replaces all of the grid() variables
	*
	* @param   string   css file string
	* @return  string	css file string
	*/
	public function replaceGridVariables($css) 
	{
		global $cc, $cw, $gw, $bl, $gridw;
	
		// Replace grid(xcol)
		if (preg_match_all('/grid\((\d+)?col\)/', $css, $matches))
		{
			foreach ($matches[1] as $key => $number)
			{
				$colw = ($number * $cw) - $gw .'px';		
				$css = str_replace($matches[0][$key],$colw,$css);
			}
		}
		
		// Replace grid(max)
		$css = str_replace('grid(max)',$gridw ."px",$css);
		
		// Replace grid(baseline)	
		$css = str_replace('grid(baseline)', $bl . "px" , $css);
		
		// Replace grid(gutter)
		$css = str_replace('grid(gutter)', $gw.'px', $css);
		
		// Send it all back
		return $css;
	}

	/**
	* Gets a param from the @grid settings
	*
	* @param   string   item name
	* @param   string   The @grid settings string
	* @return  string	The value of the item
	*/
	private function getParam($name, $gridsettings)
	{		
		// Make the settings regex-friendly
		$name = str_replace('-','\-', $name);
		
		if (preg_match_all('/'.$name.'\:.+?\;/x', $gridsettings, $matches))
		{
			// Strip the name and leave the value so the value can be anything
			$result = preg_replace('/'.$name.'|\:|\;| /', '', $matches[0][0]);
			
			// Remove quotes
			$result = preg_replace('/\'|\"/', '', $result);
			
			return $result;
		}
	}

	/**
	* Calculates the total amount of padding present
	* in a selector. This doesn't factor in cascading.
	*
	* @param   string   All of the properties of a selector
	* @return  string	The total amount of left and right padding combined
	*/
	private function getPadding($properties)
	{
		$padding = $paddingleft = $paddingright = 0;
		
		// Get the padding (in its many different forms)

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
	* @todo Look for more variations in the border. This could still possibly break IE6/7 because they doesn't use box-sizing. 
	*/
	private function getBorder($properties)
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

}
