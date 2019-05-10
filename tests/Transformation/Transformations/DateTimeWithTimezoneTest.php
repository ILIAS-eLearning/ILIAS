<?php

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Refinery\Transformation;
use PHPUnit\Framework\TestCase;

/**
 * TestCase for timezone transformation
 */
class DateTimeWithTimezoneTest extends TestCase
{
	protected function setUp(): void
	{
		$this->tf = new Transformation\Factory();
	}

	public function testConstruction()
	{
		$tz = 'America/El_Salvador';
		$trans = $this->tf->toDateTimeWithTimezone($tz);
		$this->assertInstanceOf(
			Transformation\Transformations\DateTimeWithTimezone::class,
			$trans
		);
	}
	public function testWrongConstruction()
	{
		$this->expectException(\InvalidArgumentException::class);
		$tz = 'MiddleEarth/Minas_Morgul';
		$trans = $this->tf->toDateTimeWithTimezone($tz);
	}

	public function testTransform()
	{
		$dat = '2019-05-26 13:15:01';
		$origin_tz = 'Europe/Berlin';
		$target_tz = 'Europe/London';
		$origin = new \DateTimeImmutable($dat, new \DateTimeZone($origin_tz));
		$expected = new \DateTimeImmutable($dat, new \DateTimeZone($target_tz));
		$trans = $this->tf->toDateTimeWithTimezone($target_tz);

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
		$origin = new \DateTimeImmutable($dat, new \DateTimeZone($origin_tz));
		$trans = $this->tf->toDateTimeWithTimezone($target_tz);
		$this->assertEquals(
			$dat,
			date_format($trans->transform($origin), 'Y-m-d H:i:s')
		);
	}

	public function testNullTransform()
	{
		$trans = $this->tf->toDateTimeWithTimezone('Europe/Berlin');
		$this->assertNull($trans->transform(null));
	}

	public function testInvalidTransform()
	{
		$this->expectException(\InvalidArgumentException::class);
		$trans = $this->tf->toDateTimeWithTimezone('Europe/Berlin');
		$trans->transform('erroneous');
	}

	public function testInvoke()
	{
		$dat = '2019/05/26 16:05:22';
		$origin_tz = 'Europe/Berlin';
		$target_tz = 'Europe/London';
		$origin = new \DateTimeImmutable($dat, new \DateTimeZone($origin_tz));
		$expected = new \DateTimeImmutable($dat, new \DateTimeZone($target_tz));
		$trans = $this->tf->toDateTimeWithTimezone($target_tz);
		$this->assertEquals($expected, $trans($origin));
	}

	public function testApplyToOK()
	{
		$trans = $this->tf->toDateTimeWithTimezone('Europe/London');
		$value = '2019/05/26';
		$origin = new \DateTimeImmutable($value);
		$expected = new \DateTimeImmutable($value, new \DateTimeZone('Europe/London'));

		$df = new \ILIAS\Data\Factory();
		$ok = $df->ok($origin);

		$result = $trans->applyTo($ok);
		$this->assertEquals($expected, $result->value());
		$this->assertFalse($result->isError());
	}

	public function testApplyToFail()
	{
		$trans = $this->tf->toDateTimeWithTimezone('Europe/London');
		$df = new \ILIAS\Data\Factory();
		$ok = $df->ok('not_a_date');

		$result = $trans->applyTo($ok);
		$this->assertTrue($result->isError());
	}
}
