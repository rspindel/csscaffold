<?php

# Get the global include
require_once './_files/_inc.php';

$css_dir = dirname(__FILE__) . '/_files/Gradient/';

$original = file_get_contents( $css_dir . '/in.css');
$expected = file_get_contents( $css_dir . '/out.css');

CSScaffold::cache_set( $css_dir . 'cache/' );

$css = Gradient::post_process($original);

$passed = assertTrue($expected === $css, 'Gradient');

if (__FILE__ === realpath($_SERVER['SCRIPT_FILENAME']))
{
	echo "\n---Output: " .strlen($css). " bytes\n\n{$css}\n\n";
	
	if (!$passed) 
	{
	    echo "---Expected: " .strlen($expected). " bytes\n\n{$expected}\n\n";
	    echo "---Source: " .strlen($original). " bytes\n\n{$original}\n\n\n";    
	}
}