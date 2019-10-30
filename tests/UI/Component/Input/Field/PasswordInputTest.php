<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");
require_once(__DIR__ . "/InputTest.php");

use ILIAS\UI\Implementation\Component\SignalGenerator;
use ILIAS\UI\Implementation\Component\Input\PostData;
use ILIAS\Data\Password as PWD;
use \ILIAS\UI\Component\Input\Field;
use \ILIAS\Data;
use \ILIAS\Validation;
use \ILIAS\Transformation;

class _PWDPostData implements PostData
{
    public function get($name)
    {
        return 'some value';
    }
    public function getOr($name, $default)
    {
        return 'some alternative value';
    }
}

class PasswordInputTest extends ILIAS_UI_TestBase
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
        $pwd = $f->password("label", "byline");
        $this->assertInstanceOf(Field\Input::class, $pwd);
        $this->assertInstanceOf(Field\Password::class, $pwd);
    }


    public function test_render()
    {
        $f = $this->buildFactory();
        $label = "label";
        $byline = "byline";
        $name = "name_0";
        $pwd = $f->password($label, $byline)->withNameFrom($this->name_source);

        $r = $this->getDefaultRenderer();
        $expected = ""
            . "<div class=\"form-group row\">"
                . "<label for=\"$name\" class=\"control-label col-sm-3\">$label</label>"
                . "<div class=\"col-sm-9\">"
                    . "<div class=\"il-input-password\">"
                        . "<input type=\"password\" name=\"$name\" class=\"form-control form-control-sm\" />"
                    . "</div>"
                    . "<div class=\"help-block\">$byline</div>"
                . "</div>"
            . "</div>";
        $this->assertHTMLEquals($expected, $r->render($pwd));
    }


    public function test_render_error()
    {
        $f = $this->buildFactory();
        $label = "label";
        $byline = "byline";
        $name = "name_0";
        $error = "an_error";
        $pwd = $f->password($label, $byline)->withNameFrom($this->name_source)->withError($error);

        $r = $this->getDefaultRenderer();
        $html = $this->normalizeHTML($r->render($pwd));
        $expected = ""
            . "<div class=\"form-group row\">"
                . " <label for=\"$name\" class=\"control-label col-sm-3\">$label</label>"
                . " <div class=\"col-sm-9\">"
                    . " <div class=\"il-input-password\">"
                        . " <input type=\"password\" name=\"$name\" class=\"form-control form-control-sm\" />"
                    . " </div>"
                    . " <div class=\"help-block\">$byline</div>"
                    . " <div class=\"help-block alert alert-danger\" role=\"alert\">"
                        . " <img border=\"0\" src=\"./templates/default/images/icon_alert.svg\" alt=\"alert\" />"
                        . " $error"
                    . " </div>"
                . " </div>"
            . "</div>";

        $html = preg_replace('!\s+!', ' ', $html);
        $expected = preg_replace('!\s+!', ' ', $expected);
        $html = explode(' ', $html); //so you can actually _see_ the difference...
        $expected = explode(' ', $expected);
        $this->assertEquals($expected, $html);
    }


    public function test_render_no_byline()
    {
        $f = $this->buildFactory();
        $label = "label";
        $name = "name_0";
        $pwd = $f->password($label)->withNameFrom($this->name_source);

        $r = $this->getDefaultRenderer();
        $expected = ""
            . "<div class=\"form-group row\">"
                . "<label for=\"$name\" class=\"control-label col-sm-3\">$label</label>"
                . "<div class=\"col-sm-9\">"
                    . "<div class=\"il-input-password\">"
                        . "<input type=\"password\" name=\"$name\" class=\"form-control form-control-sm\" />"
                    . "</div>"
                . "</div>"
            . "</div>";
        $this->assertHTMLEquals($expected, $r->render($pwd));
    }


    public function test_render_value()
    {
        $f = $this->buildFactory();
        $label = "label";
        $value = "value_0";
        $name = "name_0";
        $pwd = $f->password($label)->withValue($value)->withNameFrom($this->name_source);

        $r = $this->getDefaultRenderer();
        $expected = ""
            . "<div class=\"form-group row\">"
                . "<label for=\"$name\" class=\"control-label col-sm-3\">$label</label>"
                . "<div class=\"col-sm-9\">"
                    . "<div class=\"il-input-password\">"
                        . "<input type=\"password\" name=\"$name\" value=\"$value\" class=\"form-control form-control-sm\" />"
                    . "</div>"
                . "</div>"
            . "</div>";
        $this->assertHTMLEquals($expected, $r->render($pwd));
    }


    public function test_render_required()
    {
        $f = $this->buildFactory();
        $label = "label";
        $name = "name_0";
        $pwd = $f->password($label)->withNameFrom($this->name_source)->withRequired(true);

        $r = $this->getDefaultRenderer();
        $html = $r->render($pwd);

        $expected = ""
        . "<div class=\"form-group row\">"
            . "<label for=\"$name\" class=\"control-label col-sm-3\">" . "$label"
                . "<span class=\"asterisk\">*</span>"
            . "</label>"
            . "<div class=\"col-sm-9\">"
                . "<div class=\"il-input-password\">"
                    . "<input type=\"password\" name=\"$name\" class=\"form-control form-control-sm\" />"
                . "</div>"
            . "</div>"
        . "</div>";
        $this->assertHTMLEquals($expected, $html);
    }


    public function test_value_required()
    {
        $f = $this->buildFactory();
        $label = "label";
        $name = "name_0";
        $pwd = $f->password($label)->withNameFrom($this->name_source)->withRequired(true);

        $pwd1 = $pwd->withInput(new DefPostData([$name => "0"]));
        $value1 = $pwd1->getContent();
        $this->assertTrue($value1->isOk());

        $pwd2 = $pwd->withInput(new DefPostData([$name => ""]));
        $value2 = $pwd2->getContent();
        $this->assertTrue($value2->isError());
    }

    public function test_value_type()
    {
        $f = $this->buildFactory();
        $label = "label";
        $pwd = $f->password($label);
        $this->assertNull($pwd->getValue());

        $post = new _PWDPostData();
        $pwd = $pwd->withInput($post);
        $this->assertEquals($post->getOr('', ''), $pwd->getValue());
        $this->assertInstanceOf(PWD::class, $pwd->getContent()->value());
    }
}
