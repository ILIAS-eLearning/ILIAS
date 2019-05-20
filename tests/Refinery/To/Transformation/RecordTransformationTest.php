<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Tests\Refinery\To\Transformation;

use ILIAS\Data\Result\Ok;
use ILIAS\Refinery\To\Transformation\IntegerTransformation;
use ILIAS\Refinery\To\Transformation\RecordTransformation;
use ILIAS\Refinery\To\Transformation\StringTransformation;
use ILIAS\Refinery\Validation\Constraints\ConstraintViolationException;
use ILIAS\Tests\Refinery\TestCase;

require_once('./libs/composer/vendor/autoload.php');

class RecordTransformationTest extends TestCase
{
	/**
	 * @throws \ilException
	 */
	public function testTransformationIsCorrect()
	{
		$recordTransformation = new RecordTransformation(
			array(
				'stringTrafo' => new StringTransformation(),
				'integerTrafo' => new IntegerTransformation())
		);

		$result = $recordTransformation->transform(array('stringTrafo' => 'hello', 'integerTrafo' => 1));

		$this->assertEquals(array('stringTrafo' => 'hello', 'integerTrafo' => 1), $result);
	}

	public function testInvalidTransformationArray()
	{
		$this->expectNotToPerformAssertions();

		try {
			$recordTransformation = new RecordTransformation(
				array(
					new StringTransformation(),
					new IntegerTransformation())
			);
		} catch (ConstraintViolationException $exception) {
			return;
		}

		$this->fail();
	}

	public function testTransformationIsInvalidBecauseValueDoesNotMatchWithTransformation()
	{
		$this->expectNotToPerformAssertions();

		$recordTransformation = new RecordTransformation(
			array(
				'integerTrafo' => new IntegerTransformation(),
				'anotherIntTrafo' => new IntegerTransformation())
		);

		try {
			$result = $recordTransformation->transform(array('stringTrafo' => 'hello', 'anotherIntTrafo' => 1));
		} catch (ConstraintViolationException $exception) {
			return;
		}

		$this->fail();
	}

	public function testInvalidValueKey()
	{
		$this->expectNotToPerformAssertions();

		$recordTransformation = new RecordTransformation(
			array(
				'stringTrafo' => new StringTransformation(),
				'integerTrafo' => new IntegerTransformation())
		);

		try {
			$result = $recordTransformation->transform(array('stringTrafo' => 'hello', 'floatTrafo' => 1));
		} catch (ConstraintViolationException $exception) {
			return;
		}

		$this->fail();
	}

	public function testInvalidToManyValues()
	{
		$this->expectNotToPerformAssertions();

		$recordTransformation = new RecordTransformation(
			array(
				'stringTrafo' => new StringTransformation(),
				'integerTrafo' => new IntegerTransformation())
		);


		try {
			$result = $recordTransformation->transform(
				array(
					'stringTrafo' => 'hello',
					'integerTrafo' => 1,
					'floatTrafo' => 1
				)
			);
		} catch (ConstraintViolationException $exception) {
			return;
		}

		$this->fail();
	}

	public function testTransformationThrowsExceptionBecauseKeyIsNotAString()
	{
		$this->expectNotToPerformAssertions();

		$recordTransformation = new RecordTransformation(
			array(
				'stringTrafo' => new StringTransformation(),
				'integerTrafo' => new IntegerTransformation())
		);

		try {
			$result = $recordTransformation->transform(array('someKey' => 'hello', 1));
		} catch (ConstraintViolationException $exception) {
			return;
		}

		$this->fail();
	}

	/**
	 * @throws \ilException
	 */
	public function testApplyIsCorrect()
	{
		$recordTransformation = new RecordTransformation(
			array(
				'stringTrafo' => new StringTransformation(),
				'integerTrafo' => new IntegerTransformation())
		);

		$result = $recordTransformation->applyTo(new Ok(array('stringTrafo' => 'hello', 'integerTrafo' => 1)));

		$this->assertEquals(array('stringTrafo' => 'hello', 'integerTrafo' => 1), $result->value());
	}

	/**
	 * @throws \ilException
	 */
	public function testApplyIsInvalidBecauseValueDoesNotMatchWithTransformation()
	{
		$recordTransformation = new RecordTransformation(
			array(
				'integerTrafo' => new IntegerTransformation(),
				'anotherIntTrafo' => new IntegerTransformation())
		);

		$result = $recordTransformation->applyTo(new Ok(array('stringTrafo' => 'hello', 'anotherIntTrafo' => 1)));

		$this->assertTrue($result->isError());
	}

	/**
	 * @throws \ilException
	 */
	public function testInvalidValueKeyInApplyToMethod()
	{
		$recordTransformation = new RecordTransformation(
			array(
				'stringTrafo' => new StringTransformation(),
				'integerTrafo' => new IntegerTransformation())
		);

		$result = $recordTransformation->applyTo(new Ok(array('stringTrafo' => 'hello', 'floatTrafo' => 1)));

		$this->assertTrue($result->isError());
	}

	/**
	 * @throws \ilException
	 */
	public function testInvalidToManyValuesInApplyToMethodCall()
	{
		$recordTransformation = new RecordTransformation(
			array(
				'stringTrafo' => new StringTransformation(),
				'integerTrafo' => new IntegerTransformation())
		);

		$result = $recordTransformation->applyTo(
			new Ok(
				array(
					'stringTrafo' => 'hello',
					'integerTrafo' => 1,
					'floatTrafo' => 1
				)
			)
		);

		$this->assertTrue($result->isError());
	}

	/**
	 * @throws \ilException
	 */
	public function testApplyThrowsExceptionBecauseKeyIsNotAString()
	{
		$recordTransformation = new RecordTransformation(
			array(
				'stringTrafo' => new StringTransformation(),
				'integerTrafo' => new IntegerTransformation())
		);

		$result = $recordTransformation->applyTo(new Ok(array('someKey' => 'hello', 1)));

		$this->assertTrue($result->isError());
	}
}
