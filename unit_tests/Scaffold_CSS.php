<?php

require_once('simpletest/autorun.php');
require_once('../scaffold/libraries/Scaffold/CSS.php');

class CSSUtilityTests extends UnitTestCase
{
	function testCompress()
	{
		$before = file_get_contents( dirname(__FILE__) . '/_files/general.css');
		$after = Scaffold_CSS::compress($before);
		$expected = "";

		$this->assertEqual($after,$expected);
	}
}