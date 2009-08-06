<?php defined('BASEPATH') OR die('No direct access allowed.');

/**
 * Icy compressor class
 *
 **/
class Icy extends Plugins
{
	/**
	 * Formatting process
	 *
	 * @author Anthony Short
	 * @return null
	 */
	public function formatting_process()
	{
		$css =& CSS::$css;
		
		$css = CSS::convert_entities('encode', $css);
	    
	    # remove html and css comments    
	    Icy_compressor::kill_comments($css);
	    
	    # trim whitespace from the start and end
	    $css = trim($css);
	    
	    # turn all rgb values into hex
	    if (Config::get('rgbtohex', 'Icy')) 
	    {
	    	Icy_compressor::rgb2hex($css);
	    }
	    
	    # shorten colours
	    if (Config::get('colors2hex', 'Icy')) 
	    {
	    	Icy_compressor::long_colours_to_short_hex($css);
	    }
	    
	    if (Config::get('hex2colors', 'Icy'))
	    {   
	        Icy_compressor::long_hex_to_short_colours($css);
	    }
	    
	    # remove any extra measurements that aren't needed
	    if (Config::get('remove_zeros', 'Icy'))
	    {
	        Icy_compressor::remove_zero_measurements($css);
	    }
	        
	    # seperate into selectors and properties
	    Icy_compressor::sort_css($css);
	    
	    # Change font weight text into numbers
	    if (Config::get('text_weights_to_numbers', 'Icy'))
	    {
	        Icy_compressor::font_weight_text_to_numbers($css);
	    }
	    
	    # Check if any selectors are used twice and combine the properties
	    if (Config::get('combine_identical_selectors', 'Icy'))
	    {
	        Icy_compressor::combine_identical_selectors();
	    }
	    
	    # remove any properties which are declared twice in one rule
	    if (Config::get('remove_overwritten_properties', 'Icy'))
	    {
	        Icy_compressor::remove_overwritten_properties();
	    }
	        
	    # check if properties should be combined
	    if (Config::get('combine_props_list', 'Icy'))
	    {
	        for ($n = 0; $n < count(Icy_compressor::$file_props); $n++)
	        {
	            # attempt to combine the different parts
	            Icy_compressor::combine_props_list(Icy_compressor::$file_props[$n]);    
	        }
	    }
	    
	    # reduces six hex codes to three (#ff0000 -> #f00)
	    if (Config::get('short_hex', 'Icy'))
	    {
		    for ($n = 0; $n < count(Icy_compressor::$file_props); $n++)
		    {
		        # run all the individual functions to reduce their size
		        array_walk(Icy_compressor::$file_props[$n], 'Icy_compressor::short_hex');
		    }
	    }
	    
	    # removes useless values from padding and margins (margin: 4px 5px 4px 5px -> margin: 4px 5px)
	    if (Config::get('short_margins_and_paddings'))
	    {
		    for ($n = 0; $n < count(Icy_compressor::$file_props); $n++)
		    {
		        # run all the individual functions to reduce their size
		        array_walk(Icy_compressor::$file_props[$n], 'Icy_compressor::compress_padding_and_margins');
		    }
	    }
	        
	    # Remove all the properties that were blanked out earlier
	    Icy_compressor::remove_empty_rules();
	    
	    # check if any rules are the same as other ones and remove the first ones
	    if (Config::get('combine_identical_rules', 'Icy'))
	    {
	        Icy_compressor::combine_identical_rules();
	    }
	    
	    # one final run through to remove all unnecessary parts of the arrays
	    Icy_compressor::remove_empty_rules();
	    
	    $css = "";
	    
	    for ($a = 0; $a < count(Icy_compressor::$file_selector); $a++)
        {
            for ($b = 0; $b < count(Icy_compressor::$file_selector[$a]); $b++)
            {
               Icy_compressor::$file_selector[$a][$b] = Icy_compressor::$file_selector[$a][$b];
            }
            
            for ($b = 0; $b < count(Icy_compressor::$file_props[$a]); $b++)
            {
                $parts = explode(':', Icy_compressor::$file_props[$a][$b]);
                Icy_compressor::$file_props[$a][$b] = '' . $parts[0] . ':' . $parts[1];        
            }    

            $css .= implode(',', Icy_compressor::$file_selector[$a]) . '{';
            $css .= implode(';', Icy_compressor::$file_props[$a]) . '}';
        }
        
        $css = CSS::convert_entities('decode', $css);
	    $css = stripslashes($css);
	}
	 

}