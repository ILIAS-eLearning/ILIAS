<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__."/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__."/../../Base.php");

use \ILIAS\UI\Implementation\Component\Input\Input;
use \ILIAS\UI\Implementation\Component\Input\NameSource;

class WithButtonNoUIFactory extends NoUIFactory {
	protected $button_factory;
	public function __construct($button_factory) {
		$this->button_factory = $button_factory;
	}
	public function button() {
		return $this->button_factory;
	}
}

class FixedNameSource implements NameSource {
	public $name = "name";
	public function getNewName() {
		return $this->name;
	}
}

/**
 * Test on form implementation.
 */
class FormTest extends ILIAS_UI_TestBase {
	protected function buildFactory() {
		return new ILIAS\UI\Implementation\Component\Form\Factory;
	}

	protected function buildInputFactory() {
		return new ILIAS\UI\Implementation\Component\Input\Factory;
	}

	protected function buildButtonFactory() {
		return new ILIAS\UI\Implementation\Component\Button\Factory;
	}

	public function getUIFactory() {
		return new WithButtonNoUIFactory($this->buildButtonFactory());
	}

	public function test_getInputs () {
	    $f = $this->buildFactory();
		$if = $this->buildInputFactory();
		$url = "MY_URL";
		$form = $f->standard($url, [$if->text("label")]);
		$this->assertEquals([$if->text("label")], $form->getInputs());
	}

	public function test_getNamedInputs () {
	    $f = $this->buildFactory();
		$if = $this->buildInputFactory();
		$name_source = new FixedNameSource();

		$inputs = [$if->text(""), $if->text("")];
		$form = $f->standard("", $inputs);

		$seen_names = [];
		$named_inputs = $form->getNamedInputs();
		$this->assertEquals(count($inputs), count($named_inputs));

		foreach($named_inputs as $named_input) {
			$name = $named_input->getName();
			$name_source->name = $name;

			// name is a string
			$this->assertInternalType("string", $name);

			// only name is attached
			$input = array_shift($inputs);
			$this->assertEquals($input->withNameFrom($name_source), $named_input);

			// every name can only be contained once.
			$this->assertNotContains($name, $seen_names);
			$seen_names[] = $name;
		}
	}

	public function test_getPostURL () {
	    $f = $this->buildFactory();
		$if = $this->buildInputFactory();
		$url = "MY_URL";
		$form = $f->standard($url, [$if->text("label")]);
		$this->assertEquals($url, $form->getPostURL());
	}

	public function test_render() {
	    $f = $this->buildFactory();
		$bf = $this->buildButtonFactory();
		$if = $this->buildInputFactory();
		$name_source = new FixedNameSource();

		$url = "MY_URL";
		$form = $f->standard($url, 
			[ $if->text("label", "byline")
			]);

		$r = $this->getDefaultRenderer();
		$html = $this->normalizeHTML($r->render($form));

		$button = $this->normalizeHTML(str_replace('">', '" id="id_1">', $r->render($bf->standard("save", "#"))));
		$name_source->name = "name_0";
		$input = $this->normalizeHTML($r->render($if->text("label", "byline")->withNameFrom($name_source)));

		$expected =
			"<form role=\"form\" class=\"form-horizontal\" enctype=\"multipart/formdata\" action=\"$url\" method=\"post\" novalidate=\"novalidate\">".
			"	<div class=\"ilFormHeader\">".
			"		<div class=\"ilFormCmds\">$button</div>".
			"	</div>".
			"	".$input.
			"</form>";
		$this->assertEquals($expected, $html);
	}
}
