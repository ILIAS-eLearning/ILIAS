<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Tests\Refinery\KindlyTo\Transformation;

require_once('./libs/composer/vendor/autoload.php');

use ILIAS\BackgroundTasks\Exceptions\InvalidArgumentException;
use ILIAS\Data\Result;
use ILIAS\Refinery\KindlyTo\Transformation\FloatTransformation;
use ILIAS\Tests\Refinery\TestCase;

class FloatTransformationTest extends TestCase
{
	/**
	 * @var FloatTransformation
	 */
	private $transformation;

	public function setUp() : void
	{
		$this->transformation = new FloatTransformation();
	}

	public function testIntegerToFloatTransformation()
	{
		$transformedValue = $this->transformation->transform(200);

		$this->assertEquals(200.0, $transformedValue);
	}

	public function testStringToFloatTransformation()
	{
		$transformedValue = $this->transformation->transform('hello');

		$this->assertEquals(0.0, $transformedValue);
	}

	public function testFloatToFloatTransformation()
	{
		$transformedValue = $this->transformation->transform(10.5);

		$this->assertEquals(10.5, $transformedValue);
	}

	public function testNegativeIntegerToFloatTransformation()
	{
		$transformedValue = $this->transformation->transform(-200);

		$this->assertEquals(-200, $transformedValue);
	}

	public function testZeroIntegerToFloatTransformation()
	{
		$transformedValue = $this->transformation->transform(0);

		$this->assertEquals(0.0, $transformedValue);
	}

	public function testZeroFloatToFloatTransformation()
	{
		$transformedValue = $this->transformation->transform(0.0);

		$this->assertEquals(0.0, $transformedValue);
	}

	public function testPositiveIntegerToFloatApply()
	{
		$resultObject = new Result\Ok(200);

		$transformedObject = $this->transformation->applyTo($resultObject);

		$this->assertEquals(200.0, $transformedObject->value());
	}

	public function testNegativeIntegerToFloatApply()
	{
		$resultObject = new Result\Ok(-200);

		$transformedObject = $this->transformation->applyTo($resultObject);

		$this->assertEquals(-200.0, $transformedObject->value());
	}

	public function testZeroIntegerToFloatApply()
	{
		$resultObject = new Result\Ok(0);

		$transformedObject = $this->transformation->applyTo($resultObject);

		$this->assertEquals(0.0, $transformedObject->value());
	}

	public function testStringToFloatApply()
	{
		$resultObject = new Result\Ok('hello');

		$transformedObject = $this->transformation->applyTo($resultObject);

		$this->assertEquals(0.0, $transformedObject->value());
	}

	public function testIntegerToFloatApply()
	{
		$resultObject = new Result\Ok(200);

		$transformedObject = $this->transformation->applyTo($resultObject);

		$this->assertEquals(200.0, $transformedObject->value());
	}

	public function testFloatToFloatApply()
	{
		$resultObject = new Result\Ok(10.5);

		$transformedObject = $this->transformation->applyTo($resultObject);

		$this->assertEquals(10.5, $transformedObject->value());
	}

	public function testBooleanToFloatApply()
	{
		$resultObject = new Result\Ok(true);

		$transformedObject = $this->transformation->applyTo($resultObject);

		$this->assertEquals(1.0, $transformedObject->value());
	}

	public function testInstanceApplyTo()
	{
		$resultObject = new Result\Ok($this->getMockBuilder('Something'));

		$transformedObject = $this->transformation->applyTo($resultObject);

		$this->assertTrue($transformedObject->isError());
	}

	public function testInstanceTransform()
	{
		$this->expectNotToPerformAssertions();

		try {
			$value = $this->transformation->transform($this->getMockBuilder('Something'));
		} catch (\InvalidArgumentException $invalidArgumentException) {
			return;
		}

		$this->fail();
	}

	public function testArrayTransform()
	{
		$value = $this->transformation->transform(array());

		$this->assertSame(0.0, $value);
	}

	public function testArrayApplyTo()
	{
		$resultObject = new Result\Ok(array());

		$transformedObject = $this->transformation->applyTo($resultObject);

		$this->assertSame(0.0, $transformedObject->value());
	}
}
