<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Refinery\KindlyTo\Refinery;

use ILIAS\Data\Result\Ok;
use ILIAS\Refinery\KindlyTo\Transformation\RecordTransformation;
use ILIAS\Refinery\KindlyTo\Transformation\IntegerTransformation;
use ILIAS\Refinery\KindlyTo\Transformation\StringTransformation;

require_once('./libs/composer/vendor/autoload.php');

class RecordTransformationTest extends \PHPUnit_Framework_TestCase
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


	/**
	 * @expectedException \ilException
	 */
	public function testInvalidTransformationArray()
	{
		$recordTransformation = new RecordTransformation(
			array(
				new StringTransformation(),
				new IntegerTransformation())
		);

		$this->fail();
	}

	/**
	 * @expectedException \ilException
	 */
	public function testTransformationIsInvalidBecauseValueDoesNotMatchWithTransformation()
	{
		$recordTransformation = new RecordTransformation(
			array(
				'integerTrafo' => new IntegerTransformation(),
				'anotherIntTrafo' => new IntegerTransformation())
		);

		$result = $recordTransformation->transform(array('stringTrafo' => 'hello', 'anotherIntTrafo' => 1));

		$this->fail();
	}

	/**
	 * @expectedException \ilException
	 */
	public function testInvalidValueKey()
	{
		$recordTransformation = new RecordTransformation(
			array(
				'stringTrafo' => new StringTransformation(),
				'integerTrafo' => new IntegerTransformation())
		);

		$result = $recordTransformation->transform(array('stringTrafo' => 'hello', 'floatTrafo' => 1));

		$this->fail();
	}

	/**
	 * @expectedException \ilException
	 */
	public function testInvalidToManyValues()
	{
		$recordTransformation = new RecordTransformation(
			array(
				'stringTrafo' => new StringTransformation(),
				'integerTrafo' => new IntegerTransformation())
		);

		$result = $recordTransformation->transform(
			array(
				'stringTrafo' => 'hello',
				'integerTrafo' => 1,
				'floatTrafo' => 1
			)
		);

		$this->fail();
	}

	/**
	 * @expectedException \ilException
	 */
	public function testTransformationThrowsExceptionBecauseKeyIsNotAString()
	{
		$recordTransformation = new RecordTransformation(
			array(
				'stringTrafo' => new StringTransformation(),
				'integerTrafo' => new IntegerTransformation())
		);

		$result = $recordTransformation->transform(array('someKey' => 'hello', 1));

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
