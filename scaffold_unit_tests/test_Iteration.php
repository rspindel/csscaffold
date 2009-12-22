<?php

# Get the global include
require_once './_files/_inc.php';

$css_dir = dirname(__FILE__) . '/_files/Iteration/';

$original = file_get_contents( $css_dir . '/in.css');
$expected = Minify::compress(file_get_contents( $css_dir . '/out.css'));

$css = Minify::compress(Iteration::parse($original));

$passed = assertTrue($expected === $css, 'Iteration');

if (__FILE__ === realpath($_SERVER['SCRIPT_FILENAME']))
{
	echo "\n---Output: " .strlen($css). " bytes\n\n{$css}\n\n";
	
	if (!$passed) 
	{
	    echo "---Expected: " .strlen($expected). " bytes\n\n{$expected}\n\n";
	    echo "---Source: " .strlen($original). " bytes\n\n{$original}\n\n\n";    
	}
}