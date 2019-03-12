<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Tests\Refinery\KindlyTo\Transformation;

use ILIAS\Data\Result;
use ILIAS\Refinery\KindlyTo\Transformation\IntegerTransformation;
use ILIAS\Refinery\KindlyTo\Transformation\StringTransformation;
use ILIAS\Refinery\KindlyTo\Transformation\TupleTransformation;

require_once('./libs/composer/vendor/autoload.php');

class TupleTransformationTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @throws \ilException
	 */
	public function testTupleTransformationsAreCorrect()
	{
		$transformation = new TupleTransformation(
			array(new IntegerTransformation(), new IntegerTransformation())
		);

		$result = $transformation->transform(array(1, 2));

		$this->assertEquals(array(1, 2), $result);
	}

	public function testTupleWithDifferentTransformation()
	{
		$transformation = new TupleTransformation(
			array(new IntegerTransformation(), new StringTransformation())
		);

		$result = $transformation->transform(array(1.3, 2));

		$this->assertSame(array(1, '2'), $result);
	}

	/**
	 * @expectedException \ilException
	 */
	public function testToManyValuesForTransformation()
	{
		$transformation = new TupleTransformation(
			array(new IntegerTransformation(), new IntegerTransformation())
		);

		$result = $transformation->transform(array(1, 2, 3));

		$this->fail();
	}

	public function testTupleAppliesAreCorrect()
	{
		$transformation = new TupleTransformation(
			array(new IntegerTransformation(), new IntegerTransformation())
		);

		$result = $transformation->applyTo(new Result\Ok(array(1, 2)));

		$this->assertEquals(array(1, 2), $result->value());
	}

	public function testTupleAppliesAreIncorrectAndWillReturnErrorResult()
	{
		$transformation = new TupleTransformation(
			array(new IntegerTransformation(), new StringTransformation())
		);

		$result = $transformation->applyTo(new Result\Ok(array(1.3, 2)));

		$this->assertSame(array(1, '2'), $result->value());
	}

	public function testToManyValuesForApply()
	{
		$transformation = new TupleTransformation(
			array(new IntegerTransformation(), new StringTransformation())
		);

		$result = $transformation->applyTo(new Result\Ok(array(1, 2, 3)));

		$this->assertTrue($result->isError());
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testInvalidTransformationWillThrowException()
	{
		$transformation = new TupleTransformation(
			array(new IntegerTransformation(), 'hello')
		);

		$this->fail();
	}
}
