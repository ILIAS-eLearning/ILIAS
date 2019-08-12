<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Tests\Refinery\To\Transformation;

require_once('./libs/composer/vendor/autoload.php');

use ILIAS\Data\Result;
use ILIAS\Refinery\To\Transformation\BooleanTransformation;
use ILIAS\Refinery\ConstraintViolationException;
use ILIAS\Tests\Refinery\TestCase;

class BooleanTransformationTest extends TestCase
{
	/**
	 * @var BooleanTransformation
	 */
	private $transformation;

	public function setUp() : void
	{
		$this->transformation = new BooleanTransformation();
	}

	public function testIntegerToBooleanTransformation()
	{
		$this->expectNotToPerformAssertions();

		try {
			$transformedValue = $this->transformation->transform(200);
		} catch (ConstraintViolationException $exception) {
			return;
		}
		$this->fail();
	}

	public function testNegativeIntegerToBooleanTransformation()
	{
		$this->expectNotToPerformAssertions();

		try {
			$transformedValue = $this->transformation->transform(-200);
		} catch (ConstraintViolationException $exception) {
			return;
		}
		$this->fail();
	}

	public function testZeroIntegerToBooleanTransformation()
	{
		$this->expectNotToPerformAssertions();

		try {
			$transformedValue = $this->transformation->transform(0);
		} catch (ConstraintViolationException $exception) {
			return;
		}
		$this->fail();
	}

	public function testStringToBooleanTransformation()
	{
		$this->expectNotToPerformAssertions();

		try {
			$transformedValue = $this->transformation->transform('hello');
		} catch (ConstraintViolationException $exception) {
			return;
		}
		$this->fail();
	}

	public function testFloatToBooleanTransformation()
	{
		$this->expectNotToPerformAssertions();

		try {
			$transformedValue = $this->transformation->transform(10.5);
		} catch (ConstraintViolationException $exception) {
			return;
		}
		$this->fail();
	}

	public function testNegativeFloatToBooleanTransformation()
	{
		$this->expectNotToPerformAssertions();

		try {
			$transformedValue = $this->transformation->transform(-10.5);
		} catch (ConstraintViolationException $exception) {
			return;
		}
		$this->fail();
	}

	public function testZeroFloatToBooleanTransformation()
	{
		$this->expectNotToPerformAssertions();

		try {
			$transformedValue = $this->transformation->transform(0.0);
		} catch (ConstraintViolationException $exception) {
			return;
		}
		$this->fail();
	}

	public function testPositiveBooleanToBooleanTransformation()
	{
		$transformedValue = $this->transformation->transform(true);
		$this->assertTrue($transformedValue);
	}

	public function testNegativeBooleanToBooleanTransformation()
	{
		$transformedValue = $this->transformation->transform(false);
		$this->assertFalse($transformedValue);
	}

	public function testStringToBooleanApply()
	{
		$resultObject = new Result\Ok('hello');

		$transformedObject = $this->transformation->applyTo($resultObject);

		$this->assertTrue($transformedObject->isError());
	}

	public function testPositiveIntegerToBooleanApply()
	{
		$resultObject = new Result\Ok(200);

		$transformedObject = $this->transformation->applyTo($resultObject);

		$this->assertTrue($transformedObject->isError());
	}

	public function testNegativeIntegerToBooleanApply()
	{
		$resultObject = new Result\Ok(-200);

		$transformedObject = $this->transformation->applyTo($resultObject);

		$this->assertTrue($transformedObject->isError());
	}

	public function testZeroIntegerToBooleanApply()
	{
		$resultObject = new Result\Ok(0);

		$transformedObject = $this->transformation->applyTo($resultObject);

		$this->assertTrue($transformedObject->isError());
	}

	public function testFloatToBooleanApply()
	{
		$resultObject = new Result\Ok(10.5);

		$transformedObject = $this->transformation->applyTo($resultObject);

		$this->assertTrue($transformedObject->isError());
	}

	public function testBooleanToBooleanApply()
	{
		$resultObject = new Result\Ok(true);

		$transformedObject = $this->transformation->applyTo($resultObject);

		$this->assertTrue($transformedObject->value());
	}
}
