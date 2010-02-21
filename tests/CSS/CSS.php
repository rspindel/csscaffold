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
	}
	
	function css($file)
	{
		$this->css = new Scaffold_CSS( $this->find($file.'.css') );
	}
	
	function test_find_functions()
	{
		$this->css('functions');

		// Simple test
		$found = $this->css->find_functions('test');
		$this->assertTrue(is_array($found));
		$this->assertEqual($found[0][0],'test(value)');
		$this->assertEqual($found[2][0],'value');
		
		// There are multiple test functions
		$found = $this->css->find_functions('test2');
		$this->assertEqual(count($found[0]),2);
		$this->assertEqual($found[2][1],'value2');
		
		// There are multiple params
		$found = $this->css->find_functions('test3');
		$this->assertEqual($found[2][0],'value,value,value');
		
		// There are brackets inside the params
		$found = $this->css->find_functions('test4');
		$this->assertEqual($found[2][0],'value,value(another),value');
	}
	
	function test_find_at_group()
	{
		$this->css('groups');
		
		// Standard group
		$found = $this->css->find_at_group('identifier');
	}
	
	function test_find_selectors_with_property()
	{
		$this->css('standard');
	}
	
	function test_find_properties_with_value()
	{
		$this->css('standard');
	}
	
	function test_find_selectors()
	{
		$this->css('standard');
	}
	
	function test_find_property()
	{
		$this->css('standard');
	}
	
	function test_selector_exists()
	{
		$this->css('standard');
	}
	
	function test_remove_properties()
	{
		$this->css('standard');
	}
	
	function test_convert_entities()
	{
		$this->css('standard');
	}
}