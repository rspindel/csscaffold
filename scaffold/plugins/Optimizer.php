<?php defined('BASEPATH') OR die('No direct access allowed.');

require join_path(BASEPATH,'libraries/CSSTidy.php');

/**
 * CSSTidyPlugin
 *
 * @package csscaffold
 * @dependencies CSSTidy
 **/
class Optimizer extends Plugins
{
	function formatting_process()
	{				
		if(Config::get('tidy') === true)
		{
			$tidy = new csstidy();
								
			$tidy->set_cfg('preserve_css',false);
			$tidy->set_cfg('sort_selectors',false);
			$tidy->set_cfg('sort_properties',true);
			$tidy->set_cfg('merge_selectors',2);
			$tidy->set_cfg('optimise_shorthands',1);
			$tidy->set_cfg('compress_colors',true);
			$tidy->set_cfg('compress_font-weight',false);
			$tidy->set_cfg('lowercase_s',true);
			$tidy->set_cfg('case_properties',1);
			$tidy->set_cfg('remove_bslash',false);
			$tidy->set_cfg('remove_last_;',true);
			$tidy->set_cfg('discard_invalid_properties',false);
			$tidy->set_cfg('css_level','CSS2.1');
			$tidy->set_cfg('time_stamp','false');
			
			$tidy->load_template('highest_compression');
			
			$result = @$tidy->parse(CSS::$css);
					
			CSS::$css = $tidy->print->plain(); 
		}
	}
} 
