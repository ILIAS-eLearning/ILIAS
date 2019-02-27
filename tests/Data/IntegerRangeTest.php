<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


namespace ILIAS\Data;

require_once("./libs/composer/vendor/autoload.php");

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class IntegerRangeTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @throws \InvalidArgumentException
	 */
	public function testRangeIsAccepted()
	{
		$range = new IntegerRange(3, 100);

		$this->assertEquals(3, $range->minimumAsInteger());
		$this->assertEquals(100, $range->maximumAsInteger());
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testMaximumCanNotBeLowerThanMinimum()
	{
		$range = new IntegerRange(3, 1);
		$this->fail();
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testMaximumCanNotBeEqual()
	{
		$range = new IntegerRange(3, 1);
		$this->fail();
	}

	/**
	 * @throws \InvalidArgumentException
	 */
	public function testHexIsAllowForRanges()
	{
		$range = new IntegerRange(0x3 , 0xA);

		$this->assertSame($range->minimumAsInteger(), 3);
		$this->assertSame($range->maximumAsInteger(), 10);
	}

	/**
	 * @throws \InvalidArgumentException
	 */
	public function testBinaryIsAllowForRanges()
	{
		$range = new IntegerRange(0b11 , 0b1010);

		$this->assertSame($range->minimumAsInteger(), 3);
		$this->assertSame($range->maximumAsInteger(), 10);

	}
}
