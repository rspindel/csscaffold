<?php

class HTTPTests extends Scaffold_Test
{	
	function setUp()
	{
		parent::init(__FILE__);
	}

	function test_set_header()
	{
		Scaffold::set_header('name','value');
	}
}


//		$files = array('/unit_tests/_files/HTTP/standard.css');
//		$options = array();
//		$this->loadConfig();
//
//		$result = Scaffold::parse($files,$this->config,$options,true);
//		
//		$this->assertNotNull( $result['headers']['Expires'] ); 
//		$this->assertNotNull( $result['headers']['Cache-Control'] ); 
//		$this->assertNotNull( $result['headers']['Content-Type'] );
//		$this->assertNotNull( $result['headers']['Last-Modified'] );
//		$this->assertNotNull( $result['headers']['Content-Length'] );
//		$this->assertNotNull( $result['headers']['ETag'] );
//		
		// Make sure the content length is set
//		$this->assertNotEqual( strlen($result['headers']['Content-Length']), 0);