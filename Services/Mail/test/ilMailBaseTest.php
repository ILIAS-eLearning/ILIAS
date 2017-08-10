<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'libs/composer/vendor/autoload.php';

/**
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class ilMailBaseTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * 
	 */
	protected function setUp()
	{
		$GLOBALS['DIC'] = new \ILIAS\DI\Container();

		parent::setUp();
	}

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
}