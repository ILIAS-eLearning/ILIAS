<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Tests\Refinery\To\Transformation;

require_once('./libs/composer/vendor/autoload.php');

use ILIAS\Data\Result;
use ILIAS\Refinery\To\Transformation\BooleanTransformation;

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

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testIntegerToBooleanTransformation()
	{
		$transformedValue = $this->transformation->transform(200);
		$this->fail();
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testNegativeIntegerToBooleanTransformation()
	{
		$transformedValue = $this->transformation->transform(-200);
		$this->fail();
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testZeroIntegerToBooleanTransformation()
	{
		$transformedValue = $this->transformation->transform(0);
		$this->fail();
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testStringToBooleanTransformation()
	{
		$transformedValue = $this->transformation->transform('hello');
		$this->fail();
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testFloatToBooleanTransformation()
	{
		$transformedValue = $this->transformation->transform(10.5);
		$this->fail();
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testNegativeFloatToBooleanTransformation()
	{
		$transformedValue = $this->transformation->transform(-10.5);
		$this->fail();
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testZeroFloatToBooleanTransformation()
	{
		$transformedValue = $this->transformation->transform(0.0);
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
