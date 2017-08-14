<?php

/**
 * Class ilWorkflowEngineBaseTest
 */
abstract class ilWorkflowEngineBaseTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @param string $name
	 * @param mixed $value
	 */
	protected function setGlobalVariable($name, $value)
	{
		global $DIC;

		$GLOBALS[$name] = $value;

		unset($DIC[$name]);
		$DIC[$name] = function ($c) use ($name) {
			return $GLOBALS[$name];
		};
	}

	/**
	 * 
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->setGlobalVariable('ilDB', $this->getMockBuilder('ilDBInterface')->getMock());
	}
}