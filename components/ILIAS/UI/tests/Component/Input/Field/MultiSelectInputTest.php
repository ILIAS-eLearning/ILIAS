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
use ILIAS\UI\Component\Input\InputData;
use ILIAS\UI\Implementation\Component\Input\NameSource;
use ILIAS\UI\Component\Input\Field;
use ILIAS\Data;
use ILIAS\Refinery\Factory as Refinery;

class MultiSelectInputTest extends ILIAS_UI_TestBase
{
    use CommonFieldRendering;

    protected DefNamesource $name_source;

    public function setUp(): void
    {
        $this->name_source = new DefNamesource();
    }

    public function testImplementsFactoryInterface(): void
    {
        $f = $this->getFieldFactory();
        $options = array(
            "1" => "Pick 1",
            "2" => "Pick 2"
        );
        $ms = $f->multiSelect("label", $options, "byline");
        $this->assertInstanceOf(\ILIAS\UI\Component\Input\Container\Form\FormInput::class, $ms);
        $this->assertInstanceOf(Field\MultiSelect::class, $ms);
    }

    public function testOptions(): void
    {
        $f = $this->getFieldFactory();
        $options = array(
            "1" => "Pick 1",
            "2" => "Pick 2"
        );
        $ms = $f->multiSelect("label", $options, "byline");
        $this->assertEquals($options, $ms->getOptions());
    }

    public function testOnlyAcceptsActualOptionsFromClientSide(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $f = $this->getFieldFactory();
        $options = array(
            "1" => "Pick 1",
            "2" => "Pick 2"
        );
        $ms = $f->multiSelect("label", $options, "byline")
            ->withNameFrom(new class () implements NameSource {
                public function getNewName(): string
                {
                    return "name";
                }
            });
        $ms = $ms->withInput(new class () implements InputData {
            /**
             * @return string[]
             */
            public function getOr($_, $__): array
            {
                return ["3"];
            }
            public function get($_): void
            {
            }
            public function has($name): bool
            {
            }
        });
        $ms->getContent();
    }

    public function testRender(): void
    {
        $r = $this->getDefaultRenderer();
        $f = $this->getFieldFactory();
        $options = array(
            "1" => "Pick 1",
            "2" => "Pick 2"
        );
        $ms = $f->multiSelect("label", $options, "byline")
            ->withNameFrom($this->name_source);

        $name = $ms->getName();
        $label = $ms->getLabel();
        $byline = $ms->getByline();
        $expected_options = "";
        foreach ($options as $opt_value => $opt_label) {
            $expected_options .= ""
                    . "<li><label>"
                    . "<input type=\"checkbox\" name=\"$name" . "[]\" value=\"$opt_value\" />"
                    . ' <span class="c-field-multiselect__label-text">'
                    . $opt_label
                    . "</span></label></li>";
        }
        $expected = $this->getFormWrappedHtml(
            'multi-select-field-input',
            $label,
            '<ul class="c-field-multiselect">'
            . $expected_options .
            '</ul>',
            $byline,
            null
        );
        $this->assertEquals($expected, $this->render($ms));
    }

    public function testRenderValue(): void
    {
        $r = $this->getDefaultRenderer();
        $f = $this->getFieldFactory();
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
        $expected_options = "";
        foreach ($options as $opt_value => $opt_label) {
            if ($opt_value === $value) {
                $expected_options .= ""
                    . "<li><label>"
                    . "<input type=\"checkbox\" name=\"$name" . "[]\" value=\"$opt_value\" checked=\"checked\" />"
                    . '<span class="c-field-multiselect__label-text">'
                    . $opt_label
                    . "</span></label></li>";
            } else {
                $expected_options .= ""
                        . "<li><label>"
                        . "<input type=\"checkbox\" name=\"$name" . "[]\" value=\"$opt_value\" />"
                        . '<span class="c-field-multiselect__label-text">'
                        . $opt_label
                        . "</span></label></li>";
            }
        }
        $expected = $this->getFormWrappedHtml(
            'multi-select-field-input',
            $label,
            '<ul class="c-field-multiselect">'
                . $expected_options .
            '</ul>',
            $byline,
            null
        );
        $this->assertEquals($expected, $this->render($ms));
    }

    public function testCommonRendering(): void
    {
        $f = $this->getFieldFactory();
        $label = "label";
        $multi_select = $f->multiSelect($label, [], null)->withNameFrom($this->name_source);

        $this->testWithError($multi_select);
        $this->testWithNoByline($multi_select);
        $this->testWithRequired($multi_select);
        $this->testWithDisabled($multi_select);
        $this->testWithAdditionalOnloadCodeRendersId($multi_select);
    }

    public function testRenderNoOptions(): void
    {
        $r = $this->getDefaultRenderer();
        $f = $this->getFieldFactory();
        $options = [];
        $ms = $f->multiSelect("label", $options, "byline")
            ->withNameFrom($this->name_source)->withDisabled(true);

        $name = $ms->getName();
        $label = $ms->getLabel();
        $byline = $ms->getByline();
        $expected = '
        <fieldset class="c-input" data-il-ui-component="multi-select-field-input" data-il-ui-input-name="name_0" disabled="disabled" tabindex="0">
            <label class="c-input__label">label</label>
            <div class="c-input__field">
                <ul class="c-field-multiselect">
                    <li>-</li>
                </ul>
            </div>
            <div class="c-input__help-byline">byline</div>
            <div class="c-input__value_representation"></div>
        </fieldset>';

        $this->assertHTMLEquals($expected, $r->render($ms));
    }
}
