<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Tests\Refinery\To\Transformation;

require_once('./libs/composer/vendor/autoload.php');

use ILIAS\Data\Result;
use ILIAS\Refinery\To\Transformation\FloatTransformation;
use ILIAS\Tests\Refinery\TestCase;

class FloatTransformationTest extends TestCase
{
	/**
	 * @var FloatTransformation
	 */
	private $transformation;

	public function setUp()
	{
		$this->transformation = new FloatTransformation();
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testIntegerToFloatTransformation()
	{
		$transformedValue = $this->transformation->transform(200);

		$this->fail();
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testStringToFloatTransformation()
	{
		$transformedValue = $this->transformation->transform('hello');

		$this->fail();
	}

	public function testFloatToFloatTransformation()
	{
		$transformedValue = $this->transformation->transform(10.5);

		$this->assertEquals(10.5, $transformedValue);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testNegativeIntegerToFloatTransformation()
	{
		$transformedValue = $this->transformation->transform(-200);

		$this->fail();
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testZeroIntegerToFloatTransformation()
	{
		$transformedValue = $this->transformation->transform(0);

		$this->fail();
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

		$this->assertTrue($transformedObject->isError());
	}

	public function testNegativeIntegerToFloatApply()
	{
		$resultObject = new Result\Ok(-200);

		$transformedObject = $this->transformation->applyTo($resultObject);

		$this->assertTrue($transformedObject->isError());
	}

	public function testZeroIntegerToFloatApply()
	{
		$resultObject = new Result\Ok(0);

		$transformedObject = $this->transformation->applyTo($resultObject);

		$this->assertTrue($transformedObject->isError());
	}

	public function testStringToFloatApply()
	{
		$resultObject = new Result\Ok('hello');

		$transformedObject = $this->transformation->applyTo($resultObject);

		$this->assertTrue($transformedObject->isError());
	}

	public function testIntegerToFloatApply()
	{
		$resultObject = new Result\Ok(200);

		$transformedObject = $this->transformation->applyTo($resultObject);

		$this->assertTrue($transformedObject->isError());
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

		$this->assertTrue($transformedObject->isError());
	}
}
