<?php

require_once 'tests/UI/AbstractFactoryTest.php';

use \ILIAS\UI\Component\Input\Field;
use ILIAS\UI\Implementation\Component\SignalGenerator;

class FieldFactoryTest extends AbstractFactoryTest {

	public $kitchensink_info_settings = array(
		"text"           => array(
			"context" => false,
		),
		"numeric"        => array(
			"context" => false,
		),
		"group"          => array(
			"context" => false,
		),
		"section"        => array(
			"context" => false,
		),
		"dependantGroup" => array(
			"context" => false,
		),
		"checkbox"       => array(
			"context" => false,
		),
	);
	public $factory_title = 'ILIAS\\UI\\Component\\Input\\Field\\Factory';


	final public function buildFactory() {
		return new \ILIAS\UI\Implementation\Component\Input\Field\Factory(new SignalGenerator());
	}


	public function test_implements_factory_interface() {
		$f = $this->buildFactory();

		$text = $f->text("label", "byline");
		$this->assertInstanceOf(Field\Input::class, $text);
		$this->assertInstanceOf(Field\Text::class, $text);

		$text = $f->numeric("label", "byline");
		$this->assertInstanceOf(Field\Input::class, $text);
		$this->assertInstanceOf(Field\Numeric::class, $text);

		$text = $f->section([], "label", "byline");
		$this->assertInstanceOf(Field\Input::class, $text);
		$this->assertInstanceOf(Field\Group::class, $text);
		$this->assertInstanceOf(Field\Section::class, $text);

		$text = $f->group([]);
		$this->assertInstanceOf(Field\Input::class, $text);
		$this->assertInstanceOf(Field\Group::class, $text);
	}
}
