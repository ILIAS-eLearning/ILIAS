<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");


use ILIAS\UI\Implementation\Component\SignalGenerator;
use \ILIAS\UI\Implementation\Component\Input\NameSource;
use \ILIAS\UI\Component\Input\Field;
use \ILIAS\Data;
use \ILIAS\Validation;
use \ILIAS\Transformation;

class DurationInputTest extends ILIAS_UI_TestBase {

	public function setUp(): void
	{
		$this->name_source = new DefNamesource();
		$this->data_factory = new Data\Factory();
		$this->factory = $this->buildFactory();
	}

	protected function buildFactory() {
		$df = new Data\Factory();
		return new ILIAS\UI\Implementation\Component\Input\Field\Factory(
			new SignalGenerator(),
			$this->data_factory,
			new Validation\Factory(
				$this->data_factory,
				$this->createMock(\ilLanguage::class)
			),
			new Transformation\Factory()
		);
	}

	public function test_withFormat() {
		$format = $this->data_factory->date_format()->germanShort();
		$duration = $this->factory->duration('label', 'byline')
			->withFormat($format);

		$this->assertEquals(
			$format,
			$duration->getFormat()
		);
	}

	public function test_withMinValue() {
		$dat = new \DateTime('2019-01-09');
		$duration = $this->factory->duration('label', 'byline')
			->withMinValue($dat);

		$this->assertEquals(
			$dat,
			$duration->getMinValue()
		);
	}

	public function test_withMaxValue() {
		$dat = new \DateTime('2019-01-09');
		$duration = $this->factory->duration('label', 'byline')
			->withMaxValue($dat);

		$this->assertEquals(
			$dat,
			$duration->getMaxValue()
		);
	}

	public function test_withTime() {
		$datetime = $this->factory->duration('label', 'byline');
		$this->assertFalse($datetime->getUseTime());
		$this->assertTrue($datetime->withTime(true)->getUseTime());
	}

	public function test_withTimeOnly() {
		$datetime = $this->factory->duration('label', 'byline');
		$this->assertFalse($datetime->getTimeOnly());
		$this->assertTrue($datetime->withTimeOnly(true)->getTimeOnly());
	}

}
