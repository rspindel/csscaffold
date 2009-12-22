<?php

/**
 * Parses the input as PHP. Used for perform math operations.
 *
 * @author Anthony Short
 * @param $math
 * @return string
 */
function Scaffold_calc($expression)
{
	$result = eval("return $expression;");
	
	if($result !== false)
	{
		return $result;
	}
	else
	{
		return false;
	}
}