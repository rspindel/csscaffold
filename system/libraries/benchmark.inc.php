<?php if (!defined('CSS_CACHEER')) { header('Location:/'); }

$time = array();

function mark($label)
{
	global $time;
	
	$time[$label] = microtime();
}

function elapsed_time($label1, $label2)
{
	global $time; 
	
	list($sm, $ss) = explode(' ', $time[$label1]);
	list($em, $es) = explode(' ', $time[$label2]);
	
	return ($em + $es) - ($sm + $ss);
}