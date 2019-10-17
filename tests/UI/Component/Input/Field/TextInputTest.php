<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");
require_once(__DIR__ . "/InputTest.php");

use ILIAS\UI\Implementation\Component\SignalGenerator;
use \ILIAS\UI\Component\Input\Field;
use \ILIAS\Data;
use \ILIAS\Validation;
use \ILIAS\Transformation;

class TextInputTest extends ILIAS_UI_TestBase
{
    public function setUp()
    {
        $this->name_source = new DefNamesource();
    }


    protected function buildFactory()
    {
        $df = new Data\Factory();
        return new ILIAS\UI\Implementation\Component\Input\Field\Factory(
            new SignalGenerator(),
            $df,
            new Validation\Factory($df, $this->createMock(\ilLanguage::class)),
            new Transformation\Factory()
        );
    }


    public function test_implements_factory_interface()
    {
        $f = $this->buildFactory();

        $text = $f->text("label", "byline");

        $this->assertInstanceOf(Field\Input::class, $text);
        $this->assertInstanceOf(Field\Text::class, $text);
    }


    public function test_render()
    {
        $f = $this->buildFactory();
        $label = "label";
        $byline = "byline";
        $name = "name_0";
        $text = $f->text($label, $byline)->withNameFrom($this->name_source);

        $r = $this->getDefaultRenderer();
        $html = $this->normalizeHTML($r->render($text));

        $expected = "<div class=\"form-group row\">" . "	<label for=\"$name\" class=\"control-label col-sm-3\">$label</label>"
                    . "	<div class=\"col-sm-9\">" . "		<input type=\"text\" name=\"$name\" class=\"form-control form-control-sm\" />"
                    . "		<div class=\"help-block\">$byline</div>" . "		" . "	</div>" . "</div>";
        $this->assertEquals($expected, $html);
    }


    public function test_render_error()
    {
        $f = $this->buildFactory();
        $label = "label";
        $byline = "byline";
        $name = "name_0";
        $error = "an_error";
        $text = $f->text($label, $byline)->withNameFrom($this->name_source)->withError($error);

        $r = $this->getDefaultRenderer();
        $html = $this->normalizeHTML($r->render($text));

        $expected = "<div class=\"form-group row\">" . "	<label for=\"$name\" class=\"control-label col-sm-3\">$label</label>"
                    . "	<div class=\"col-sm-9\">" . "		<input type=\"text\" name=\"$name\" class=\"form-control form-control-sm\" />"
                    . "		<div class=\"help-block\">$byline</div>" . "		<div class=\"help-block alert alert-danger\" role=\"alert\">"
                    . "			<img border=\"0\" src=\"./templates/default/images/icon_alert.svg\" alt=\"alert\" />" . "			$error"
                    . "		</div>" . "	</div>" . "</div>";
        $this->assertEquals($expected, $html);
    }


    public function test_render_no_byline()
    {
        $f = $this->buildFactory();
        $label = "label";
        $name = "name_0";
        $text = $f->text($label)->withNameFrom($this->name_source);

        $r = $this->getDefaultRenderer();
        $html = $this->normalizeHTML($r->render($text));

        $expected = "<div class=\"form-group row\">" . "	<label for=\"$name\" class=\"control-label col-sm-3\">$label</label>"
                    . "	<div class=\"col-sm-9\">" . "		<input type=\"text\" name=\"$name\" class=\"form-control form-control-sm\" />" . "		"
                    . "		" . "	</div>" . "</div>";
        $this->assertEquals($expected, $html);
    }


    public function test_render_value()
    {
        $f = $this->buildFactory();
        $label = "label";
        $value = "value";
        $name = "name_0";
        $text = $f->text($label)->withValue($value)->withNameFrom($this->name_source);

        $r = $this->getDefaultRenderer();
        $html = $this->normalizeHTML($r->render($text));

        $expected = "<div class=\"form-group row\">" . "	<label for=\"$name\" class=\"control-label col-sm-3\">$label</label>"
                    . "	<div class=\"col-sm-9\">"
                    . "		<input type=\"text\" value=\"$value\" name=\"$name\" class=\"form-control form-control-sm\" />" . "		" . "		"
                    . "	</div>" . "</div>";
        $this->assertEquals($expected, $html);
    }


    public function test_render_required()
    {
        $f = $this->buildFactory();
        $label = "label";
        $name = "name_0";
        $text = $f->text($label)->withNameFrom($this->name_source)->withRequired(true);

        $r = $this->getDefaultRenderer();
        $html = $this->normalizeHTML($r->render($text));

        $expected = "<div class=\"form-group row\">" . "	<label for=\"$name\" class=\"control-label col-sm-3\">" . "$label"
                    . "<span class=\"asterisk\">*</span>" . "</label>" . "	<div class=\"col-sm-9\">"
                    . "		<input type=\"text\" name=\"$name\" class=\"form-control form-control-sm\" />" . "		" . "		" . "	</div>"
                    . "</div>";
        $this->assertEquals($expected, $html);
    }


    public function test_value_required()
    {
        $f = $this->buildFactory();
        $label = "label";
        $name = "name_0";
        $text = $f->text($label)->withNameFrom($this->name_source)->withRequired(true);

        $text1 = $text->withInput(new DefPostData([$name => "0"]));
        $value1 = $text1->getContent();
        $this->assertTrue($value1->isOk());
        $this->assertEquals("0", $value1->value());

        $text2 = $text->withInput(new DefPostData([$name => ""]));
        $value2 = $text2->getContent();
        $this->assertTrue($value2->isError());
    }

    public function test_stripsTags()
    {
        $f = $this->buildFactory();
        $name = "name_0";
        $text = $f->text("")
            ->withNameFrom($this->name_source)
            ->withInput(new DefPostData([$name => "<script>alert()</script>"]));

        $content = $text->getContent();
        $this->assertEquals("alert()", $content->value());
    }
}
