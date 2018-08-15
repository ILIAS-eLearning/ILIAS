<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceBaseTest
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class ilTermsOfServiceBaseTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @inheritdoc
	 */
	protected function setUp()
	{
		$GLOBALS['DIC'] = new \ILIAS\DI\Container();

		parent::setUp();
	}


	/**
	 * @param string $exceptionClass
	 */
	protected function assertException(string $exceptionClass)
	{
		if (version_compare(\PHPUnit_Runner_Version::id(), '5.0', '>=')) {
			$this->setExpectedException($exceptionClass);
		}
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 */
	protected function setGlobalVariable(string $name, $value)
	{
		global $DIC;

		$GLOBALS[$name] = $value;

		unset($DIC[$name]);
		$DIC[$name] = function ($c) use ($name) {
			return $GLOBALS[$name];
		};
	}
}