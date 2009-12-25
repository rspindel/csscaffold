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
	$result = false;
	$expression = str_replace( array('px','em'), '', $expression);

	$E = error_reporting(0);
	$result = eval("return $expression;");
	error_reporting($E);
	
	return ($result) ? $result : false;
}