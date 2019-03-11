<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Tests\Refinery\KindlyTo\Transformation;

require_once('./libs/composer/vendor/autoload.php');

use ILIAS\Data\Result;
use ILIAS\Refinery\KindlyTo\Transformation\StringTransformation;

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

	public function testIntegerToStringTransformation()
	{
		$transformedValue = $this->transformation->transform(200);

		$this->assertEquals('200', $transformedValue);
	}

	public function testNegativeIntegerToIntegerTransformation()
	{
		$transformedValue = $this->transformation->transform(-200);

		$this->assertEquals('-200', $transformedValue);
	}

	public function testZeroIntegerToIntegerTransformation()
	{
		$transformedValue = $this->transformation->transform(0);

		$this->assertEquals('0', $transformedValue);
	}

	public function testFloatToStringTransformation()
	{
		$transformedValue = $this->transformation->transform(10.5);

		$this->assertEquals('10.5', $transformedValue);
	}

	public function testPositiveBooleanToStringTransformation()
	{
		$transformedValue = $this->transformation->transform(true);

		$this->assertEquals('1', $transformedValue);
	}

	public function testNegativeBooleanToStringTransformation()
	{
		$transformedValue = $this->transformation->transform(false);

		$this->assertEquals('', $transformedValue);
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

		$this->assertEquals('200', $transformedObject->value());
	}

	public function testNegativeIntegerToIntegerApply()
	{
		$resultObject = new Result\Ok(-200);

		$transformedObject = $this->transformation->applyTo($resultObject);

		$this->assertEquals('-200', $transformedObject->value());
	}

	public function testZeroIntegerToIntegerApply()
	{
		$resultObject = new Result\Ok(0);

		$transformedObject = $this->transformation->applyTo($resultObject);

		$this->assertEquals('0', $transformedObject->value());
	}

	public function testFloatToStringApply()
	{
		$resultObject = new Result\Ok(10.5);

		$transformedObject = $this->transformation->applyTo($resultObject);

		$this->assertEquals('10.5', $transformedObject->value());
	}

	public function testBooleanToStringApply()
	{
		$resultObject = new Result\Ok(true);

		$transformedObject = $this->transformation->applyTo($resultObject);

		$this->assertEquals('1', $transformedObject->value());
	}

	public function testInstanceApplyTo()
	{
		$resultObject = new Result\Ok($this->getMockBuilder('Something'));

		$transformedObject = $this->transformation->applyTo($resultObject);

		$this->assertTrue($transformedObject->isError());
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testInstanceTransform()
	{
		$value = $this->transformation->transform($this->getMockBuilder('Something'));

		$this->fail();
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testArrayTransform()
	{
		$value = $this->transformation->transform(array());

		$this->fail();
	}

	public function testArrayApplyTo()
	{
		$resultObject = new Result\Ok(array());

		$transformedObject = $this->transformation->applyTo($resultObject);

		$this->assertTrue($transformedObject->isError());
	}
}
