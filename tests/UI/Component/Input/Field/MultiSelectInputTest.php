<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");

use ILIAS\UI\Implementation\Component\SignalGenerator;
use ILIAS\UI\Implementation\Component\Input\PostData;
use \ILIAS\UI\Component\Input\Field;
use \ILIAS\Data;
use \ILIAS\Validation;
use \ILIAS\Transformation;

class MultiSelectInputTest extends ILIAS_UI_TestBase
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
        $options = array(
            "1" => "Pick 1",
            "2" => "Pick 2"
        );
        $ms = $f->multiSelect("label", $options, "byline");
        $this->assertInstanceOf(Field\Input::class, $ms);
        $this->assertInstanceOf(Field\MultiSelect::class, $ms);
    }


    public function test_options()
    {
        $f = $this->buildFactory();
        $options = array(
            "1" => "Pick 1",
            "2" => "Pick 2"
        );
        $ms = $f->multiSelect("label", $options, "byline");
        $this->assertEquals($options, $ms->getOptions());
    }


    public function test_only_accepts_actual_options_from_client_side() {
        $this->expectException(\InvalidArgumentException::class);
        $f = $this->buildFactory();
        $options = array(
            "1" => "Pick 1",
            "2" => "Pick 2"
        );
        $ms = $f->multiSelect("label", $options, "byline");
        $ms = $ms->withInput(new class () implements PostData {
            public function getOr($_, $__) {
                return ["3"];
            }
            public function get($_) {}
        });
        $content = $ms->getContent();
    }


    public function test_render()
    {
        $r = $this->getDefaultRenderer();
        $f = $this->buildFactory();
        $options = array(
            "1" => "Pick 1",
            "2" => "Pick 2"
        );
        $ms = $f->multiSelect("label", $options, "byline")
            ->withNameFrom($this->name_source);

        $name = $ms->getName();
        $label = $ms->getLabel();
        $byline = $ms->getByline();
        $expected = ""
            . "<div class=\"form-group row\">"
                . "<label for=\"$name\" class=\"control-label col-sm-3\">$label</label>"
                . "<div class=\"col-sm-9\">"
                    . "<ul class=\"il-input-multiselect\">";

        foreach ($options as $opt_value=>$opt_label) {
            $expected .= ""
                        . "<li>"
                            . "<input type=\"checkbox\" name=\"$name" . "[]\" value=\"$opt_value\" />"
                            . "<span>$opt_label</span>"
                        . "</li>";
        }

        $expected .= ""
                    . "</ul>"
                    . "<div class=\"help-block\">$byline</div>"
                . "</div>"
            . "</div>";
        $this->assertHTMLEquals($expected, $r->render($ms));
    }


    public function test_render_value()
    {
        $r = $this->getDefaultRenderer();
        $f = $this->buildFactory();
        $options = array(
            "1" => "Pick 1",
            "2" => "Pick 2"
        );
        $value = array_keys($options)[1];
        $ms = $f->multiSelect("label", $options, "byline")
            ->withNameFrom($this->name_source)
            ->withValue([$value]);

        $name = $ms->getName();
        $label = $ms->getLabel();
        $byline = $ms->getByline();
        $expected = ""
            . "<div class=\"form-group row\">"
                . "<label for=\"$name\" class=\"control-label col-sm-3\">$label</label>"
                . "<div class=\"col-sm-9\">"
                    . "<ul class=\"il-input-multiselect\">";

        foreach ($options as $opt_value=>$opt_label) {
            if ($opt_value === $value) {
                $expected .= ""
                        . "<li>"
                            . "<input type=\"checkbox\" name=\"$name" . "[]\" value=\"$opt_value\" checked=\"checked\" />"
                            . "<span>$opt_label</span>"
                        . "</li>";
            } else {
                $expected .= ""
                        . "<li>"
                            . "<input type=\"checkbox\" name=\"$name" . "[]\" value=\"$opt_value\" />"
                            . "<span>$opt_label</span>"
                        . "</li>";
            }
        }

        $expected .= ""
                    . "</ul>"
                    . "<div class=\"help-block\">$byline</div>"
                . "</div>"
            . "</div>";
        $this->assertHTMLEquals($expected, $r->render($ms));
    }
}
