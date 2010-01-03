<?php

/**
 * Round a number to the nearest baseline multiple.
 * Requires the layout module (@grid rule)
 *
 * @param $num
 * @return string
 */
function Scaffold_baseline_round($num)
{
	if(isset(Layout::$baseline))
	{
		$baseline = Layout::$baseline;
		return round($num/$baseline)*$baseline."px";
	}
	else
	{
		return false;
	}
}