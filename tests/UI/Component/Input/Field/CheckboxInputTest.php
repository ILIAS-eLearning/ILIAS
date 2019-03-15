<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");
require_once(__DIR__ . "/InputTest.php");

use ILIAS\UI\Implementation\Component\SignalGenerator;
use \ILIAS\UI\Component\Input\Field;
use \ILIAS\Data;
use ILIAS\Refinery;

class CheckboxInputTest extends ILIAS_UI_TestBase {

	public function setUp(): void{
		$this->name_source = new DefNamesource();
	}


	protected function buildFactory() {
		$df = new Data\Factory();
		$language = $this->createMock(\ilLanguage::class);
		return new ILIAS\UI\Implementation\Component\Input\Field\Factory(
			new SignalGenerator(),
			$df,
			new \ILIAS\Refinery\Factory($df, $language)
		);
	}


	public function testImplementsFactoryInterface() {
		$f = $this->buildFactory();

		$checkbox = $f->checkbox("label", "byline");

        $this->assertInstanceOf(Field\Input::class, $checkbox);
        $this->assertInstanceOf(Field\Checkbox::class, $checkbox);
	}


	public function testRender() {
		$f = $this->buildFactory();
		$label = "label";
		$byline = "byline";
		$checkbox = $f->checkbox($label, $byline)->withNameFrom($this->name_source);

		$r = $this->getDefaultRenderer();
		$html = $r->render($checkbox);

		$expected = "<div class=\"form-group row\">  <label for=\"name_0\" class=\"control-label col-sm-3\">label</label>        <div class=\"col-sm-9\">          <input type=\"checkbox\"  value=\"checked\"  name=\"name_0\" class=\"form-control form-control-sm\" />          <div class=\"help-block\">byline</div>                    </div></div>";
        $this->assertHTMLEquals($expected, $html);
	}


	public function testRenderError() {
		$f = $this->buildFactory();
		$label = "label";
		$byline = "byline";
		$error = "an_error";
        $checkbox = $f->checkbox($label, $byline)->withNameFrom($this->name_source)->withError($error);

		$r = $this->getDefaultRenderer();
		$html = $r->render($checkbox);

		$expected = "<div class=\"form-group row\">  <label for=\"name_0\" class=\"control-label col-sm-3\">label</label>        <div class=\"col-sm-9\">          <input type=\"checkbox\"  value=\"checked\"  name=\"name_0\" class=\"form-control form-control-sm\" />          <div class=\"help-block\">byline</div>            <div class=\"help-block alert alert-danger\" role=\"alert\">        <img border=\"0\" src=\" ./templates/default/images/icon_alert.svg\" alt=\"alert\" />" . "			$error"
		            . "		</div></div></div>";
        $this->assertHTMLEquals($expected, $html);
	}


	public function testRenderNoByline() {
		$f = $this->buildFactory();
		$label = "label";
        $checkbox = $f->checkbox($label)->withNameFrom($this->name_source);

		$r = $this->getDefaultRenderer();
		$html = $r->render($checkbox);

		$expected = "<div class=\"form-group row\">  <label for=\"name_0\" class=\"control-label col-sm-3\">label</label>        <div class=\"col-sm-9\">          <input type=\"checkbox\"  value=\"checked\"  name=\"name_0\" class=\"form-control form-control-sm\" />                                  </div></div>";
        $this->assertHTMLEquals($expected, $html);
	}


	public function testRenderValue() {
		$f = $this->buildFactory();
		$label = "label";
		$value = "checked";
		$checkbox = $f->checkbox($label)->withValue($value)->withNameFrom($this->name_source);

		$r = $this->getDefaultRenderer();
		$html = $r->render($checkbox);

		$expected = "<div class=\"form-group row\">  <label for=\"name_0\" class=\"control-label col-sm-3\">label</label>        <div class=\"col-sm-9\">          <input type=\"checkbox\"  value=\"checked\"  checked=\"checked\" name=\"name_0\" class=\"form-control form-control-sm\" />                                  </div></div>";
        $this->assertHTMLEquals($expected, $html);
	}

    public function testHandleInvalidValue() {
        $f = $this->buildFactory();
        $label = "label";
        $value = "invalid";
        try{
            $f->checkbox($label)->withValue($value);
            $this->assertFalse(true);

        }catch(InvalidArgumentException $e){
            $this->assertTrue(true);
        }
    }


	public function testRenderRequired() {
		$f = $this->buildFactory();
		$label = "label";
		$checkbox = $f->checkbox($label)->withNameFrom($this->name_source)->withRequired(true);

		$r = $this->getDefaultRenderer();
		$html = $r->render($checkbox);

		$expected = "<div class=\"form-group row\">  <label for=\"name_0\" class=\"control-label col-sm-3\">label<span class=\"asterisk\">*</span></label> <div class=\"col-sm-9\">          <input type=\"checkbox\"  value=\"checked\"  name=\"name_0\" class=\"form-control form-control-sm\" />                                  </div></div>";
		$this->assertHTMLEquals($expected, $html);
	}

	public function testRenderDisabled() {
		$f = $this->buildFactory();
		$label = "label";
		$checkbox = $f->checkbox($label)->withNameFrom($this->name_source)->withDisabled(true);

		$r = $this->getDefaultRenderer();
		$html = $r->render($checkbox);

		$expected = "<div class=\"form-group row\">  <label for=\"name_0\" class=\"control-label col-sm-3\">label</label>        <div class=\"col-sm-9\">          <input type=\"checkbox\"  value=\"checked\"  name=\"name_0\" disabled=\"disabled\" class=\"form-control form-control-sm\" />                                  </div></div>";
		$this->assertHTMLEquals($expected, $html);

	}
}
