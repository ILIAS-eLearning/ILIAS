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

	//TODO fix expected
	public function test_renderer_with_min_limit()
	{
		$f = $this->buildFactory();
		$label = "label";
		$min = 5;
		$byline = "Just a textarea input<br>Minimum: ".$min;
		$textarea = $f->textArea($label, $byline)->withMinLimit($min);

		$r = $this->getDefaultRenderer();

		$expected = "<div class=\"form-group row\">"
			."<label for=\"form_input_1\" class=\"control-label col-sm-3\">$label</label>"
			."<div class=\"col-sm-9\">"
			."<textarea name=\"form_input_1\" class=\"form-control form-control-sm\" id=\"il_ui_fw_5b87d7a6c8a984_60891539\"></textarea>"
			."<div id=\"textarea_feedback_il_ui_fw_5b87d7a6c8a984_60891539\"></div>"
			."<div class=\"help-block\">$byline</div>"
			."</div>"
			."</div>";

		$html = $this->normalizeHTML($r->render($textarea));
		$this->assertHTMLEquals($expected, $html);

	}

	//TODO fix expected
	public function test_renderer_with_max_limit()
	{
		$f = $this->buildFactory();
		$label = "label";
		$max = 10;
		$byline = "Just a textarea input<br>Maximum: ".$max;
		$textarea = $f->textArea($label, $byline)->withMaxLimit($max);
		$r = $this->getDefaultRenderer();

		$expected = "<div class=\"form-group row\">"
			."<label for=\"form_input_1\" class=\"control-label col-sm-3\">$label</label>"
			."<div class=\"col-sm-9\">"
			."<textarea name=\"form_input_1\" class=\"form-control form-control-sm\" id=\"il_ui_fw_5b87d7a6c8a984_60891539\"></textarea>"
			."<div id=\"textarea_feedback_il_ui_fw_5b87d7a6c8a984_60891539\" data-maxchars=\"$max\"></div>"
			."<div class=\"help-block\">$byline</div>"
			."</div>"
			."</div>";

		$html = $this->normalizeHTML($r->render($textarea));
		$this->assertHTMLEquals($expected, $html);
	}

	//TODO fix expected + ID
	public function test_renderer_with_min_and_max_limit()
	{
		$f = $this->buildFactory();
		$label = "label";
		$min = 3;
		$max = 10;
		$byline = "Just a textarea input<br>Minimum: ".$min." Maximum: ".$max;
		$textarea = $f->textArea($label, $byline)->withMinLimit($min)->withMaxLimit($max);

		$r = $this->getDefaultRenderer();
		$expected = "<div class=\"form-group row\">"
			."<label for=\"form_input_1\" class=\"control-label col-sm-3\">$label</label>"
			."<div class=\"col-sm-9\">"
			."<textarea name=\"form_input_1\" class=\"form-control form-control-sm\" id=\"il_ui_fw_5b87d7a6c8a984_60891539\"></textarea>"
			."<div id=\"textarea_feedback_il_ui_fw_5b87d7a6c8a984_60891539\" data-maxchars=\"$max\"></div>"
			."<div class=\"help-block\">$byline</div>"
			."</div>"
			."</div>";

		$html = $this->normalizeHTML($r->render($textarea));
		$this->assertHTMLEquals($expected, $html);
	}

	//TODO fix ID
	public function test_renderer_counter_with_value()
	{
		$f = $this->buildFactory();
		$label = "label";
		$byline = "byline";
		$value = "lorem ipsum";
		$textarea = $f->textArea($label, $byline)->withValue($value);
		$r = $this->getDefaultRenderer();

		$expected = "<div class=\"form-group\" id=\"il_prop_cont_atxt\">"
			."<label for=\"atxt\" class=\"col-sm-3 control-label\">$label</label>"
			."<div class=\"col-sm-9\">"
			."<textarea class=\"form-control \" name=\"atxt\" id=\"atxt\" rows=\"40\" required=\"required\" onkeyup=\"return il.Form.showCharCounterTextarea('atxt','textarea_feedback_atxt','','')\" style=\"width: 841px; height: 68px;\" wrap=\"virtual\">$value</textarea>"
			."<p id=\"charcounter\" style=\"display:none;\"><input spellcheck=\"false\" autocomplete=\"off\" autocorrect=\"off\" autocapitalize=\"off\" readonly=\"readonly\" id=\"myCounter\" name=\"myCounter\" style=\"border: 0; background: transparent;\" size=\"\" value=\"\" type=\"text\"></p>"
			."</div>"
			."</div>";

		$html = $this->normalizeHTML($r->render($textarea));
		$this->assertHTMLEquals($expected, $html);
	}

	//TODO fix expected
	public function test_renderer_min_error()
	{
		$f = $this->buildFactory();
		$label = "label";
		$min = 5;
		$value = "lorem ipsum";
		$byline = "Just a textarea input<br>Minimum: ".$min;
		$textarea = $f->textArea($label, $byline)->withMinLimit($min);
		$textarea->withValue($value);
		$r = $this->getDefaultRenderer();

		$expected = "<div class=\"form-group row\">"
			."<label for=\"form_input_1\" class=\"control-label col-sm-3\">$label</label>"
			."<div class=\"col-sm-9\">"
			."<textarea name=\"form_input_1\" class=\"form-control form-control-sm\" id=\"il_ui_fw_5b87d7a6c8a984_60891539\">$value</textarea>"
			."<div id=\"textarea_feedback_il_ui_fw_5b87d7a6c8a984_60891539\" data-maxchars=\"20\"></div>"
			."<div class=\"help-block\">$byline</div>"
			."</div>"
			."</div>";

		$html = $this->normalizeHTML($r->render($textarea));
		$this->assertHTMLEquals($expected, $html);
	}

	//TODO fix expected
	public function test_renderer_max_error()
	{
		$f = $this->buildFactory();
		$label = "label";
		$byline = "byline";
		$min = 5;
		$max = 10;
		$value = "lorem ipsum";
		$textarea = $f->textArea($label, $byline)->withMinLimit($min)->withMaxLimit($max);
		$textarea->withValue($value);
		$r = $this->getDefaultRenderer();

		$expected = "<div class=\"form-group\" id=\"il_prop_cont_atxt\">"
			."<label for=\"atxt\" class=\"col-sm-3 control-label\">$label</label>"
			."<div class=\"col-sm-9\">"
			."<textarea class=\"form-control \" name=\"atxt\" id=\"atxt\" rows=\"40\" required=\"required\" onkeyup=\"return il.Form.showCharCounterTextarea('atxt','textarea_feedback_atxt','','')\" style=\"width: 841px; height: 68px;\" wrap=\"virtual\">$value</textarea>"
			."<p id=\"charcounter\" style=\"display:none;\"><input spellcheck=\"false\" autocomplete=\"off\" autocorrect=\"off\" autocapitalize=\"off\" readonly=\"readonly\" id=\"myCounter\" name=\"myCounter\" style=\"border: 0; background: transparent;\" size=\"\" value=\"\" type=\"text\"></p>"
			."</div>"
			."</div>";

		$html = $this->normalizeHTML($r->render($textarea));
		$this->assertHTMLEquals($expected, $html);
	}

}