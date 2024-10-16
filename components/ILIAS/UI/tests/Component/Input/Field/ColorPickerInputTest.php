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
require_once(__DIR__ . "/InputTest.php");
require_once(__DIR__ . "/CommonFieldRendering.php");

use ILIAS\UI\Implementation\Component as I;
use ILIAS\UI\Component\Input\Field;
use ILIAS\Data;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Implementation\Component\SignalGenerator;

class ColorPickerInputTest extends ILIAS_UI_TestBase
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
        $cp = $f->colorpicker("label", "byline");
        $this->assertInstanceOf(\ILIAS\UI\Component\Input\Container\Form\FormInput::class, $cp);
        $this->assertInstanceOf(Field\ColorPicker::class, $cp);
    }

    public function testRender(): void
    {
        $f = $this->getFieldFactory();
        $label = "label";
        $byline = "byline";
        $cp = $f->colorpicker($label, $byline)->withNameFrom($this->name_source);

        $expected = $this->getFormWrappedHtml(
            'color-picker-field-input',
            $label,
            '<input id="id_1" type="color" name="name_0" value="" class="c-field-color-picker"/>',
            $byline,
            'id_1'
        );
        $this->assertEquals($expected, $this->render($cp));
    }

    public function testCommonRendering(): void
    {
        $f = $this->getFieldFactory();
        $label = "label";
        $colorpicker = $f->colorpicker($label, null)->withNameFrom($this->name_source);

        $this->testWithError($colorpicker);
        $this->testWithNoByline($colorpicker);
        $this->testWithRequired($colorpicker);
        $this->testWithDisabled($colorpicker);
        $this->testWithAdditionalOnloadCodeRendersId($colorpicker);
    }

    public function testRenderValue(): void
    {
        $f = $this->getFieldFactory();
        $label = "label";
        $byline = "byline";
        $value = "value_0";
        $cp = $f->colorpicker($label, $byline)
                ->withValue($value)
                ->withNameFrom($this->name_source);

        $expected = $this->getFormWrappedHtml(
            'color-picker-field-input',
            $label,
            '<input id="id_1" type="color" name="name_0" value="value_0" class="c-field-color-picker"/>',
            $byline,
            'id_1'
        );
        $this->assertEquals($expected, $this->render($cp));
    }

    public function testValueRequired(): void
    {
        $f = $this->getFieldFactory();
        $label = "label";
        $byline = "byline";
        $name = "name_0";
        $cp = $f->colorpicker($label, $byline)
                ->withNameFrom($this->name_source)
                ->withRequired(true);

        $cp1 = $cp->withInput(new DefInputData([$name => "#FFF"]));
        $value1 = $cp1->getContent();
        $this->assertTrue($value1->isOk());

        $cp2 = $cp->withInput(new DefInputData([$name => "#00"]));
        $value2 = $cp2->getContent();
        $this->assertTrue($value2->isError());

        $cp3 = $cp->withInput(new DefInputData([$name => ""]));
        $value2 = $cp3->getContent();
        $this->assertTrue($value2->isError());
    }

    public function testNullValue(): void
    {
        $f = $this->getFieldFactory();
        $colorpicker = $f->colorpicker("label", "byline");
        $this->expectException(\InvalidArgumentException::class);
        $colorpicker->withValue(null);
    }
}
