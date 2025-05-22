<?php

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

declare(strict_types=1);

require_once(__DIR__ . "/../../../../../../../vendor/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");
require_once(__DIR__ . "/CommonFieldRendering.php");

use ILIAS\UI\Implementation\Component as I;
use ILIAS\UI\Implementation\Component\SignalGenerator;
use ILIAS\UI\Component\Input\Field;
use ILIAS\Data;
use ILIAS\Refinery\Factory as Refinery;

class RadioInputTest extends ILIAS_UI_TestBase
{
    use CommonFieldRendering;

    protected DefNamesource $name_source;

    public function setUp(): void
    {
        $this->name_source = new DefNamesource();
    }

    protected function buildRadio(): \ILIAS\UI\Component\Input\Container\Form\FormInput
    {
        $f = $this->getFieldFactory();
        $label = "label";
        $byline = "byline";
        return $f
            ->radio($label, $byline)
            ->withOption('value0', 'label0', 'byline0')
            ->withOption('1', 'label1', 'byline1')
            ->withNameFrom($this->name_source);
    }

    public function testImplementsFactoryInterface(): void
    {
        $f = $this->getFieldFactory();
        $radio = $f->radio("label", "byline");
        $this->assertInstanceOf(\ILIAS\UI\Component\Input\Container\Form\FormInput::class, $radio);
        $this->assertInstanceOf(Field\Radio::class, $radio);
    }

    public function testRender(): void
    {
        $r = $this->getDefaultRenderer();
        $radio = $this->buildRadio();
        $name = $radio->getName();
        $label = $radio->getLabel();
        $byline = $radio->getByline();
        $options = $radio->getOptions();

        $expected_options = "";
        foreach ($options as $opt_value => $opt_label) {
            $expected_options .= ""
                . '<div class="c-field-radio__item">'
                . "<input type=\"radio\" id=\"id_1_" . $opt_value . "_opt\" name=\"$name\" value=\"$opt_value\" />"
                . "<label for=\"id_1_" . $opt_value . "_opt\">$opt_label</label>"
                . "<div class=\"c-input__help-byline\">{$radio->getBylineFor((string) $opt_value)}</div>"
                . '</div>';
        }
        $expected = $this->getFormWrappedHtml(
            'radio-field-input',
            $label,
            '<div class="c-field-radio">' . $expected_options . '</div>',
            $byline,
            null
        );
        $this->assertEquals($expected, $this->render($radio));
    }

    public function testRenderValue(): void
    {
        $r = $this->getDefaultRenderer();
        $radio = $this->buildRadio();
        $name = $radio->getName();
        $label = $radio->getLabel();
        $byline = $radio->getByline();
        $options = $radio->getOptions();
        $value = '1';
        $radio = $radio->withValue($value);
        $expected_options = "";
        foreach ($options as $opt_value => $opt_label) {
            $expected_options .= '<div class="c-field-radio__item">';
            if ($opt_value == $value) {
                $expected_options .= "<input type=\"radio\" id=\"id_1_" . $opt_value . "_opt\" name=\"$name\" value=\"$opt_value\" checked=\"checked\" />";
            } else {
                $expected_options .= "<input type=\"radio\" id=\"id_1_" . $opt_value . "_opt\" name=\"$name\" value=\"$opt_value\" />";
            }
            $expected_options .= ""
                . "<label for=\"id_1_" . $opt_value . "_opt\">$opt_label</label>"
                . "<div class=\"c-input__help-byline\">{$radio->getBylineFor((string) $opt_value)}</div>"
                . '</div>';
        }
        $expected = $this->getFormWrappedHtml(
            'radio-field-input',
            $label,
            '<div class="c-field-radio">' . $expected_options . '</div>',
            $byline,
            null
        );
        $this->assertEquals($expected, $this->render($radio));
    }

    public function testCommonRendering(): void
    {
        $f = $this->getFieldFactory();
        $radio = $f->radio('label', null)->withNameFrom($this->name_source);

        $this->testWithError($radio);
        $this->testWithNoByline($radio);
        $this->testWithRequired($radio);
        $this->testWithDisabled($radio);
        $this->testWithAdditionalOnloadCodeRendersId($radio);
    }

}
