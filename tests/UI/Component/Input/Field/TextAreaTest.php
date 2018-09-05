<?php

/* Copyright (c) 2018 Jesús López <lopez@leifos.com> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");
require_once(__DIR__ . "/InputTest.php");

use ILIAS\UI\Implementation\Component\SignalGenerator;
use \ILIAS\UI\Component\Input\Field;

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
		$this->assertInstanceOf(Field\Input::class, $textarea);
		$this->assertInstanceOf(Field\TextArea::class, $textarea);
	}

	public function test_implements_factory_interface_without_byline() {
		$f = $this->buildFactory();
		$textarea = $f->textArea("label");
		$this->assertInstanceOf(Field\Input::class, $textarea);
		$this->assertInstanceOf(Field\TextArea::class, $textarea);
	}

	public function test_renderer() {
		$f = $this->buildFactory();
		$r = $this->getDefaultRenderer();
		$label = "label";
		$byline = "byline";
		$name = "name_0";
		$textarea = $f->textArea($label, $byline)->withNameFrom($this->name_source);

		$expected = "<div class=\"form-group row\">"
				."<label for=\"$name\" class=\"control-label col-sm-3\">$label</label>"
				."<div class=\"col-sm-9\">"
				."<textarea name=\"$name\" class=\"form-control form-control-sm\" id=\"\"></textarea>"
				."<div id=\"textarea_feedback_\" data-maxchars=\"\"></div>"
				."<div class=\"help-block\">byline</div>"
				."</div>"
				."</div>";

		$html = $this->normalizeHTML($r->render($textarea));
		$this->assertHTMLEquals($expected, $html);
	}

	public function test_renderer_with_min_limit()
	{
		$f = $this->buildFactory();
		$r = $this->getDefaultRenderer();
		$name = "name_0";
		$id = "id_1";
		$label = "label";

		$min = 5;
		$byline = "This is just a byline Min: ".$min;
		$textarea = $f->textArea($label, $byline)->withMinLimit($min)->withNameFrom($this->name_source);

		$expected = "<div class=\"form-group row\">"
			."<label for=\"$name\" class=\"control-label col-sm-3\">$label</label>"
			."<div class=\"col-sm-9\">"
			."<textarea name=\"$name\" class=\"form-control form-control-sm\" id=\"$id\"></textarea>"
			."<div id=\"textarea_feedback_$id\" data-maxchars=\"\"></div>"
			."<div class=\"help-block\">$byline</div>"
			."</div>"
			."</div>";

		$html = $this->normalizeHTML($r->render($textarea));
		$this->assertHTMLEquals($expected, $html);

	}

	public function test_renderer_with_max_limit()
	{
		$f = $this->buildFactory();
		$r = $this->getDefaultRenderer();
		$name = "name_0";
		$id = "id_1";
		$label = "label";
		$max = 20;
		$byline = "This is just a byline Max: ".$max;
		$textarea = $f->textArea($label, $byline)->withMaxLimit($max)->withNameFrom($this->name_source);

		$expected = "<div class=\"form-group row\">"
			."<label for=\"$name\" class=\"control-label col-sm-3\">$label</label>"
			."<div class=\"col-sm-9\">"
			."<textarea name=\"$name\" class=\"form-control form-control-sm\" id=\"$id\"></textarea>"
			."<div id=\"textarea_feedback_$id\" data-maxchars=\"$max\"></div>"
			."<div class=\"help-block\">$byline</div>"
			."</div>"
			."</div>";

		$html = $this->normalizeHTML($r->render($textarea));
		$this->assertHTMLEquals($expected, $html);
	}

	public function test_renderer_with_min_and_max_limit()
	{
		$f = $this->buildFactory();
		$r = $this->getDefaultRenderer();
		$name = "name_0";
		$id = "id_1";
		$label = "label";
		$min = 5;
		$max = 20;
		$byline = "This is just a byline Min: ".$min." Max: ".$max;
		$textarea = $f->textArea($label, $byline)->withMinLimit($min)->withMaxLimit($max)->withNameFrom($this->name_source);

		$expected = "<div class=\"form-group row\">"
			."<label for=\"$name\" class=\"control-label col-sm-3\">$label</label>"
			."<div class=\"col-sm-9\">"
			."<textarea name=\"$name\" class=\"form-control form-control-sm\" id=\"$id\"></textarea>"
			."<div id=\"textarea_feedback_$id\" data-maxchars=\"$max\"></div>"
			."<div class=\"help-block\">$byline</div>"
			."</div>"
			."</div>";

		$html = $this->normalizeHTML($r->render($textarea));
		$this->assertHTMLEquals($expected, $html);
	}

	public function test_renderer_counter_with_value()
	{
		$f = $this->buildFactory();
		$r = $this->getDefaultRenderer();
		$label = "label";
		$byline = "byline";
		$name = "name_0";
		$value = "Lorem ipsum dolor sit";
		$textarea = $f->textArea($label, $byline)->withValue($value)->withNameFrom($this->name_source);

		$expected = "<div class=\"form-group row\">"
			."<label for=\"$name\" class=\"control-label col-sm-3\">$label</label>"
			."<div class=\"col-sm-9\">"
			."<textarea name=\"$name\" class=\"form-control form-control-sm\" id=\"\">$value</textarea>"
			."<div id=\"textarea_feedback_\" data-maxchars=\"\"></div>"
			."<div class=\"help-block\">byline</div>"
			."</div>"
			."</div>";

		$html = $this->normalizeHTML($r->render($textarea));
		$this->assertHTMLEquals($expected, $html);
	}

	public function test_renderer_with_error()
	{
		$f = $this->buildFactory();
		$r = $this->getDefaultRenderer();
		$name = "name_0";
		$label = "label";
		$min = 5;
		$byline = "This is just a byline Min: ".$min;
		$error = "an_error";
		$textarea = $f->textArea($label, $byline)->withNameFrom($this->name_source)->withError($error);

		$expected = "<div class=\"form-group row\">"
			."<label for=\"$name\" class=\"control-label col-sm-3\">$label</label>"
			."<div class=\"col-sm-9\">"
			."<textarea name=\"$name\" class=\"form-control form-control-sm\" id=\"\"></textarea>"
			."<div id=\"textarea_feedback_\" data-maxchars=\"\"></div>"
			."<div class=\"help-block\">$byline</div>"
			."<div class=\"help-block alert alert-danger\" role=\"alert\">"
			."<img border=\"0\" src=\"./templates/default/images/icon_alert.svg\" alt=\"alert\">"
			."$error</div></div></div>";

		$html = $this->normalizeHTML($r->render($textarea));
		$html = trim(preg_replace('/\t+/', '', $html));
		$expected = trim(preg_replace('/\t+/', '', $expected));
		$this->assertEquals($expected, $html);
	}

}