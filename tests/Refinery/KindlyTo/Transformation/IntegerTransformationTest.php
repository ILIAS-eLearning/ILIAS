<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Tests\Refinery\KindlyTo\Transformation;

require_once('./libs/composer/vendor/autoload.php');

use ILIAS\Data\Result;
use ILIAS\Refinery\KindlyTo\Transformation\IntegerTransformation;
use ILIAS\Tests\Refinery\TestCase;

class IntegerTransformationTest extends TestCase
{
	/**
	 * @var IntegerTransformation
	 */
	private $transformation;

	public function setUp()
	{
		$this->transformation = new IntegerTransformation();
	}

	public function testIntegerToIntegerTransformation()
	{
		$transformedValue = $this->transformation->transform(200);

		$this->assertEquals(200, $transformedValue);
	}

	public function testNegativeIntegerToIntegerTransformation()
	{
		$transformedValue = $this->transformation->transform(-200);

		$this->assertEquals(-200, $transformedValue);
	}

	public function testZeroIntegerToIntegerTransformation()
	{
		$transformedValue = $this->transformation->transform(0);

		$this->assertEquals(0, $transformedValue);
	}

	public function testStringToIntegerTransformation()
	{
		$transformedValue = $this->transformation->transform('hello');

		$this->assertEquals(0, $transformedValue);
	}

	public function testFloatToIntegerTransformation()
	{
		$transformedValue = $this->transformation->transform(10.5);

		$this->assertEquals(10, $transformedValue);
	}

	public function testPositiveBooleanToIntegerTransformation()
	{
		$transformedValue = $this->transformation->transform(true);

		$this->assertEquals(1, $transformedValue);
	}

	public function testNegativeBooleanToIntegerTransformation()
	{
		$transformedValue = $this->transformation->transform(false);

		$this->assertEquals(0, $transformedValue);
	}

	public function testStringToIntegerApply()
	{
		$resultObject = new Result\Ok('hello');

		$transformedObject = $this->transformation->applyTo($resultObject);

		$this->assertEquals(0, $transformedObject->value());
	}

	public function testPositiveIntegerToIntegerApply()
	{
		$resultObject = new Result\Ok(200);

		$transformedObject = $this->transformation->applyTo($resultObject);

		$this->assertEquals(200, $transformedObject->value());
	}

	public function testNegativeIntegerToIntegerApply()
	{
		$resultObject = new Result\Ok(-200);

		$transformedObject = $this->transformation->applyTo($resultObject);

		$this->assertEquals(-200, $transformedObject->value());
	}

	public function testZeroIntegerToIntegerApply()
	{
		$resultObject = new Result\Ok(0);

		$transformedObject = $this->transformation->applyTo($resultObject);

		$this->assertEquals(0, $transformedObject->value());
	}

	public function testFloatToIntegerApply()
	{
		$resultObject = new Result\Ok(10.5);

		$transformedObject = $this->transformation->applyTo($resultObject);

		$this->assertEquals(10, $transformedObject->value());
	}

	public function testBooleanToIntegerApply()
	{
		$resultObject = new Result\Ok(true);

		$transformedObject = $this->transformation->applyTo($resultObject);

		$this->assertEquals(1, $transformedObject->value());
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

	public function testArrayTransform()
	{
		$value = $this->transformation->transform(array());

		$this->assertSame(0, $value);
	}

	public function testArrayApplyTo()
	{
		$resultObject = new Result\Ok(array());

		$transformedObject = $this->transformation->applyTo($resultObject);

		$this->assertSame(0, $transformedObject->value());
	}
}
