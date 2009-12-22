<?php

# Get the global include
require_once './_files/_inc.php';

$dir = dirname(__FILE__) . '/_files/Utils/';

$list = array();

// build test file list
$d = dir($dir);
while (false !== ($entry = $d->read())) 
{
    if (preg_match('/_out\.txt$/', $entry, $m) || $entry[0] == ".") 
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
	$item = str_replace('.txt','',$item);
	$original = file_get_contents( $dir . "/{$item}.txt");
	$expected = file_get_contents( $dir . "/{$item}_out.txt");
	
	if($item == "is_image" || $item == "is_css")
	{
		$lines = explode("\n",$original);
		$expected_lines = explode("\n",$expected);
		
		foreach($lines as $key => $value)
		{
			$result = call_user_func(array('Scaffold_Utils', $item), $value);
			$passed = assertTrue($result == $expected_lines[$key], 'Utilities: '.$item.' : Line ' . ($key + 1));
			
			if (__FILE__ === realpath($_SERVER['SCRIPT_FILENAME']))
			{		
				if (!$passed) 
				{
					echo "\n---Output: " .strlen($result). " bytes\n\n{$result}\n\n";
				    echo "---Expected: " .strlen($expected_lines[$key]). " bytes\n\n{$expected_lines[$key]}\n\n";
				    echo "---Source: " .strlen($value). " bytes\n\n{$value}\n\n\n";    
				}
			}
		}
	}
	else
	{
		$string = call_user_func(array('Scaffold_Utils', $item), $original);
		$passed = assertTrue($expected === $string, 'Utilities: ' . $item);
		
		if (__FILE__ === realpath($_SERVER['SCRIPT_FILENAME']))
		{		
			if (!$passed) 
			{
				echo "\n---Output: " .strlen($string). " bytes\n\n{$string}\n\n";
			    echo "---Expected: " .strlen($expected). " bytes\n\n{$expected}\n\n";
			    echo "---Source: " .strlen($original). " bytes\n\n{$original}\n\n\n";    
			}
		}
	}
}