<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Tests\Refinery\To\Transformation;

use ILIAS\Data\Result;
use ILIAS\Refinery\To\Transformation\IntegerTransformation;
use ILIAS\Refinery\To\Transformation\StringTransformation;
use ILIAS\Refinery\To\Transformation\TupleTransformation;
use ILIAS\Refinery\Validation\Constraints\ConstraintViolationException;
use ILIAS\Refinery\Validation\Constraints\IsArrayOfSameType;
use ILIAS\Refinery\Validation\Factory;
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

	public function testTupleIsIncorrectAndWillThrowException()
	{
		$this->expectNotToPerformAssertions();

		$transformation = new TupleTransformation(
			array(new IntegerTransformation(), new StringTransformation())
		);

		try {
			$result = $transformation->transform(array(1, 2));
		} catch (ConstraintViolationException $exception) {
			return;
		}

		$this->fail();
	}

	public function testTupleIsIncorrectAndWillThrowException2()
	{
		$this->expectNotToPerformAssertions();

		$transformation = new TupleTransformation(
			array(new IntegerTransformation(), 'hello' => new IntegerTransformation())
		);

		try {
			$result = $transformation->transform(array(1, 2));
		} catch (ConstraintViolationException $exception) {
			return;
		}

		$this->fail();
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

		$result = $transformation->applyTo(new Result\Ok(array(1, 2)));

		$this->assertTrue($result->isError());
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
		} catch (ConstraintViolationException $exception) {
			return;
		}


		$this->fail();
	}
}
