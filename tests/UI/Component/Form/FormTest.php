<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__."/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__."/../../Base.php");

use \ILIAS\UI\Implementation\Component\Input\Input;

class WithButtonNoUIFactory extends NoUIFactory {
	protected $button_factory;
	public function __construct($button_factory) {
		$this->button_factory = $button_factory;
	}
	public function button() {
		return $this->button_factory;
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
		$url = "MY_URL";
		$form = $f->standard($url, 
			[ $if->text("label", "byline")
			]);

		$r = $this->getDefaultRenderer();
		$html = $this->normalizeHTML($r->render($form));

		$button = $this->normalizeHTML(str_replace('">', '" id="id_1">', $r->render($bf->standard("save", "#"))));
		$input = $this->normalizeHTML($r->render($if->text("label", "byline")->withName("name_0")));

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
