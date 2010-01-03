<?php

include '_load.php';

class ModulesTests extends UnitTestCase
{
	function dir($name = false)
	{
		static $directory;

		if($name)
		{
			$directory =  dirname(__FILE__) . '/_files/'.$name.'/';
			CSScaffold::$current['path'] = $directory;
		}
		return $directory;
	}

	function test_Absolute_Urls()
	{
		$this->dir('Absolute_Urls');
		$original = file_get_contents( $this->dir() . 'original.css');
		$expected = file_get_contents( $this->dir() . 'expected.css');
		$css = Absolute_Urls::rewrite($original);
		$this->assertEqual($css,$expected);
	}

	function test_Constants()
	{
		$this->dir('Constants');
		$original = file_get_contents( $this->dir() . 'in.css');
		$expected = file_get_contents( $this->dir() . 'out.css');
		$css = Constants::parse($original);
		$css = Constants::replace($css);
		$this->assertEqual($css,$expected);
	}
	
	function test_Flags()
	{
		$this->dir('Flags');
		CSScaffold::flag_set('flag2');
		CSScaffold::flag_set('flag3');
		$original = file_get_contents( $this->dir() . 'in.css');
		$expected = file_get_contents( $this->dir() . 'out.css');
		$css = Flags::post_process($original);
		$this->assertEqual($css,$expected);
	}
	
	function test_Import()
	{
		$this->dir('Import');
		$original = file_get_contents( $this->dir() . 'in.css' );
		$expected = file_get_contents( $this->dir() . 'out.css' );
		CSScaffold::$current['file'] = $this->dir() . 'in.css';
		CSScaffold::add_include_path( $this->dir() );
		$css = Import::parse($original);
		$this->assertEqual($expected,$css);
	}
	
	function test_Iteration()
	{
		$this->dir('Iteration');
		$original = file_get_contents( $this->dir() . 'in.css');
		$expected = file_get_contents( $this->dir() . 'out.css');
		$css = Iteration::parse($original);
		$this->assertEqual($css,$expected);
	}
	
	function test_Mixins()
	{
		$this->dir('Mixins');
		$list = array();

		$d = dir($this->dir());
		while (false !== ($entry = $d->read())) 
		{
		    if (preg_match('/_out\.css$/', $entry, $m) || $entry[0] == ".") 
		     	continue;
		     	
			$list[] = $entry;
		}
		$d->close();
		
		foreach($list as $item)
		{ 
			$item = str_replace('.css','',$item);
			$original = file_get_contents( $this->dir() . "/{$item}.css");
			$expected = file_get_contents( $this->dir() . "/{$item}_out.css");
			
			$css = Mixins::parse($original);
			
			$css = Minify::compress($css);
			$expected = Minify::compress($expected);
			
			$this->assertEqual($expected,$css);
		}
	}

	function test_Nested_Selectors()
	{
		$this->dir('NestedSelectors');
		$original = file_get_contents( $this->dir() . 'in.css');
		$expected = file_get_contents( $this->dir() . 'out.css');
		$css = NestedSelectors::parse($original);
		$this->assertEqual($css,$expected);
	}

}