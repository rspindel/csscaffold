<?php

# Get the global include
require_once './_files/_inc.php';

$css_dir = dirname(__FILE__) . '/_files/Constants/';

$original = file_get_contents( $css_dir . '/in.css');
$expected = file_get_contents( $css_dir . '/out.css');

Scaffold_Benchmark::start('Constants');

$css = Constants::parse($original);
$css = Constants::replace($css);

Scaffold_Benchmark::stop('Constants');

$time = Scaffold_Benchmark::get('Constants', 'time');

$passed = assertTrue($expected === $css, 'Constants');

if (__FILE__ === realpath($_SERVER['SCRIPT_FILENAME']))
{
	echo "\n---Output: " .strlen($css). " bytes  |  Time: {$time}\n\n{$css}\n\n";
	
	if (!$passed) 
	{
	    echo "---Expected: " .strlen($expected). " bytes\n\n{$expected}\n\n";
	    echo "---Source: " .strlen($original). " bytes\n\n{$original}\n\n\n";    
	}
}