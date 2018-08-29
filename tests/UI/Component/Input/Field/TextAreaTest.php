<?php

/* Copyright (c) 2018 Jesús López <lopez@leifos.com> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");
require_once(__DIR__ . "/InputTest.php");

use ILIAS\UI\Implementation\Component\SignalGenerator;

class TextAreaTest extends ILIAS_UI_TestBase {

	/**
	 * @var DefNamesource
	 */
	private $name_source;

	public function setUp() {
		$this->name_source = new DefNamesource();
	}

	protected function buildFactory() {
		return new ILIAS\UI\Implementation\Component\Input\Field\Factory(new SignalGenerator());
	}

	public function test_implements_factory_interface() {
		$f = $this->buildFactory();
		$textarea = $f->textArea("label", "byline");
	}

	public function test_implements_factory_interface_without_byline() {
		$f = $this->buildFactory();
		$textarea = $f->textArea("label");
	}

	public function test_renderer() {
		$f = $this->buildFactory();
		$label = "label";
		$byline = "byline";
		$textarea = $f->textArea($label, $byline);

		$r = $this->getDefaultRenderer();

		$expected = "<div class=\"form-group\" id=\"il_prop_cont_atxt\">"
			."<label for=\"atxt\" class=\"col-sm-3 control-label\">$label</label>"
			."<div class=\"col-sm-9\">"
			."<textarea class=\"form-control \" name=\"atxt\" id=\"atxt\" rows=\"40\" required=\"required\" onkeyup=\"return il.Form.showCharCounterTextarea('atxt','textarea_feedback_atxt','','')\" style=\"width: 841px; height: 68px;\" wrap=\"virtual\"></textarea>"
			."<p id=\"charcounter\" style=\"display:none;\"><input spellcheck=\"false\" autocomplete=\"off\" autocorrect=\"off\" autocapitalize=\"off\" readonly=\"readonly\" id=\"myCounter\" name=\"myCounter\" style=\"border: 0; background: transparent;\" size=\"\" value=\"\" type=\"text\"></p>"
			."</div>"
			."</div>";

		$html = $this->normalizeHTML($r->render($textarea));
		$this->assertHTMLEquals($expected, $html);
	}

	//renderer with min/max/error/counter
}