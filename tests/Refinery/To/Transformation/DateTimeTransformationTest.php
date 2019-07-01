<?php

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Refinery\To\Transformation;

require_once('./libs/composer/vendor/autoload.php');

use ILIAS\Refinery\To\Transformation\DateTimeTransformation;
use PHPUnit\Framework\TestCase as TestCase;
use ILIAS\Data\Factory;
use ILIAS\Data\Result;
/**
 * TestCase for DateTime transformations
 */
class DateTimeTransformationTest extends TestCase
{
	/**
	 * @var Refinery\To\Transformations\DateTimeTransformation
	 */
	private $trans;

	protected function setUp(): void
	{
		$df = new Factory();
		$this->trans = new DateTimeTransformation($df);
	}

	public function testTransform()
	{
		$value = '26.05.1977';
		$expected = new \DateTimeImmutable($value);

		$this->assertEquals(
			$expected,
			$this->trans->transform($value)
		);
	}

	public function testNullTransform()
	{
		$this->assertNull($this->trans->transform(null));
	}

	public function testInvalidTransform()
	{
		$this->expectException(\InvalidArgumentException::class);
		$this->trans->transform('erroneous');
	}

	public function testInvoke()
	{
		$value = '2019/05/26';
		$expected = new \DateTimeImmutable($value);
		$t = $this->trans;

		$this->assertEquals($expected, $t($value));
	}

	public function testApplyToOK()
	{
		$value = '2019/05/26';
		$expected = new \DateTimeImmutable($value);

		$df = new \ILIAS\Data\Factory();
		$ok = $df->ok($expected);

		$result = $this->trans->applyTo($ok);
		$this->assertEquals($expected, $result->value());
		$this->assertFalse($result->isError());
	}

	public function testApplyToFail()
	{
		$df = new \ILIAS\Data\Factory();
		$ok = $df->ok('not_a_date');

		$result = $this->trans->applyTo($ok);
		$this->assertTrue($result->isError());
	}
}
