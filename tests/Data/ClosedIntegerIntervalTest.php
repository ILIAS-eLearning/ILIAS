<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


namespace ILIAS\Data;

use ILIAS\Data\Interval\ClosedIntegerInterval;
use ILIAS\Refinery\Validation\Constraints\ConstraintViolationException;
use PHPUnit\Framework\TestCase;

require_once("./libs/composer/vendor/autoload.php");

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ClosedIntegerIntervalTest extends TestCase
{
	/**
	 * @throws \InvalidArgumentException
	 */
	public function testRangeIsAccepted()
	{
		$range = new ClosedIntegerInterval(3, 100);

		$this->assertEquals(3, $range->minimum());
		$this->assertEquals(100, $range->maximum());
	}

	public function testValueIsInRange()
	{
		$range = new ClosedIntegerInterval(3, 100);

		$this->assertTrue($range->spans(50));
	}

	public function testMinimumValueIsNotInRange()
	{
		$range = new ClosedIntegerInterval(3, 100);

		$this->assertFalse($range->spans(3));
	}

	public function testMaximumValueIsNotInRange()
	{
		$range = new ClosedIntegerInterval(3, 100);

		$this->assertFalse($range->spans(3));
	}

	public function testValueIsNotInRangeBecauseTheValueIsToLow()
	{
		$range = new ClosedIntegerInterval(3, 100);

		$this->assertFalse($range->spans(1));
	}

	public function testValueIsNotInRangeBecauseTheValueIsToHigh()
	{
		$range = new ClosedIntegerInterval(3, 100);

		$this->assertFalse($range->spans(101));
	}

	public function testMaximumCanNotBeLowerThanMinimum()
	{
		$this->expectNotToPerformAssertions();

		try {
			$range = new ClosedIntegerInterval(3, 1);
		} catch (ConstraintViolationException $exception) {
			return;
		}
		$this->fail();
	}

	public function testMaximumCanNotBeEqualMinimum()
	{
		$this->expectNotToPerformAssertions();

		try {
			$range = new ClosedIntegerInterval(3, 1);
		} catch (ConstraintViolationException $exception) {
			return;
		}
		$this->fail();
	}

	/**
	 * @throws ConstraintViolationException
	 */
	public function testHexIsAllowForRanges()
	{
		$range = new ClosedIntegerInterval(0x3 , 0xA);

		$this->assertSame($range->minimum(), 3);
		$this->assertSame($range->maximum(), 10);
	}

	/**
	 * @throws ConstraintViolationException
	 */
	public function testBinaryIsAllowForRanges()
	{
		$range = new ClosedIntegerInterval(0b11 , 0b1010);

		$this->assertSame($range->minimum(), 3);
		$this->assertSame($range->maximum(), 10);

	}
}
