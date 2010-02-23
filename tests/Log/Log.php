<?php

class Test_Log extends Scaffold_Test
{
	/**
	 * The log instance
	 *
	 * @var object
	 */
	private $log;

	function setUp()
	{
		parent::init(__FILE__);	
		$this->log = Scaffold_Log::instance(false);
		$this->log->directory($this->dir . 'logs/');
	}
	
	function test_add()
	{
		// Add a new message
		$this->log->add('Test log message');
		
		// This should be in the messages array
		$this->assertEqual(count($this->log->messages()),1);
	}
	
	function test_save()
	{
		$this->log->save();

		// There should be 3 in the dir, including the 2 . paths
		$this->assertEqual(count(scandir($this->log->directory())),3);
	}
	
	function tearDown()
	{
		$dirs = $this->log->directory();
		
		foreach(scandir($dirs) as $dir)
		{
			if($dir[0] == '.')
				continue;

			unlink($this->log->directory() . '/' . $dir);
		}
	}
}