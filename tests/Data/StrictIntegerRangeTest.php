<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


namespace ILIAS\Data;

use ILIAS\Data\Range\StrictIntegerRange;
use ILIAS\Refinery\Validation\Constraints\ConstraintViolationException;

require_once("./libs/composer/vendor/autoload.php");

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class StrictIntegerRangeTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @throws \InvalidArgumentException
	 */
	public function testRangeIsAccepted()
	{
		$range = new StrictIntegerRange(3, 100);

		$this->assertEquals(3, $range->minimum());
		$this->assertEquals(100, $range->maximum());
	}

	public function testValueIsInRange()
	{
		$range = new StrictIntegerRange(3, 100);

		$this->assertTrue($range->spans(50));
	}

	public function testMinimumValueIsNotInRange()
	{
		$range = new StrictIntegerRange(3, 100);

		$this->assertFalse($range->spans(3));
	}

	public function testMaximumValueIsNotInRange()
	{
		$range = new StrictIntegerRange(3, 100);

		$this->assertFalse($range->spans(3));
	}

	public function testValueIsNotInRangeBecauseTheValueIsToLow()
	{
		$range = new StrictIntegerRange(3, 100);

		$this->assertFalse($range->spans(1));
	}

	public function testValueIsNotInRangeBecauseTheValueIsToHigh()
	{
		$range = new StrictIntegerRange(3, 100);

		$this->assertFalse($range->spans(101));
	}

	public function testMaximumCanNotBeLowerThanMinimum()
	{
		try {
			$range = new StrictIntegerRange(3, 1);
		} catch (ConstraintViolationException $exception) {
			return;
		}
		$this->fail();
	}

	public function testMaximumCanNotBeEqualMinimum()
	{
		try {
			$range = new StrictIntegerRange(3, 1);
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
		$range = new StrictIntegerRange(0x3 , 0xA);

		$this->assertSame($range->minimum(), 3);
		$this->assertSame($range->maximum(), 10);
	}

	/**
	 * @throws ConstraintViolationException
	 */
	public function testBinaryIsAllowForRanges()
	{
		$range = new StrictIntegerRange(0b11 , 0b1010);

		$this->assertSame($range->minimum(), 3);
		$this->assertSame($range->maximum(), 10);

	}
}
