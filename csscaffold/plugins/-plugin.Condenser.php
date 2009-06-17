<?php defined('BASEPATH') OR die('No direct access allowed.');

/**
 * CondenserPlugin class
 *
 * @package Cacheer
 **/
class Condenser extends Plugins
{
	function post_process($css)
	{
		$css = trim(preg_replace('#/\*[^*]*\*+([^/*][^*]*\*+)*/#', '', $css)); // comments
		$css = preg_replace('#\s+(\{|\})#', "$1", $css); // before
		$css = preg_replace('#(\{|\}|:|,|;)\s+#', "$1", $css); // after
		return $css;
	}
}