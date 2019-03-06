<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Refinery\KindlyTo\Refinery;

require_once('./libs/composer/vendor/autoload.php');

use ILIAS\Data\Result;
use ILIAS\Refinery\KindlyTo\Transformation\BooleanTransformation;

class BooleanTransformationTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var BooleanTransformation
	 */
	private $transformation;

	public function setUp()
	{
		$this->transformation = new BooleanTransformation();
	}

	public function testIntegerToBooleanTransformation()
	{
		$transformedValue = $this->transformation->transform(200);
		$this->assertTrue($transformedValue);
	}

	public function testNegativeIntegerToBooleanTransformation()
	{
		$transformedValue = $this->transformation->transform(-200);
		$this->assertTrue($transformedValue);
	}

	public function testZeroIntegerToBooleanTransformation()
	{
		$transformedValue = $this->transformation->transform(0);
		$this->assertFalse($transformedValue);
	}

	public function testStringToBooleanTransformation()
	{
		$transformedValue = $this->transformation->transform('hello');
		$this->assertTrue($transformedValue);
	}

	public function testFloatToBooleanTransformation()
	{
		$transformedValue = $this->transformation->transform(10.5);
		$this->assertTrue($transformedValue);
	}

	public function testNegativeFloatToBooleanTransformation()
	{
		$transformedValue = $this->transformation->transform(-10.5);
		$this->assertTrue($transformedValue);
	}

	public function testZeroFloatToBooleanTransformation()
	{
		$transformedValue = $this->transformation->transform(0.0);
		$this->assertFalse($transformedValue);
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

		$this->assertFalse($transformedObject->isError());
	}

	public function testPositiveIntegerToBooleanApply()
	{
		$resultObject = new Result\Ok(200);

		$transformedObject = $this->transformation->applyTo($resultObject);

		$this->assertTrue($transformedObject->value());
	}

	public function testNegativeIntegerToBooleanApply()
	{
		$resultObject = new Result\Ok(-200);

		$transformedObject = $this->transformation->applyTo($resultObject);

		$this->assertTrue($transformedObject->value());
	}

	public function testZeroIntegerToBooleanApply()
	{
		$resultObject = new Result\Ok(0);

		$transformedObject = $this->transformation->applyTo($resultObject);

		$this->assertFalse($transformedObject->value());
	}

	public function testFloatToBooleanApply()
	{
		$resultObject = new Result\Ok(10.5);

		$transformedObject = $this->transformation->applyTo($resultObject);

		$this->assertTrue($transformedObject->value());
	}

	public function testBooleanToBooleanApply()
	{
		$resultObject = new Result\Ok(true);

		$transformedObject = $this->transformation->applyTo($resultObject);

		$this->assertTrue($transformedObject->value());
	}
}
