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

require_once('./libs/composer/vendor/autoload.php');


class ParallelTest extends \PHPUnit_Framework_TestCase
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

		$this->assertEquals($result, array('hello', 'hello'));
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
