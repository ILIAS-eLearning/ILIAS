<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Data;

use ILIAS\Data\Interval\OpenedFloatInterval;
use ILIAS\Refinery\Validation\Constraints\ConstraintViolationException;
use PHPUnit\Framework\TestCase;

require_once 'libs/composer/vendor/autoload.php';

class OpenedFloatIntervalTest extends TestCase
{
	/**
	 * @throws ConstraintViolationException
	 */
	public function testValidFloatRanges()
	{
		$floatRange = new OpenedFloatInterval(3.0 , 100.4);

		$this->assertSame($floatRange->minimum(), 3.0);
		$this->assertSame($floatRange->maximum(), 100.4);
	}

	public function testValueIsInRange()
	{
		$range = new OpenedFloatInterval(3.0 , 100.4);

		$this->assertTrue($range->spans(50));
	}

	public function testMinimumValueIsInRange()
	{
		$range = new OpenedFloatInterval(3.0 , 100.4);

		$this->assertTrue($range->spans(3));
	}

	public function testMaximumValueIsInRange()
	{
		$range = new OpenedFloatInterval(3.0 , 100.4);

		$this->assertTrue($range->spans(3));
	}

	public function testValueIsNotInRangeBecauseTheValueIsToLow()
	{
		$range = new OpenedFloatInterval(3.0 , 100.4);

		$this->assertFalse($range->spans(1));
	}

	public function testValueIsNotInRangeBecauseTheValueIsToHigh()
	{
		$range = new OpenedFloatInterval(3.0 , 100.4);

		$this->assertFalse($range->spans(101));
	}

	/**
	 * @throws ConstraintViolationException
	 */
	public function testHexIsAllowForRanges()
	{
		$floatRange = new OpenedFloatInterval(0x3 , 0xA);

		$this->assertSame($floatRange->minimum(), 3.0);
		$this->assertSame($floatRange->maximum(), 10.0);
	}

	/**
	 * @throws ConstraintViolationException
	 */
	public function testBinaryIsAllowForRanges()
	{
		$floatRange = new OpenedFloatInterval(0b11 , 0b1010);

		$this->assertSame($floatRange->minimum(), 3.0);
		$this->assertSame($floatRange->maximum(), 10.0);
	}

	/**
	 * @throws ConstraintViolationException
	 */
	public function testRangeCanBeSame()
	{
		$floatRange = new OpenedFloatInterval(3.0, 3.0);

		$this->assertSame($floatRange->minimum(), 3.0);
		$this->assertSame($floatRange->maximum(), 3.0);
	}

	public function testMaximumsIsLowerThanMinimumThrowsException()
	{
		$this->expectNotToPerformAssertions();

		try {
			$floatRange = new OpenedFloatInterval(3.0, 1.0);
		} catch (ConstraintViolationException $exception) {
			return;
		}
		$this->fail();
	}
}
