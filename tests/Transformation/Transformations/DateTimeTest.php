<?php

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Refinery\Transformation;
use PHPUnit\Framework\TestCase;

/**
 * TestCase for DateTime transformations
 */
class DateTimeTest extends TestCase {
	/**
	 * @var Transformation\Transformations\Date
	 */
	private $trans;

	protected function setUp(): void
	{
		$f = new Transformation\Factory();
		$this->trans = $f->toDateTime();
	}

	public function testTransform()
	{
		$value = '26.05.1977';
		$expected = new \DateTime($value);

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
		$expected = new \DateTime($value);
		$t = $this->trans;

		$this->assertEquals($expected, $t($value));
	}

	public function testApplyToOK()
	{
		$value = '2019/05/26';
		$expected = new \DateTime($value);

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
