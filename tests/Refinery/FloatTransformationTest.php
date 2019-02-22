<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Refinery;

require_once('./libs/composer/vendor/autoload.php');

use ILIAS\Data\Result;
use ILIAS\Refinery\To\Transformation\FloatTransformation;

class FloatTransformationTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var FloatTransformation
	 */
	private $transformation;

	public function setUp()
	{
		$this->transformation = new FloatTransformation();
	}

	public function testIntegerToFloatTransformation()
	{
		$transformedValue = $this->transformation->transform(200);

		$this->assertEquals(200, $transformedValue);
	}

	public function testStringToFloatTransformation()
	{
		$transformedValue = $this->transformation->transform('hello');

		$this->assertEquals(0, $transformedValue);
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

		$this->assertEquals(0, $transformedValue);
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

		$this->assertEquals(200, $transformedObject->value());
	}

	public function testNegativeIntegerToFloatApply()
	{
		$resultObject = new Result\Ok(-200);

		$transformedObject = $this->transformation->applyTo($resultObject);

		$this->assertEquals(-200, $transformedObject->value());
	}

	public function testZeroIntegerToFloatApply()
	{
		$resultObject = new Result\Ok(0);

		$transformedObject = $this->transformation->applyTo($resultObject);

		$this->assertEquals(0, $transformedObject->value());
	}

	public function testStringToFloatApply()
	{
		$resultObject = new Result\Ok('hello');

		$transformedObject = $this->transformation->applyTo($resultObject);

		$this->assertEquals(0, $transformedObject->value());
	}

	public function testIntegerToFloatApply()
	{
		$resultObject = new Result\Ok(200);

		$transformedObject = $this->transformation->applyTo($resultObject);

		$this->assertEquals(200, $transformedObject->value());
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

		$this->assertEquals(1, $transformedObject->value());
	}
}
