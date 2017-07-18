<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__."/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__."/../../Base.php");

use \ILIAS\UI\Implementation\Component\Input\Input;

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
		$url = "MY_URL";
		$form = $f->standard($url, []);

		$r = $this->getDefaultRenderer();
		$html = $this->normalizeHTML($r->render($form));

		$this->assertCount(1, $ids);
		$name = $ids[0];

		$expected =
			"<form role=\"form\" class=\"form-horizontal\" enctype=\"multipart/formdata\" action=\"$url\" method=\"post\" novalidate=\"novalidate\">".
			"	<div class=\"ilFormHeader\">".
			"		<div class=\"ilFormCmds\">$button</div>".
			"	</div>".
			"</form>";
		$this->assertEquals($expected, $html);
	}
}
