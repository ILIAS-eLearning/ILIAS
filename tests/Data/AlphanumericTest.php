<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Data;

require_once 'libs/composer/vendor/autoload.php';

class AlphanumericTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @throws \ilException
	 */
	public function testSimpleStringIsCorrectAlphanumericValueAndCanBeConvertedToString()
	{
		$value = new Alphanumeric('hello');

		$this->assertSame('hello', $value->asString());
	}

	/**
	 * @throws \ilException
	 */
	public function testIntegerIsAlphanumericValueAndCanBeConvertedToString()
	{
		$value = new Alphanumeric(6);

		$this->assertSame('6', $value->asString());
	}

	/**
	 * @throws \ilException
	 */
	public function testIntegerIsAlphanumericValue()
	{
		$value = new Alphanumeric(6);

		$this->assertSame(6, $value->getValue());
	}

	/**
	 * @throws \ilException
	 */
	public function testFloatIsAlphanumericValueAndCanBeConvertedToString()
	{
		$value = new Alphanumeric(6.0);

		$this->assertSame('6', $value->asString());
	}

	/**
	 * @throws \ilException
	 */
	public function testFloatIsAlphanumericValue()
	{
		$value = new Alphanumeric(6.0);

		$this->assertSame(6.0, $value->getValue());
	}

	/**
	 * @expectedException  \ilException
	 */
	public function testTextIsNotAlphanumericAndWillThrowException()
	{
		$value = new Alphanumeric('hello world');
		$this->fail();
	}
}
