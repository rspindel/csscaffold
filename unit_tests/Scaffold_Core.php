<?php

include '_load.php';

class CoreTests extends UnitTestCase
{
	function testSetFlag()
	{
		$this->assertTrue( Scaffold::flag_set('Flag') );
	}
	
	function testLogThreshold()
	{
		Scaffold_Core::log_threshold(4);
		Scaffold_Core::log('Foo',4);
		Scaffold_Core::log('bar',3);
		Scaffold_Core::log('bar',2);
		Scaffold_Core::log('bar',1);
		$this->assertEqual(count(Scaffold_Core::$log),4);
		
		Scaffold_Core::$log = array();
		
		Scaffold_Core::log_threshold(3);
		Scaffold_Core::log('Foo',4);
		Scaffold_Core::log('bar',3);
		Scaffold_Core::log('bar',2);
		Scaffold_Core::log('bar',1);
		$this->assertEqual(count(Scaffold_Core::$log),3);
		
		Scaffold_Core::$log = array();
		
		Scaffold_Core::log_threshold(2);
		Scaffold_Core::log('Foo',4);
		Scaffold_Core::log('bar',3);
		Scaffold_Core::log('bar',2);
		Scaffold_Core::log('bar',1);
		$this->assertEqual(count(Scaffold_Core::$log),2);
		
		Scaffold_Core::$log = array();
		
		Scaffold_Core::log_threshold(1);
		Scaffold_Core::log('Foo',4);
		Scaffold_Core::log('bar',3);
		Scaffold_Core::log('bar',2);
		Scaffold_Core::log('bar',1);
		$this->assertEqual(count(Scaffold_Core::$log),1);
	}
	
	function testLogDirectory()
	{
		$this->assertEqual(Scaffold_Core::log_directory(),'/Library/WebServer/Documents/_projects/CSScaffold/scaffold/logs/');
	}
}