<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Data;

use ILIAS\Data\Range\FloatRange;

require_once 'libs/composer/vendor/autoload.php';

class FloatRangeTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @throws \InvalidArgumentException
	 */
	public function testValidFloatRanges()
	{
		$floatRange = new FloatRange(3.0 , 100.4);

		$this->assertSame($floatRange->minimum(), 3.0);
		$this->assertSame($floatRange->maximum(), 100.4);
	}

	public function testValueIsInRange()
	{
		$range = new FloatRange(3.0 , 100.4);

		$this->assertTrue($range->spans(50));
	}

	public function testMinimumValueIsInRange()
	{
		$range = new FloatRange(3.0 , 100.4);

		$this->assertTrue($range->spans(3));
	}

	public function testMaximumValueIsInRange()
	{
		$range = new FloatRange(3.0 , 100.4);

		$this->assertTrue($range->spans(3));
	}

	public function testValueIsNotInRangeBecauseTheValueIsToLow()
	{
		$range = new FloatRange(3.0 , 100.4);

		$this->assertFalse($range->spans(1));
	}

	public function testValueIsNotInRangeBecauseTheValueIsToHigh()
	{
		$range = new FloatRange(3.0 , 100.4);

		$this->assertFalse($range->spans(101));
	}

	/**
	 * @throws \InvalidArgumentException
	 */
	public function testHexIsAllowForRanges()
	{
		$floatRange = new FloatRange(0x3 , 0xA);

		$this->assertSame($floatRange->minimum(), 3.0);
		$this->assertSame($floatRange->maximum(), 10.0);
	}

	/**
	 * @throws \InvalidArgumentException
	 */
	public function testBinaryIsAllowForRanges()
	{
		$floatRange = new FloatRange(0b11 , 0b1010);

		$this->assertSame($floatRange->minimum(), 3.0);
		$this->assertSame($floatRange->maximum(), 10.0);
	}

	/**
	 * @throws \InvalidArgumentException
	 */
	public function testRangeCanBeSame()
	{
		$floatRange = new FloatRange(3.0, 3.0);

		$this->assertSame($floatRange->minimum(), 3.0);
		$this->assertSame($floatRange->maximum(), 3.0);
	}

	/**
	 * @expectedException  \InvalidArgumentException
	 */
	public function testMaximumsIsLowerThanMinimumThrowsException()
	{
		$floatRange = new FloatRange(3.0, 1.0);
	}
}
