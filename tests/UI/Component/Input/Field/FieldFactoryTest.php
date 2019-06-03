<?php

require_once 'tests/UI/AbstractFactoryTest.php';

use \ILIAS\UI\Component\Input\Field;
use ILIAS\UI\Implementation\Component\SignalGenerator;
use \ILIAS\Data;
use ILIAS\Refinery;

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
		"optionalGroup" => array(
			"context" => false,
		),
		"checkbox"       => array(
			"context" => false,
		),
		"select"		=> array(
			"context" => false,
		),
		"textarea"	=> array(
			"context" => false,
		),
		"radio"			=> array(
			"context" => false,
		),
		"multiSelect"	=> array(
			"context" => false,
		)
	);
	public $factory_title = 'ILIAS\\UI\\Component\\Input\\Field\\Factory';


	final public function buildFactory() {
		$df = new Data\Factory();
		$language = $this->createMock(\ilLanguage::class);
		return new \ILIAS\UI\Implementation\Component\Input\Field\Factory(
			new SignalGenerator(),
			$df,
			new \ILIAS\Refinery\Factory($df, $language)
		);
	}


	public function test_implements_factory_interface() {
		$f = $this->buildFactory();

		$input = $f->text("label", "byline");
		$this->assertInstanceOf(Field\Input::class, $input);
		$this->assertInstanceOf(Field\Text::class, $input);

		$input = $f->numeric("label", "byline");
		$this->assertInstanceOf(Field\Input::class, $input);
		$this->assertInstanceOf(Field\Numeric::class, $input);

		$input = $f->section([], "label", "byline");
		$this->assertInstanceOf(Field\Input::class, $input);
		$this->assertInstanceOf(Field\Group::class, $input);
		$this->assertInstanceOf(Field\Section::class, $input);

		$input = $f->group([]);
		$this->assertInstanceOf(Field\Input::class, $input);
		$this->assertInstanceOf(Field\Group::class, $input);

		$input = $f->dependantGroup([]);
		$this->assertInstanceOf(Field\Input::class, $input);
		$this->assertInstanceOf(Field\Group::class, $input);
		$this->assertInstanceOf(Field\DependantGroup::class, $input);

		$input = $f->checkbox("label", "byline");
		$this->assertInstanceOf(Field\Input::class, $input);
		$this->assertInstanceOf(Field\Checkbox::class, $input);

		$input = $f->tag( "label", [],"byline");
		$this->assertInstanceOf(Field\Input::class, $input);
		$this->assertInstanceOf(Field\Tag::class, $input);

		$input = $f->password("label", "byline");
		$this->assertInstanceOf(Field\Input::class, $input);
		$this->assertInstanceOf(Field\Password::class, $input);

		$input = $f->select("label",[],  "byline");
		$this->assertInstanceOf(Field\Input::class, $input);
		$this->assertInstanceOf(Field\Select::class, $input);

		$input = $f->textarea( "label", "byline");
		$this->assertInstanceOf(Field\Input::class, $input);
		$this->assertInstanceOf(Field\Textarea::class, $input);

		$input = $f->radio("label", "byline");
		$this->assertInstanceOf(Field\Input::class, $input);
		$this->assertInstanceOf(Field\Radio::class, $input);

		$input = $f->multiSelect("label", [], "byline");
		$this->assertInstanceOf(Field\Input::class, $input);
		$this->assertInstanceOf(Field\MultiSelect::class, $input);
	}

	public function test_implements_factory_no_by_line() {
		$f = $this->buildFactory();

		$input = $f->text("label");
		$this->assertInstanceOf(Field\Input::class, $input);
		$this->assertInstanceOf(Field\Text::class, $input);

		$input = $f->numeric("label");
		$this->assertInstanceOf(Field\Input::class, $input);
		$this->assertInstanceOf(Field\Numeric::class, $input);

		$input = $f->section([], "label");
		$this->assertInstanceOf(Field\Input::class, $input);
		$this->assertInstanceOf(Field\Group::class, $input);
		$this->assertInstanceOf(Field\Section::class, $input);

		$input = $f->checkbox("label");
		$this->assertInstanceOf(Field\Input::class, $input);
		$this->assertInstanceOf(Field\Checkbox::class, $input);

		$input = $f->tag( "label", []);
		$this->assertInstanceOf(Field\Input::class, $input);
		$this->assertInstanceOf(Field\Tag::class, $input);

		$input = $f->password("label");
		$this->assertInstanceOf(Field\Input::class, $input);
		$this->assertInstanceOf(Field\Password::class, $input);

		$input = $f->select("label",[]);
		$this->assertInstanceOf(Field\Input::class, $input);
		$this->assertInstanceOf(Field\Select::class, $input);

		$input = $f->textarea( "label");
		$this->assertInstanceOf(Field\Input::class, $input);
		$this->assertInstanceOf(Field\Textarea::class, $input);

		$input = $f->radio("label");
		$this->assertInstanceOf(Field\Input::class, $input);
		$this->assertInstanceOf(Field\Radio::class, $input);

		$input = $f->multiSelect("label", []);
		$this->assertInstanceOf(Field\Input::class, $input);
		$this->assertInstanceOf(Field\MultiSelect::class, $input);
	}
}
