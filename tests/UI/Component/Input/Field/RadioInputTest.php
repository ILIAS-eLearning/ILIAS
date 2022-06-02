<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/
 
require_once(__DIR__ . "/../../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");

use ILIAS\UI\Implementation\Component as I;
use ILIAS\UI\Implementation\Component\SignalGenerator;
use ILIAS\UI\Component\Input\Field;
use ILIAS\Data;
use ILIAS\Refinery\Factory as Refinery;

class RadioInputTest extends ILIAS_UI_TestBase
{
    protected DefNamesource $name_source;

    public function setUp() : void
    {
        $this->name_source = new DefNamesource();
    }

    protected function buildFactory() : I\Input\Field\Factory
    {
        $df = new Data\Factory();
        $language = $this->createMock(\ilLanguage::class);
        return new I\Input\Field\Factory(
            new SignalGenerator(),
            $df,
            new Refinery($df, $language),
            $language
        );
    }

    protected function buildRadio() : Field\Input
    {
        $f = $this->buildFactory();
        $label = "label";
        $byline = "byline";
        return $f
            ->radio($label, $byline)
            ->withOption('value0', 'label0', 'byline0')
            ->withOption('1', 'label1', 'byline1')
            ->withNameFrom($this->name_source);
    }

    public function test_implements_factory_interface() : void
    {
        $f = $this->buildFactory();
        $radio = $f->radio("label", "byline");
        $this->assertInstanceOf(Field\Input::class, $radio);
        $this->assertInstanceOf(Field\Radio::class, $radio);
    }

    public function test_render() : void
    {
        $r = $this->getDefaultRenderer();
        $radio = $this->buildRadio();
        $name = $radio->getName();
        $label = $radio->getLabel();
        $byline = $radio->getByline();
        $options = $radio->getOptions();

        $expected = ""
            . "<div class=\"form-group row\">"
                . "<label class=\"control-label col-sm-4 col-md-3 col-lg-2\">$label</label>"
                . "<div class=\"col-sm-8 col-md-9 col-lg-10\">"
                    . "<div id=\"id_1\" class=\"il-input-radio\">";

        foreach ($options as $opt_value => $opt_label) {
            $expected .= ""
                        . "<div class=\"form-control form-control-sm il-input-radiooption\">"
                            . "<input type=\"radio\" id=\"id_1_" . $opt_value . "_opt\" name=\"$name\" value=\"$opt_value\" />"
                            . "<label for=\"id_1_" . $opt_value . "_opt\">$opt_label</label>"
                            . "<div class=\"help-block\">{$radio->getBylineFor((string) $opt_value)}</div>"
                        . "</div>";
        }

        $expected .= ""
                    . "</div>"
                    . "<div class=\"help-block\">$byline</div>"
                . "</div>"
            . "</div>";
        $this->assertHTMLEquals($expected, $r->render($radio));
    }

    public function test_render_value() : void
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
                . "<label class=\"control-label col-sm-4 col-md-3 col-lg-2\">$label</label>"
                . "<div class=\"col-sm-8 col-md-9 col-lg-10\">"
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
                            . "<div class=\"help-block\">{$radio->getBylineFor((string) $opt_value)}</div>"
                        . "</div>";
        }

        $expected .= ""
                    . "</div>"
                    . "<div class=\"help-block\">$byline</div>"
                . "</div>"
            . "</div>";

        $this->assertHTMLEquals($expected, $r->render($radio));
    }

    public function test_render_disabled() : void
    {
        $r = $this->getDefaultRenderer();
        $radio = $this->buildRadio()->withDisabled(true);
        $name = $radio->getName();
        $label = $radio->getLabel();
        $byline = $radio->getByline();
        $options = $radio->getOptions();

        $expected = ""
            . "<div class=\"form-group row\">"
            . "<label class=\"control-label col-sm-4 col-md-3 col-lg-2\">$label</label>"
            . "<div class=\"col-sm-8 col-md-9 col-lg-10\">"
            . "<div id=\"id_1\" class=\"il-input-radio\">";

        foreach ($options as $opt_value => $opt_label) {
            $expected .= ""
                . "<div class=\"form-control form-control-sm il-input-radiooption\">"
                . "<input type=\"radio\" id=\"id_1_" . $opt_value . "_opt\" name=\"$name\" value=\"$opt_value\" disabled=\"disabled\"/>"
                . "<label for=\"id_1_" . $opt_value . "_opt\">$opt_label</label>"
                . "<div class=\"help-block\">{$radio->getBylineFor((string) $opt_value)}</div>"
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
