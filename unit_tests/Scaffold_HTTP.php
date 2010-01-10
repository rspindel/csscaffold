<?php

include '_load.php';

class HTTPTests extends UnitTestCase
{
	var $config;

	function loadConfig()
	{
		include '../scaffold/config.php';
		$config['system']  = realpath('../scaffold/') . '/';
		$config['cache']   = $config['system'] . 'cache/';
		$this->config = $config;
		return true;
	}

	function testHeaders()
	{
		$files = array('/unit_tests/_files/HTTP/standard.css');
		$options = array();
		$this->loadConfig();

		$result = Scaffold::parse($files,$this->config,$options,true);
		
		$this->assertNotNull( $result['headers']['Expires'] ); 
		$this->assertNotNull( $result['headers']['Cache-Control'] ); 
		$this->assertNotNull( $result['headers']['Content-Type'] );
		$this->assertNotNull( $result['headers']['Last-Modified'] );
		$this->assertNotNull( $result['headers']['Content-Length'] );
		$this->assertNotNull( $result['headers']['ETag'] );
		
		// Make sure the content length is set
		$this->assertNotEqual( strlen($result['headers']['Content-Length']), 0);
	}
	
	// The CSS file has not been modified
	function testProductionNotModified()
	{
		$files = array('/unit_tests/_files/HTTP/standard.css');
		$options = array();
		$this->loadConfig();
		$this->config['in_production'] = true;
		
		// Parse it once to make sure the file is already cached
		Scaffold::parse($files,$this->config,$options,true);
		
		// Parse it again. It hasn't been modified so it should return the correct headers
		$result = Scaffold::parse($files,$this->config,$options,true);
		
		print_r($result);

		// Make sure the response code is corrent
		$this->assertTrue( strstr($result['headers']['_responseCode'],'304 Not Modified') ); 
	}
	
	// The CSS file has been modified
	function testProductionModified()
	{
	
	}

	function testDevelopmentNotModified()
	{
	
	}
	
	function testDevelopmentModified()
	{
	
	}
}