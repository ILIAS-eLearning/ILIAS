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
use ILIAS\Refinery\Validation\Constraints\ConstraintViolationException;
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

	public function testParallelTransformationFailsBecauseOfInvalidType()
	{
		$parallel = new Parallel(array(new StringTransformation()));

		try {
			$result = $parallel->transform(42.0);
		} catch (ConstraintViolationException $exception) {
			return;
		}

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

	public function testInvalidTransformationThrowsException()
	{
		try {
			$parallel = new Parallel(array(
					new StringTransformation(),
					'this is invalid'
				)
			);
		} catch (ConstraintViolationException $exception) {
			return;
		}

		$this->fail();
	}
}
