<?php

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Refinery\Transformation;
use PHPUnit\Framework\TestCase;

/**
 * TestCase for timezone transformation
 */
class TZDateTest extends TestCase {
	/**
	 * @var Transformation\Transformations\Date
	 */
	private $trans;

	protected function setUp(): void
	{
		$this->tf = new Transformation\Factory();
	}

	public function testConstruction()
	{
		$tz = 'America/El_Salvador';
		$trans = $this->tf->toTZDate($tz);
		$this->assertInstanceOf(
			Transformation\Transformations\TZDate::class,
			$trans
		);
	}
	public function testWrongConstruction()
	{
		$this->expectException(\InvalidArgumentException::class);
		$tz = 'MiddleEarth/Minas_Morgul';
		$trans = $this->tf->toTZDate($tz);
	}

	public function testTransform()
	{
		$dat = '2019-05-26 13:15:01';
		$origin_tz = 'Europe/Berlin';
		$target_tz = 'Europe/London';
		$origin = new \DateTime($dat, new \DateTimeZone($origin_tz));
		$expected = new \DateTime($dat, new \DateTimeZone($target_tz));
		$trans = $this->tf->toTZDate($target_tz);

		$this->assertEquals(
			$expected,
			$trans->transform($origin)
		);
	}

	public function testTransformValues()
	{
		$dat = '2019-05-26 13:15:01';
		$origin_tz = 'Europe/Berlin';
		$target_tz = 'America/El_Salvador';
		$origin = new \DateTime($dat, new \DateTimeZone($origin_tz));
		$trans = $this->tf->toTZDate($target_tz);
		$this->assertEquals(
			$dat,
			date_format($trans->transform($origin), 'Y-m-d H:i:s')
		);
	}

	public function testNullTransform()
	{
		$trans = $this->tf->toTZDate('Europe/Berlin');
		$this->assertNull($trans->transform(null));
	}

	public function testInvalidTransform()
	{
		$this->expectException(\InvalidArgumentException::class);
		$trans = $this->tf->toTZDate('Europe/Berlin');
		$trans->transform('erroneous');
	}

	public function testInvoke()
	{
		$dat = '2019/05/26 16:05:22';
		$origin_tz = 'Europe/Berlin';
		$target_tz = 'Europe/London';
		$origin = new \DateTime($dat, new \DateTimeZone($origin_tz));
		$expected = new \DateTime($dat, new \DateTimeZone($target_tz));
		$trans = $this->tf->toTZDate($target_tz);
		$this->assertEquals($expected, $trans($origin));
	}

	public function testApplyToOK()
	{
		$trans = $this->tf->toTZDate('Europe/London');
		$value = '2019/05/26';
		$origin = new \DateTime($value);
		$expected = new \DateTime($value, new \DateTimeZone('Europe/London'));

		$df = new \ILIAS\Data\Factory();
		$ok = $df->ok($origin);

		$result = $trans->applyTo($ok);
		$this->assertEquals($expected, $result->value());
		$this->assertFalse($result->isError());
	}

	public function testApplyToFail()
	{
		$trans = $this->tf->toTZDate('Europe/London');
		$df = new \ILIAS\Data\Factory();
		$ok = $df->ok('not_a_date');

		$result = $trans->applyTo($ok);
		$this->assertTrue($result->isError());
	}
}
