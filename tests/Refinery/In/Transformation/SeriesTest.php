<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Tests\Refinery\In\Transformation;

use ILIAS\Data\Result\Ok;
use ILIAS\Refinery\In\Series;
use ILIAS\Refinery\To\Transformation\IntegerTransformation;
use ILIAS\Refinery\To\Transformation\StringTransformation;
use ILIAS\Refinery\ConstraintViolationException;
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

	public function testSeriesApplyTo()
	{
		$series = new Series(array(
			new StringTransformation(),
			new StringTransformation()
		));

		$result = $series->applyTo(new Ok('hello'));

		$this->assertEquals('hello', $result->value());
	}

	public function testSeriesTransformationFails()
	{
		$this->expectNotToPerformAssertions();

		$series = new Series(array(
			new IntegerTransformation(),
			new StringTransformation()
		));

		try {
			$result = $series->transform(42.0);
		} catch (ConstraintViolationException $exception) {
			return;
		}

		$this->fail();
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

	public function testInvalidTransformationThrowsException()
	{
		$this->expectNotToPerformAssertions();

		try {
			$parallel = new Series(array(
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
