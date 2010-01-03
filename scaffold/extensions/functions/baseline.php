<?php

/**
 * x number of baselines. Quicker way of doing calc($baseline * n)
 *
 * @param $num
 * @return string
 */
function Scaffold_baseline($num)
{
	if( isset(Layout::$baseline) )
	{
		return (Layout::$baseline * $num) . Layout::$unit;
	}

	return false;
}