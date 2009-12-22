<?php

# Get the global include
require_once './_files/_inc.php';

$css_dir = dirname(__FILE__) . '/_files/Layout/';
CSScaffold::cache_set( $css_dir . 'cache/' );

$list = array();

// build test file list
$d = dir($css_dir);
while (false !== ($entry = $d->read())) 
{
    if (preg_match('/cache|_out\.css$/', $entry, $m) || $entry[0] == ".") 
    {
     	continue;
    }
    else
    {
		$list[] = $entry;
    }
}
$d->close();

foreach($list as $item)
{ 
	$item = str_replace('.css','',$item);
	$original = file_get_contents( $css_dir . "/{$item}.css");
	$expected = Minify::compress(file_get_contents( $css_dir . "/{$item}_out.css"));
	
	$css = Layout::parse($original);
	$css = Minify::compress($css);
	
	$css = preg_replace('#\s+$#','',$css);
	
	$passed = assertTrue($expected === $css, 'Layout: ' . $item);
	
	if (__FILE__ === realpath($_SERVER['SCRIPT_FILENAME']))
	{		
		if (!$passed) 
		{
			echo "\n---Output: " .strlen($css). " bytes\n\n{$css}\n\n";
		    echo "---Expected: " .strlen($expected). " bytes\n\n{$expected}\n\n";
		    echo "---Source: " .strlen($original). " bytes\n\n{$original}\n\n\n";    
		}
	}
}