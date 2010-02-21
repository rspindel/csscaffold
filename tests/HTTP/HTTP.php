<?php

class HTTPTests extends Scaffold_Test
{	
	function setUp()
	{
		parent::init(__FILE__);
	}

	function test_nolifetime()
	{
		$headers = Scaffold::headers($this->find('01.css'),0);
		$now = time();
		$this->assertEqual($headers['Cache-Control'],'max-age=0');
		$this->assertEqual(strtotime($headers['Expires']),$now);
	}
	
	function test_tensecondlifetime()
	{
		$headers = Scaffold::headers($this->find('01.css'),10);
		$now = time();
		$this->assertEqual($headers['Cache-Control'],'max-age=10');
		$this->assertEqual(strtotime($headers['Expires']),$now + 10);
	}

	function test_etag()
	{
		file_put_contents($this->find('01.css'), '');
		$headers = Scaffold::headers($this->find('01.css'),0);
		
		file_put_contents($this->find('01.css'), time());
		$new_headers = Scaffold::headers($this->find('01.css'),0);
		
		$this->assertNotEqual($headers['ETag'],$new_headers['ETag']);
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