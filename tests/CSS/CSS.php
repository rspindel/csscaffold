<?php

require_once(dirname(__FILE__) . '/../_load.php');

class CSSUtilityTests extends UnitTestCase
{
	function testRemoveComments()
	{	
		$css = new Scaffold_CSS(dirname(__FILE__) . '/01.css');

		$this->assertEqual( $css->remove_comments('/* Comment */'), '');
		$this->assertEqual( $css->remove_comments('/* http://www.google.com */'), '');
		$this->assertEqual( $css->remove_comments('/* /* */'), '');
		$this->assertEqual( $css->remove_comments("/* \n\n\r\t */"), '');
	}
}