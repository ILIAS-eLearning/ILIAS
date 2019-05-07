<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


namespace ILIAS\Data;

use ILIAS\Data\Interval\OpenedIntegerInterval;
use ILIAS\Refinery\Validation\Constraints\ConstraintViolationException;
use PHPUnit\Framework\TestCase;


require_once("./libs/composer/vendor/autoload.php");

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class OpenedIntegerIntervalTest extends TestCase
{
	/**
	 * @throws \InvalidArgumentException
	 */
	public function testRangeIsAccepted()
	{
		$range = new OpenedIntegerInterval(3, 100);

		$this->assertEquals(3, $range->minimum());
		$this->assertEquals(100, $range->maximum());
	}

	public function testValueIsInRange()
	{
		$range = new OpenedIntegerInterval(3, 100);

		$this->assertTrue($range->spans(50));
	}

	public function testMinimumValueIsInRange()
	{
		$range = new OpenedIntegerInterval(3, 100);

		$this->assertTrue($range->spans(3));
	}

	public function testMaximumValueIsInRange()
	{
		$range = new OpenedIntegerInterval(3, 100);

		$this->assertTrue($range->spans(3));
	}

	public function testValueIsNotInRangeBecauseTheValueIsToLow()
	{
		$range = new OpenedIntegerInterval(3, 100);

		$this->assertFalse($range->spans(1));
	}

	public function testValueIsNotInRangeBecauseTheValueIsToHigh()
	{
		$range = new OpenedIntegerInterval(3, 100);

		$this->assertFalse($range->spans(101));
	}

	public function testMaximumCanNotBeLowerThanMinimum()
	{
		$this->expectNotToPerformAssertions();

		try {
			$range = new OpenedIntegerInterval(3, 1);
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
		$range = new OpenedIntegerInterval(0x3 , 0xA);

		$this->assertSame($range->minimum(), 3);
		$this->assertSame($range->maximum(), 10);
	}

	/**
	 * @throws ConstraintViolationException
	 */
	public function testBinaryIsAllowForRanges()
	{
		$range = new OpenedIntegerInterval(0b11 , 0b1010);

		$this->assertSame($range->minimum(), 3);
		$this->assertSame($range->maximum(), 10);

	}
}
