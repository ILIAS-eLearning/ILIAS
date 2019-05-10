<?php

/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */
require_once("libs/composer/vendor/autoload.php");

use ILIAS\Refinery\String\Constraints\HasMaxLength;
use ILIAS\Refinery\String\Constraints\HasMinLength;
use ILIAS\Refinery\Validation;
use ILIAS\Data;
use PHPUnit\Framework\TestCase;

/**
 * TestCase for the factory of constraints
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ValidationFactoryTest extends TestCase {
	/**
	 * @var Validation\Factory
	 */
	protected $f = null;

	/**
	 * @var \ilLanguage
	 */
	private $lng;

	/**
	 * @var Data\Factory
	 */
	private $data_factory;

	/**
	 * @var \ILIAS\Refinery\Factory
	 */
	private $refinery;

	protected function setUp(): void{
		$this->lng = $this->createMock(\ilLanguage::class);
		$this->data_factory = new Data\Factory();
		$this->f = new Validation\Factory($this->data_factory, $this->lng);
		$this->refinery = new \ILIAS\Refinery\Factory($this->data_factory, $this->lng);
	}

	protected function tearDown(): void {
		$this->f = null;
	}

	public function testIsNumeric() {
		$is_numeric = $this->f->isNumeric();
		$this->assertInstanceOf(Validation\Constraint::class, $is_numeric);
	}

	public function testCustom() {
		$custom = $this->refinery->custom()->constraint(function ($value) { return "This was fault";}, 5);
		$this->assertInstanceOf(Validation\Constraint::class, $custom);
	}

	public function testLoadsLanguageModule() {
		$lng = $this->createMock(\ilLanguage::class);

		$lng
			->expects($this->once())
			->method("loadLanguageModule")
			->with(Validation\Factory::LANGUAGE_MODULE);

		new Validation\Factory(new Data\Factory(), $lng);
	}
}
