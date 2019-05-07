<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Tests\Refinery\To\Transformation;

require_once('./libs/composer/vendor/autoload.php');

use ILIAS\Data\Result;
use ILIAS\Refinery\To\Transformation\StringTransformation;
use ILIAS\Refinery\Validation\Constraints\ConstraintViolationException;
use ILIAS\Tests\Refinery\TestCase;

class StringTransformationTest extends TestCase
{
	/**
	 * @var StringTransformation
	 */
	private $transformation;

	public function setUp() : void
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
		$this->expectNotToPerformAssertions();

		try {
			$transformedValue = $this->transformation->transform(200);
		} catch (ConstraintViolationException $exception) {
			return;
		}

		$this->fail();
	}

	public function testNegativeIntegerToIntegerTransformation()
	{
		$this->expectNotToPerformAssertions();

		try {
			$transformedValue = $this->transformation->transform(-200);
		} catch (ConstraintViolationException $exception) {
			return;
		}

		$this->assertEquals('-200', $transformedValue);
	}

	public function testZeroIntegerToIntegerTransformation()
	{
		$this->expectNotToPerformAssertions();

		try {
			$transformedValue = $this->transformation->transform(0);
		} catch (ConstraintViolationException $exception) {
			return;
		}

		$this->fail();
	}

	public function testFloatToStringTransformation()
	{
		$this->expectNotToPerformAssertions();

		$this->expectNotToPerformAssertions();

		try {
			$transformedValue = $this->transformation->transform(10.5);
		} catch (ConstraintViolationException $exception) {
			return;
		}

		$this->fail();
	}

	public function testPositiveBooleanToStringTransformation()
	{
		$this->expectNotToPerformAssertions();

		try {
			$transformedValue = $this->transformation->transform(true);
		} catch (ConstraintViolationException $exception) {
			return;
		}

		$this->fail();
	}

	public function testNegativeBooleanToStringTransformation()
	{
		$this->expectNotToPerformAssertions();

		try {
			$transformedValue = $this->transformation->transform(false);
		} catch (ConstraintViolationException $exception) {
			return;
		}

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
