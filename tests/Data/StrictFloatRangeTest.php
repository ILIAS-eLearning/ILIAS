<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Data;

use ILIAS\Data\Range\StrictFloatRange;
use ILIAS\Refinery\Validation\Constraints\ConstraintViolationException;

require_once 'libs/composer/vendor/autoload.php';

class StrictFloatRangeTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @throws \InvalidArgumentException
	 */
	public function testValidFloatRanges()
	{
		$floatRange = new StrictFloatRange(3.0 , 100.4);

		$this->assertSame($floatRange->minimum(), 3.0);
		$this->assertSame($floatRange->maximum(), 100.4);
	}

	public function testValueIsInRange()
	{
		$range = new StrictFloatRange(3.0 , 100.4);

		$this->assertTrue($range->spans(50));
	}

	public function testMinimumValueIsNotInRange()
	{
		$range = new StrictFloatRange(3.0 , 100.4);

		$this->assertFalse($range->spans(3));
	}

	public function testMaximumValueIsNotInRange()
	{
		$range = new StrictFloatRange(3.0 , 100.4);

		$this->assertFalse($range->spans(3));
	}

	public function testValueIsNotInRangeBecauseTheValueIsToLow()
	{
		$range = new StrictFloatRange(3.0 , 100.4);

		$this->assertFalse($range->spans(1));
	}

	public function testValueIsNotInRangeBecauseTheValueIsToHigh()
	{
		$range = new StrictFloatRange(3.0 , 100.4);

		$this->assertFalse($range->spans(101));
	}

	/**
	 * @throws ConstraintViolationException
	 */
	public function testHexIsAllowForRanges()
	{
		$floatRange = new StrictFloatRange(0x3 , 0xA);

		$this->assertSame($floatRange->minimum(), 3.0);
		$this->assertSame($floatRange->maximum(), 10.0);
	}

	/**
	 * @throws ConstraintViolationException
	 */
	public function testBinaryIsAllowForRanges()
	{
		$floatRange = new StrictFloatRange(0b11 , 0b1010);

		$this->assertSame($floatRange->minimum(), 3.0);
		$this->assertSame($floatRange->maximum(), 10.0);
	}

	public function testRangeIsSameThrowsException()
	{
		try {
			$floatRange = new StrictFloatRange(3.0, 3.0);
		} catch (ConstraintViolationException $exception) {
			return;
		}
		$this->fail();
	}

	public function testMaximumsIsLowerThanMinimumThrowsException()
	{
		try {
			$floatRange = new StrictFloatRange(3.0, 1.0);
		} catch (ConstraintViolationException $exception) {
			return;
		}
		$this->fail();
	}
}
