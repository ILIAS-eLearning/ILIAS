<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

/**
 * Class ilMailBaseTest
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class ilMailBaseTest extends TestCase
{
	/**
	 * @inheritdoc
	 */
	protected function setUp(): void 
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