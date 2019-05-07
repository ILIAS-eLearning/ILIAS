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
use ILIAS\Refinery\Validation\Constraints\ConstraintViolationException;
use ILIAS\Tests\Refinery\TestCase;

require_once('./libs/composer/vendor/autoload.php');

class TupleTransformationTest extends TestCase
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

	public function testToManyValuesForTransformation()
	{
		$this->expectNotToPerformAssertions();

		$transformation = new TupleTransformation(
			array(new IntegerTransformation(), new IntegerTransformation())
		);

		try {
			$result = $transformation->transform(array(1, 2, 3));
		} catch (ConstraintViolationException $exception) {
			return;
		}

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

	public function testInvalidTransformationWillThrowException()
	{
		$this->expectNotToPerformAssertions();

		try {
			$transformation = new TupleTransformation(
				array(new IntegerTransformation(), 'hello')
			);
		} catch (\InvalidArgumentException $exception) {
			return;
		}

		$this->fail();
	}
}
