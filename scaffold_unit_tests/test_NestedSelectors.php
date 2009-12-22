<?php

# Get the global include
require_once './_files/_inc.php';

$css_dir = dirname(__FILE__) . '/_files/NestedSelectors/';

CSScaffold::add_include_path( $css_dir );
CSScaffold::config_set('current.path', $css_dir);

$original = file_get_contents( $css_dir . 'in.css');
$expected = file_get_contents( $css_dir . 'out.css');

$css = NestedSelectors::parse($original);

$passed = assertTrue($expected === $css, 'Nested Selectors');

if (__FILE__ === realpath($_SERVER['SCRIPT_FILENAME']) || $passed == false)
{
	echo "\n---Output: " .strlen($css). " bytes\n\n{$css}\n\n";
	
	if (!$passed) 
	{
	    echo "---Expected: " .strlen($expected). " bytes\n\n{$expected}\n\n";
	    echo "---Source: " .strlen($original). " bytes\n\n{$original}\n\n\n";    
	}
}

CSScaffold::remove_include_path(dirname(__FILE__));