<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Refinery\In\Transformation;

use ILIAS\Data\Result\Ok;
use ILIAS\In\Transformation\Series;
use ILIAS\Refinery\To\Transformation\IntegerTransformation;
use ILIAS\Refinery\To\Transformation\StringTransformation;

require_once('./libs/composer/vendor/autoload.php');

class SeriesTest extends \PHPUnit_Framework_TestCase
{
	public function testSeriesTransformation()
	{
		$series = new Series(array(new StringTransformation()));

		$result = $series->transform('hello');

		$this->assertEquals('hello', $result);
	}
	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testSeriesTransformationFails()
	{
		$series = new Series(array(
			new IntegerTransformation(),
			new StringTransformation()
		));

		$result = $series->transform(42.0);

		$this->assertEquals('42', $result);
	}

	/**
	 * @throws \ilException
	 */
	public function testSeriesApply()
	{
		$series = new Series(array(
			new IntegerTransformation(),
			new StringTransformation()
		));

		$result = $series->applyTo(new Ok(42.0));

		$this->assertTrue($result->isError());
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testInvalidTransformationThrowsException()
	{
		$parallel = new Series(array(
				new StringTransformation(),
				'this is invalid'
			)
		);

		$this->fail();
	}
}
