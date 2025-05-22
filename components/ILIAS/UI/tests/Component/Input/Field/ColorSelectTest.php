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

class ColorSelectTest extends ILIAS_UI_TestBase
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
        $cp = $f->colorSelect("label", "byline");
        $this->assertInstanceOf(\ILIAS\UI\Component\Input\Container\Form\FormInput::class, $cp);
        $this->assertInstanceOf(Field\ColorSelect::class, $cp);
    }

    public function testRender(): void
    {
        $f = $this->getFieldFactory();
        $label = "label";
        $byline = "byline";
        $cp = $f->colorSelect($label, $byline)->withNameFrom($this->name_source);

        $expected = $this->getFormWrappedHtml(
            'color-select-field-input',
            $label,
            '<input id="id_1" type="color" name="name_0" value="" class="c-field-color-select"/>',
            $byline,
            'id_1'
        );
        $this->assertEquals($expected, $this->render($cp));
    }

    public function testCommonRendering(): void
    {
        $f = $this->getFieldFactory();
        $label = "label";
        $color_select = $f->colorSelect($label, null)->withNameFrom($this->name_source);

        $this->testWithError($color_select);
        $this->testWithNoByline($color_select);
        $this->testWithRequired($color_select);
        $this->testWithDisabled($color_select);
        $this->testWithAdditionalOnloadCodeRendersId($color_select);
    }

    public function testRenderValue(): void
    {
        $f = $this->getFieldFactory();
        $label = "label";
        $byline = "byline";
        $value = "value_0";
        $cp = $f->colorSelect($label, $byline)
                ->withValue($value)
                ->withNameFrom($this->name_source);

        $expected = $this->getFormWrappedHtml(
            'color-select-field-input',
            $label,
            '<input id="id_1" type="color" name="name_0" value="value_0" class="c-field-color-select"/>',
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
        $cp = $f->colorSelect($label, $byline)
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
        $color_select = $f->colorSelect("label", "byline");
        $this->expectException(\InvalidArgumentException::class);
        $color_select->withValue(null);
    }
}
