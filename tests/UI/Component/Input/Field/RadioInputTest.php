<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");

use ILIAS\UI\Implementation\Component\Input\Field\Radio;
use ILIAS\UI\Implementation\Component\SignalGenerator;
use \ILIAS\UI\Component\Input\Field;
use \ILIAS\Data;
use ILIAS\Refinery;

class RadioInputTest extends ILIAS_UI_TestBase
{
    public function setUp() : void
    {
        $this->name_source = new DefNamesource();
    }

    protected function buildFactory()
    {
        $df = new Data\Factory();
        $language = $this->createMock(\ilLanguage::class);
        return new ILIAS\UI\Implementation\Component\Input\Field\Factory(
            new SignalGenerator(),
            $df,
            new \ILIAS\Refinery\Factory($df, $language),
            $language
        );
    }

    protected function buildRadio() : Radio
    {
        $f = $this->buildFactory();
        $label = "label";
        $byline = "byline";
        $radio = $f->radio($label, $byline)
            ->withOption('value0', 'label0', 'byline0')
            ->withOption('1', 'label1', 'byline1')
            ->withNameFrom($this->name_source);
        return $radio;
    }


    public function test_implements_factory_interface()
    {
        $f = $this->buildFactory();
        $radio = $f->radio("label", "byline");
        $this->assertInstanceOf(Field\Input::class, $radio);
        $this->assertInstanceOf(Field\Radio::class, $radio);
    }


    public function test_render()
    {
        $r = $this->getDefaultRenderer();
        $radio = $this->buildRadio();
        $name = $radio->getName();
        $label = $radio->getLabel();
        $byline = $radio->getByline();
        $options = $radio->getOptions();

        $expected = ""
            . "<div class=\"form-group row\">"
                . "<label for=\"\" class=\"control-label col-sm-3\">$label</label>"
                . "<div class=\"col-sm-9\">"
                    . "<div id=\"id_1\" class=\"il-input-radio\">";

        foreach ($options as $opt_value => $opt_label) {
            $expected .= ""
                        . "<div class=\"form-control form-control-sm il-input-radiooption\">"
                            . "<input type=\"radio\" id=\"id_1_" . $opt_value . "_opt\" name=\"$name\" value=\"$opt_value\" />"
                            . "<label for=\"id_1_" . $opt_value . "_opt\">$opt_label</label>"
                            . "<div class=\"help-block\">{$radio->getBylineFor($opt_value)}</div>"
                        . "</div>";
        }

        $expected .= ""
                    . "</div>"
                    . "<div class=\"help-block\">$byline</div>"
                . "</div>"
            . "</div>";
        $this->assertHTMLEquals($expected, $r->render($radio));
    }


    public function test_render_value()
    {
        $r = $this->getDefaultRenderer();
        $radio = $this->buildRadio();
        $name = $radio->getName();
        $label = $radio->getLabel();
        $byline = $radio->getByline();
        $options = $radio->getOptions();
        $value = '1';
        $radio = $radio->withValue($value);
        $expected = ""
            . "<div class=\"form-group row\">"
                . "<label for=\"\" class=\"control-label col-sm-3\">$label</label>"
                . "<div class=\"col-sm-9\">"
                    . "<div id=\"id_1\" class=\"il-input-radio\">";

        foreach ($options as $opt_value => $opt_label) {
            $expected .= "<div class=\"form-control form-control-sm il-input-radiooption\">";
            if ($opt_value == $value) {
                $expected .= "<input type=\"radio\" id=\"id_1_" . $opt_value . "_opt\" name=\"$name\" value=\"$opt_value\" checked=\"checked\"/>";
            } else {
                $expected .= "<input type=\"radio\" id=\"id_1_" . $opt_value . "_opt\" name=\"$name\" value=\"$opt_value\" />";
            }
            $expected .= ""
                            . "<label for=\"id_1_" . $opt_value . "_opt\">$opt_label</label>"
                            . "<div class=\"help-block\">{$radio->getBylineFor($opt_value)}</div>"
                        . "</div>";
        }

        $expected .= ""
                    . "</div>"
                    . "<div class=\"help-block\">$byline</div>"
                . "</div>"
            . "</div>";

        $this->assertHTMLEquals($expected, $r->render($radio));
    }



    public function test_render_disabled()
    {
        $r = $this->getDefaultRenderer();
        $radio = $this->buildRadio()->withDisabled(true);
        $name = $radio->getName();
        $label = $radio->getLabel();
        $byline = $radio->getByline();
        $options = $radio->getOptions();

        $expected = ""
            . "<div class=\"form-group row\">"
            . "<label for=\"\" class=\"control-label col-sm-3\">$label</label>"
            . "<div class=\"col-sm-9\">"
            . "<div id=\"id_1\" class=\"il-input-radio\">";

        foreach ($options as $opt_value => $opt_label) {
            $expected .= ""
                . "<div class=\"form-control form-control-sm il-input-radiooption\">"
                . "<input type=\"radio\" id=\"id_1_" . $opt_value . "_opt\" name=\"$name\" value=\"$opt_value\" disabled=\"disabled\"/>"
                . "<label for=\"id_1_" . $opt_value . "_opt\">$opt_label</label>"
                . "<div class=\"help-block\">{$radio->getBylineFor($opt_value)}</div>"
                . "</div>";
        }

        $expected .= ""
            . "</div>"
            . "<div class=\"help-block\">$byline</div>"
            . "</div>"
            . "</div>";
        $this->assertHTMLEquals($expected, $r->render($radio));
    }
}
