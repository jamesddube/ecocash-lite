<?php

class TestCase extends \PHPUnit_Framework_TestCase
{
    
    public function setUp()
	{
		Mockery::getConfiguration()->allowMockingMethodsUnnecessarily(false);
	}

	public function tearDown()
	{
		Mockery::close();
	}
}
?>