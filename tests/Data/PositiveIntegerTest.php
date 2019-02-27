<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Data;

require_once("libs/composer/vendor/autoload.php");

class PositiveIntegerTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @throws \ilException
	 */
	public function testCreatePositiveInteger()
	{
		$integer = new PositiveInteger(6);
		$this->assertSame(6, $integer->asInteger());
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testNegativeIntegerThrowsException()
	{
		$integer = new PositiveInteger(-6);
		$this->fail();
	}

	/**
	 * @throws \InvalidArgumentException
	 */
	public function testMaximumIntegerIsAccepted()
	{
		$integer = new PositiveInteger(PHP_INT_MAX);
		$this->assertSame(PHP_INT_MAX, $integer->asInteger());
	}
}
