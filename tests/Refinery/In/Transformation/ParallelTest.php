<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Tests\Refinery\In\Transformation;

use ILIAS\BackgroundTasks\Exceptions\InvalidArgumentException;
use ILIAS\Data\Result\Ok;
use ILIAS\In\Transformation\Parallel;
use ILIAS\Refinery\To\Transformation\FloatTransformation;
use ILIAS\Refinery\To\Transformation\IntegerTransformation;
use ILIAS\Refinery\To\Transformation\StringTransformation;
use ILIAS\Tests\Refinery\TestCase;

require_once('./libs/composer/vendor/autoload.php');


class ParallelTest extends TestCase
{
	public function testParallelTransformation()
	{
		$parallel = new Parallel(
			array(
				new StringTransformation(),
				new StringTransformation()
			)
		);

		$result = $parallel->transform('hello');

		$this->assertEquals(array('hello', 'hello'), $result);
	}

	public function testParallelTransformationWithKindlyToTransformations()
	{
		$parallel = new Parallel(
			array(
				new \ILIAS\Refinery\KindlyTo\Transformation\StringTransformation(),
				new \ILIAS\Refinery\KindlyTo\Transformation\IntegerTransformation(),
				new \ILIAS\Refinery\KindlyTo\Transformation\FloatTransformation()
			)
		);

		$result = $parallel->transform(42.3);

		$this->assertEquals(array('42.3', 42, 42.3), $result);
	}

	public function testParallelTransformationForApplyTo()
	{
		$parallel = new Parallel(
			array(
				new StringTransformation(),
				new StringTransformation()
			)
		);

		$result = $parallel->applyTo(new Ok('hello'));

		$this->assertEquals(array('hello', 'hello'), $result->value());
	}

	public function testParallelTransformationForApplyToWithKindlyToTransformations()
	{
		$parallel = new Parallel(
			array(
				new \ILIAS\Refinery\KindlyTo\Transformation\StringTransformation(),
				new \ILIAS\Refinery\KindlyTo\Transformation\IntegerTransformation(),
				new \ILIAS\Refinery\KindlyTo\Transformation\FloatTransformation()
			)
		);

		$result = $parallel->applyTo(new Ok(42.3));

		$this->assertEquals(array('42.3', 42, 42.3), $result->value());
	}


	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testParallelTransformationFailsBecauseOfInvalidType()
	{
		$parallel = new Parallel(array(new StringTransformation()));

		$result = $parallel->transform(42.0);

		$this->fail();
	}

	public function testParallelApply()
	{
		$parallel = new Parallel(array(
				new StringTransformation(),
				new IntegerTransformation(),
				new FloatTransformation()
			)
		);

		$result = $parallel->applyTo(new Ok(42));

		$this->assertTrue($result->isError());
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testInvalidTransformationThrowsException()
	{
		$parallel = new Parallel(array(
				new StringTransformation(),
				'this is invalid'
			)
		);

		$this->fail();
	}
}
