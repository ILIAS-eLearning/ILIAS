<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Refinery;

use ILIAS\Data\Result\Ok;
use ILIAS\In\Transformation\Parallel;
use ILIAS\Refinery\To\Transformation\FloatTransformation;
use ILIAS\Refinery\To\Transformation\IntegerTransformation;
use ILIAS\Refinery\To\Transformation\StringTransformation;

require_once('./libs/composer/vendor/autoload.php');


class ParallelTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @throws \ilException
	 */
	public function testParallelTransformation()
	{
		$parallel = new Parallel(array(
				new StringTransformation(),
				new IntegerTransformation(),
				new FloatTransformation()
			)
		);

		$result = $parallel->transform(42.0);

		$this->assertEquals(array('42', 42, 42.0), $result);
	}

	/**
	 * @throws \ilException
	 */
	public function testParallelApply()
	{
		$parallel = new Parallel(array(
				new StringTransformation(),
				new IntegerTransformation(),
				new FloatTransformation()
			)
		);

		$result = $parallel->applyTo(new Ok(42));

		$this->assertEquals(array('42', 42, 42.0), $result->value());
	}

	/**
	 * @expectedException  \ilException
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
