<?php

class Test_Environment extends Scaffold_Test
{
	function test_document_root()
	{
		$this->assertTrue(realpath($_SERVER['DOCUMENT_ROOT']));
	}
}