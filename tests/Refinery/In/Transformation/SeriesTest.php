<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Tests\Refinery\In\Transformation;

use ILIAS\Data\Result\Ok;
use ILIAS\In\Transformation\Series;
use ILIAS\Refinery\To\Transformation\IntegerTransformation;
use ILIAS\Refinery\To\Transformation\StringTransformation;
use ILIAS\Tests\Refinery\TestCase;

require_once('./libs/composer/vendor/autoload.php');

class SeriesTest extends TestCase
{
	public function testSeriesTransformation()
	{
		$series = new Series(array(new StringTransformation()));

		$result = $series->transform('hello');

		$this->assertEquals('hello', $result);
	}

	public function testSeriesTransformationWithKindlyToTransformations()
	{
		$series = new Series(
			array(
				new \ILIAS\Refinery\KindlyTo\Transformation\FloatTransformation(),
				new \ILIAS\Refinery\KindlyTo\Transformation\IntegerTransformation(),
				new \ILIAS\Refinery\KindlyTo\Transformation\StringTransformation()
			)
		);

		$result = $series->transform(42.3);

		$this->assertEquals('42', $result);
	}

	public function testSeriesApplyTo()
	{
		$series = new Series(array(
			new StringTransformation(),
			new StringTransformation()
		));

		$result = $series->applyTo(new Ok('hello'));

		$this->assertEquals('hello', $result->value());
	}

	public function testSeriesApplyToWithKindlyToTransformations()
	{
		$series = new Series(
			array(
				new \ILIAS\Refinery\KindlyTo\Transformation\FloatTransformation(),
				new \ILIAS\Refinery\KindlyTo\Transformation\IntegerTransformation(),
				new \ILIAS\Refinery\KindlyTo\Transformation\StringTransformation()
			)
		);

		$result = $series->applyTo(new Ok(42.3));

		$this->assertEquals('42', $result->value());
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
