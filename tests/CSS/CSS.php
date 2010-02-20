<?php

class Test_CSS extends Scaffold_Test
{
	/**
	 * Scaffold_CSS instance
	 *
	 * @var object
	 */
	private $css;

	function setUp()
	{
		parent::init(__FILE__);	
		
		$this->css = new Scaffold_CSS( $this->find('01.css') );
	}
	
	function test_find_functions()
	{
		$found = $this->css->find_functions('test');

		// Make sure it's an array
		$this->assertTrue( is_array($found) );
	}
}