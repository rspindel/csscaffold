<?php

# Get the global include
require_once './_files/_inc.php';

$css_dir = dirname(__FILE__) . '/_files/Import/';

$original = file_get_contents( $css_dir . '/in.css');
$expected = file_get_contents( $css_dir . '/out.css');

CSScaffold::config_set('current.path', $css_dir);
CSScaffold::config_set('current.file', $css_dir . 'in.css' );
CSScaffold::add_include_path( $css_dir );
$css = Import::parse($original);

$passed = assertTrue($expected === $css, 'Import');

if (__FILE__ === realpath($_SERVER['SCRIPT_FILENAME']))
{
	echo "\n---Output: " .strlen($css). " bytes\n\n{$css}\n\n";
	
	if (!$passed) 
	{
	    echo "---Expected: " .strlen($expected). " bytes\n\n{$expected}\n\n";
	    echo "---Source: " .strlen($original). " bytes\n\n{$original}\n\n\n";    
	}
}