<?php
/******************************************************************************
 Prevent direct access
 ******************************************************************************/
if (!defined('CSS_CACHEER')) { header('Location:/'); }

require('system/plugins/csstidy/class.csstidy.php');

$plugin_class = 'CSSTidyPlugin';


class CSSTidyPlugin extends CacheerPlugin
{
	
	function post_process($css)
	{		
		global $tidy_options;
	
		$tidy = new csstidy();
		
		$tidy->set_cfg('preserve_css',$tidy_options['preserve_css']);
		$tidy->set_cfg('sort_selectors',$tidy_options['sort_selectors']);
		$tidy->set_cfg('sort_properties',$tidy_options['sort_properties']);
		$tidy->set_cfg('merge_selectors',$tidy_options['merge_selectors']);
		$tidy->set_cfg('optimise_shorthands',$tidy_options['optimise_shorthands']);
		$tidy->set_cfg('compress_colors',$tidy_options['compress_colors']);
		$tidy->set_cfg('compress_font-weight',$tidy_options['compress_font-weight']);
		$tidy->set_cfg('lowercase_s',$tidy_options['lowercase_s']);
		$tidy->set_cfg('case_properties',$tidy_options['case_properties']);
		$tidy->set_cfg('remove_bslash',$tidy_options['remove_bslash']);
		$tidy->set_cfg('remove_last_;',$tidy_options['remove_last_;']);
		$tidy->set_cfg('discard_invalid_properties',$tidy_options['discard_invalid_properties']);
		$tidy->set_cfg('css_level',$tidy_options['css_level']);
		$tidy->set_cfg('timestamp',$tidy_options['timestamp']);
		
		$tidy->load_template($tidy_options['template']);
		
		$result = $tidy->parse($css);
		
		$css = $tidy->print->plain(); 
		
		return $css;
	}
}

?>