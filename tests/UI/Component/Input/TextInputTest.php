<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__."/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__."/../../Base.php");
require_once(__DIR__."/InputTest.php");

class TextInputTest extends ILIAS_UI_TestBase {
	public function setUp() {
		$this->name_source = new DefNamesource();
	}

	protected function buildFactory() {
		return new ILIAS\UI\Implementation\Component\Input\Factory;
	}

	public function test_implements_factory_interface() {
	    $f = $this->buildFactory();

		$text = $f->text("label", "byline");
	}

	public function test_render() {
	    $f = $this->buildFactory();
		$label = "label";
		$byline = "byline";
		$name = "name_0";
		$text = $f->text($label, $byline)
			->withNameFrom($this->name_source);

		$r = $this->getDefaultRenderer();
		$html = $this->normalizeHTML($r->render($text));

		$expected =
				"<div class=\"form-group row\">".
				"	<label for=\"$name\" class=\"control-label col-sm-3\">$label</label>".
				"	<div class=\"col-sm-9\">".
				"		<input type=\"text\" name=\"$name\" class=\"form-control form-control-sm\" />".
				"		<div class=\"help-block\">$byline</div>".
				"		".
				"	</div>".
				"</div>";
		$this->assertEquals($expected, $html);
	}

	public function test_render_error() {
	    $f = $this->buildFactory();
		$label = "label";
		$byline = "byline";
		$name = "name_0";
		$error = "an_error";
		$text = $f->text($label, $byline)
			->withNameFrom($this->name_source)
			->withError($error);

		$r = $this->getDefaultRenderer();
		$html = $this->normalizeHTML($r->render($text));

		$expected =
				"<div class=\"form-group row\">".
				"	<label for=\"$name\" class=\"control-label col-sm-3\">$label</label>".
				"	<div class=\"col-sm-9\">".
				"		<input type=\"text\" name=\"$name\" class=\"form-control form-control-sm\" />".
				"		<div class=\"help-block\">$byline</div>".
				"		<div class=\"help-block alert alert-danger\" role=\"alert\">".
				"			<img border=\"0\" src=\"./templates/default/images/icon_alert.svg\" alt=\"alert\">".
				"			$error".
				"		</div>".
				"	</div>".
				"</div>";
		$this->assertEquals($expected, $html);
	}

	public function test_render_no_byline() {
	    $f = $this->buildFactory();
		$label = "label";
		$name = "name_0";
		$text = $f->text($label)
			->withNameFrom($this->name_source);

		$r = $this->getDefaultRenderer();
		$html = $this->normalizeHTML($r->render($text));

		$expected =
				"<div class=\"form-group row\">".
				"	<label for=\"$name\" class=\"control-label col-sm-3\">$label</label>".
				"	<div class=\"col-sm-9\">".
				"		<input type=\"text\" name=\"$name\" class=\"form-control form-control-sm\" />".
				"		".
				"		".
				"	</div>".
				"</div>";
		$this->assertEquals($expected, $html);
	}

	public function test_render_value() {
	    $f = $this->buildFactory();
		$label = "label";
		$value = "value";
		$name = "name_0";
		$text = $f->text($label)
			->withValue($value)
			->withNameFrom($this->name_source);

		$r = $this->getDefaultRenderer();
		$html = $this->normalizeHTML($r->render($text));

		$expected =
				"<div class=\"form-group row\">".
				"	<label for=\"$name\" class=\"control-label col-sm-3\">$label</label>".
				"	<div class=\"col-sm-9\">".
				"		<input type=\"text\" value=\"$value\" name=\"$name\" class=\"form-control form-control-sm\" />".
				"		".
				"		".
				"	</div>".
				"</div>";
		$this->assertEquals($expected, $html);
	}
}
