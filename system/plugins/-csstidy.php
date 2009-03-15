<?php if (!defined('CSS_CACHEER')) { header('Location:/'); }

/**
 * The class name
 * @var string
 */
$plugin_class = 'CSSTidyPlugin';

/**
 * Include the settings
 */
include $config['system_dir'] . '/config/plugins/csstidy.config.php';

/**
 * Include the CSSTidy class
 */
include $config['system_dir'] . '/classes/Csstidy.php';

/**
 * CSSTidyPlugin class
 *
 * @package csscaffold
 **/
class CSSTidyPlugin extends CacheerPlugin
{
	
	function post_process($css)
	{		
		global $options;
	
		$tidy = new csstidy();
						
		$tidy->set_cfg('preserve_css',$options[$plugin_class]['preserve_css']);
		$tidy->set_cfg('sort_selectors',$options[$plugin_class]['sort_selectors']);
		$tidy->set_cfg('sort_properties',$options[$plugin_class]['sort_properties']);
		$tidy->set_cfg('merge_selectors',$options[$plugin_class]['merge_selectors']);
		$tidy->set_cfg('optimise_shorthands',$options[$plugin_class]['optimise_shorthands']);
		$tidy->set_cfg('compress_colors',$options[$plugin_class]['compress_colors']);
		$tidy->set_cfg('compress_font-weight',$options[$plugin_class]['compress_font-weight']);
		$tidy->set_cfg('lowercase_s',$options[$plugin_class]['lowercase_s']);
		$tidy->set_cfg('case_properties',$options[$plugin_class]['case_properties']);
		$tidy->set_cfg('remove_bslash',$options[$plugin_class]['remove_bslash']);
		$tidy->set_cfg('remove_last_;',$options[$plugin_class]['remove_last_;']);
		$tidy->set_cfg('discard_invalid_properties',$options[$plugin_class]['discard_invalid_properties']);
		$tidy->set_cfg('css_level','CSS2.1');
		$tidy->set_cfg('time_stamp','false');
		
		$tidy->load_template('highest_compression');
		
		$result = $tidy->parse($css);
				
		$css = $tidy->print->plain();  
		
		return $css;
	}
} 


?>