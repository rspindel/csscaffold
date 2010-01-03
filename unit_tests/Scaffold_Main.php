<?php

require_once('simpletest/autorun.php');
require_once('../scaffold/libraries/Bootstrap.php');

class MainTests extends UnitTestCase
{
	function testSetupDevelopment()
	{
		include '../scaffold/config/Scaffold.php';
		
		$config['system']  = realpath('../scaffold/') . '/';
		$config['cache']   = $config['system'] . 'cache/';
		
		$this->assertTrue( CSScaffold::setup($config) );
		$this->assertFalse( CSScaffold::config('in_production') );
		$this->assertTrue( is_array(CSScaffold::include_paths()) );
		$this->assertTrue( is_array(CSScaffold::modules()) );
		
		foreach(CSScaffold::modules() as $module)
		{
			$this->assertTrue( class_exists($module) );
		}
	}
	
	function testSetupProduction()
	{
		include '../scaffold/config/Scaffold.php';
		
		$config['system']  = realpath('../scaffold/') . '/';
		$config['cache']   = $config['system'] . 'cache/';
		$config['in_production'] = true;

		$this->assertTrue( CSScaffold::setup($config) );
		$this->assertTrue( CSScaffold::config('in_production') );
		$this->assertTrue( is_array(CSScaffold::include_paths()) );
		$this->assertTrue( is_array(CSScaffold::modules()) );
		
		foreach(CSScaffold::modules() as $module)
		{
			$this->assertTrue( class_exists($module) );
		}
	}
	
	function testSetupCache()
	{
		include '../scaffold/config/Scaffold.php';
		
		$config['system']  = realpath('../scaffold/') . '/';
		$config['cache']   = $config['system'] . 'cache/';
		$config['in_production'] = true;
		
		$cache = new Scaffold_Cache
		(
			$config['cache'],
			$config['cache_lifetime'],
			$config['in_production'] 
		);
		
		$this->assertTrue( $cache->exists('scaffold_include_paths.txt') );
		$this->assertTrue( $cache->exists('scaffold_modules.txt') );
	}
	
	function testSetupNoConfig()
	{	
		$config['system']  = realpath('../scaffold/') . '/';
		$config['cache']   = $config['system'] . 'cache/';
		$config['in_production'] = false;
		
		$this->assertTrue( CSScaffold::setup($config) );
		$this->assertFalse( CSScaffold::config('in_production') );
		$this->assertTrue( is_array(CSScaffold::include_paths()) );
		$this->assertTrue( is_array(CSScaffold::modules()) );
		
		foreach(CSScaffold::modules() as $module)
		{
			$this->assertTrue( class_exists($module) );
		}
	}
	
	function testUrlPath()
	{
		$this->assertEqual( CSScaffold::url_path(dirname(__FILE__)), '/unit_tests');
		$this->assertEqual( CSScaffold::url_path(dirname(__FILE__) . '/..'), '/');
		$this->assertEqual( CSScaffold::url_path(dirname(__FILE__) . '/../scaffold/'), '/scaffold');
	}
	
	function testParse()
	{
		include '../scaffold/config/Scaffold.php';
		$config['system']  = realpath('../scaffold/') . '/';
		$config['cache']   = realpath($config['system'] . 'cache/');

		CSScaffold::setup($config);
		
		// Single files
		$files = array('/unit_tests/_files/Misc/general.css');
		$options = array();
		$this->assertNotNull( CSScaffold::parse($files,$options,true) );
		
		// Multiple Files
		$files = array(
			'/unit_tests/_files/Misc/general.css',
			'/unit_tests/_files/Misc/minified.css'
		);
		$options = array();
		$this->assertNotNull( CSScaffold::parse($files,$options,true) );
		
		// Same file twice
		$files = array(
			'/unit_tests/_files/Misc/general.css',
			'/unit_tests/_files/Misc/general.css'
		);
		$options = array();
		$this->assertNotNull( CSScaffold::parse($files,$options,true) );
		
		// Via a url
		$files = array(
			'http://scaffold/unit_tests/_files/Misc/general.css'
		);
		$options = array();
		//$this->assertError( CSScaffold::parse($files,$options,true) );
		
		/*
		
		$files = array('/unit_tests/_files/Misc/selectors.css');
		$options = array();
		$this->assertNotNull( CSScaffold::parse($files,$options,true) );
		
		$files = array('/unit_tests/_files/Misc/hacks.css');
		$options = array();
		$this->assertNotNull( CSScaffold::parse($files,$options,true) );
		
		$files = array('/unit_tests/_files/Misc/styles.css');
		$options = array();
		$this->assertNotNull( CSScaffold::parse($files,$options,true) );
		
		$files = array('/unit_tests/_files/Misc/unusual_strings.css');
		$options = array();
		$this->assertNotNull( CSScaffold::parse($files,$options,true) );
		
		// Multiple Files
		$files = array(
			'/unit_tests/_files/Misc/general.css',
			'/unit_tests/_files/Misc/minified.css',
			'/unit_tests/_files/Misc/selectors.css',
			'/unit_tests/_files/Misc/hacks.css',
			'/unit_tests/_files/Misc/styles.css',
			'/unit_tests/_files/Misc/unusual_strings.css'
		);
		$options = array();
		$this->assertNotNull( CSScaffold::parse($files,$options,true) );
		*/
		
	}
}