<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Data;

use ILIAS\Refinery\Validation\Constraints\ConstraintViolationException;
use PHPUnit\Framework\TestCase;

require_once("libs/composer/vendor/autoload.php");

class PositiveIntegerTest extends TestCase
{
	/**
	 * @throws ConstraintViolationException
	 */
	public function testCreatePositiveInteger()
	{
		$integer = new PositiveInteger(6);
		$this->assertSame(6, $integer->getValue());
	}

	public function testNegativeIntegerThrowsException()
	{
		$this->expectNotToPerformAssertions();

		try {
			$integer = new PositiveInteger(-6);
		} catch (ConstraintViolationException $exception) {
			return;
		}
		$this->fail();
	}

	/**
	 * @throws ConstraintViolationException
	 */
	public function testMaximumIntegerIsAccepted()
	{
		$integer = new PositiveInteger(PHP_INT_MAX);
		$this->assertSame(PHP_INT_MAX, $integer->getValue());
	}
}
