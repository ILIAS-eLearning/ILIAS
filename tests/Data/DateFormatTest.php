<?php

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");

use ILIAS\Data\DateFormat;
use PHPUnit\Framework\TestCase;

class DateFormatTest extends TestCase
{
	public function testFactory()
	{
		$f = new ILIAS\Data\Factory();
		$df = $f->dateFormat();
		$this->assertInstanceOf(DateFormat\Factory::class, $df);
		return $df;
	}

	/**
	 * @depends testFactory
	 */
	public function testDateFormatFactory(DateFormat\Factory $df)
	{
		$this->assertInstanceOf(DateFormat\DateFormat::class, $df->standard());
		$this->assertInstanceOf(DateFormat\DateFormat::class, $df->germanShort());
		$this->assertInstanceOf(DateFormat\DateFormat::class, $df->germanLong());
		$this->assertInstanceOf(DateFormat\FormatBuilder::class, $df->custom());
	}

	/**
	 * @depends testFactory
	 */
	public function testDateFormatBuilderAndGetters(DateFormat\Factory $df)
	{
		$expect = [
			'.', ',', '-', '/', ' ', 'd', 'jS', 'l', 'D', 'W', 'm', 'F', 'M', 'Y', 'y'
		];
		$format = $df->custom()
			->dot()->comma()->dash()->slash()->space()
			->day()->dayOrdinal()->weekday()->weekdayShort()
			->week()->month()->monthSpelled()->monthSpelledShort()
			->year()->twoDigitYear()
			->get();

		$this->assertEquals(
			$expect,
			$format->toArray()
		);

		$this->assertEquals(
			implode('', $expect),
			$format->toString()
		);

	}

	public function testInvalidTokens()
	{
		$this->expectException(\InvalidArgumentException::class);
		new DateFormat\DateFormat(['x', '2']);
	}
}
