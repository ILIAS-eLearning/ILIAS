<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Data;

require_once 'libs/composer/vendor/autoload.php';

class FloatRangeTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @throws \InvalidArgumentException
	 */
	public function testValidFloatRanges()
	{
		$floatRange = new FloatRange(3.0 , 100.4);

		$this->assertSame($floatRange->minimumAsFloat(), 3.0);
		$this->assertSame($floatRange->maximumAsFloat(), 100.4);
	}

	/**
	 * @throws \InvalidArgumentException
	 */
	public function testHexIsAllowForRanges()
	{
		$floatRange = new FloatRange(0x3 , 0xA);

		$this->assertSame($floatRange->minimumAsFloat(), 3.0);
		$this->assertSame($floatRange->maximumAsFloat(), 10.0);
	}

	/**
	 * @throws \InvalidArgumentException
	 */
	public function testBinaryIsAllowForRanges()
	{
		$floatRange = new FloatRange(0b11 , 0b1010);

		$this->assertSame($floatRange->minimumAsFloat(), 3.0);
		$this->assertSame($floatRange->maximumAsFloat(), 10.0);
	}

	/**
	 * @expectedException  \InvalidArgumentException
	 */
	public function testRangeIsSameThrowsException()
	{
		$floatRange = new FloatRange(3.0, 3.0);
	}

	/**
	 * @expectedException  \InvalidArgumentException
	 */
	public function testMaximumsIsLowerThanMinimumThrowsException()
	{
		$floatRange = new FloatRange(3.0, 1.0);
	}
}
