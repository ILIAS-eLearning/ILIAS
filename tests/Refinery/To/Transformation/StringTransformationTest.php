<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Tests\Refinery\To\Transformation;

require_once('./libs/composer/vendor/autoload.php');

use ILIAS\Data\Result;
use ILIAS\Refinery\To\Transformation\StringTransformation;

class StringTransformationTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var StringTransformation
	 */
	private $transformation;

	public function setUp()
	{
		$this->transformation = new StringTransformation();
	}

	public function testStringToStringTransformation()
	{
		$transformedValue = $this->transformation->transform('hello');

		$this->assertEquals('hello', $transformedValue);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testIntegerToStringTransformation()
	{
		$transformedValue = $this->transformation->transform(200);

		$this->fail();
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testNegativeIntegerToIntegerTransformation()
	{
		$transformedValue = $this->transformation->transform(-200);

		$this->assertEquals('-200', $transformedValue);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testZeroIntegerToIntegerTransformation()
	{
		$transformedValue = $this->transformation->transform(0);

		$this->fail();
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testFloatToStringTransformation()
	{
		$transformedValue = $this->transformation->transform(10.5);

		$this->fail();
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testPositiveBooleanToStringTransformation()
	{
		$transformedValue = $this->transformation->transform(true);

		$this->fail();
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testNegativeBooleanToStringTransformation()
	{
		$transformedValue = $this->transformation->transform(false);

		$this->fail();
	}

	public function testStringToStringApply()
	{
		$resultObject = new Result\Ok('hello');

		$transformedObject = $this->transformation->applyTo($resultObject);

		$this->assertEquals('hello', $transformedObject->value());
	}

	public function testPositiveIntegerToIntegerApply()
	{
		$resultObject = new Result\Ok(200);

		$transformedObject = $this->transformation->applyTo($resultObject);

		$this->assertTrue($transformedObject->isError());
	}

	public function testNegativeIntegerToIntegerApply()
	{
		$resultObject = new Result\Ok(-200);

		$transformedObject = $this->transformation->applyTo($resultObject);

		$this->assertTrue($transformedObject->isError());
	}

	public function testZeroIntegerToIntegerApply()
	{
		$resultObject = new Result\Ok(0);

		$transformedObject = $this->transformation->applyTo($resultObject);

		$this->assertTrue($transformedObject->isError());
	}

	public function testFloatToStringApply()
	{
		$resultObject = new Result\Ok(10.5);

		$transformedObject = $this->transformation->applyTo($resultObject);

		$this->assertTrue($transformedObject->isError());
	}

	public function testBooleanToStringApply()
	{
		$resultObject = new Result\Ok(true);

		$transformedObject = $this->transformation->applyTo($resultObject);

		$this->assertTrue($transformedObject->isError());
	}
}
