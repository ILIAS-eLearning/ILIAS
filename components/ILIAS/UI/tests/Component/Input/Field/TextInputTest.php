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
use ILIAS\UI\Implementation\Component\SignalGenerator;
use ILIAS\UI\Component\Input\Field;
use ILIAS\Data;
use ILIAS\Refinery\Factory as Refinery;

class TextInputTest extends ILIAS_UI_TestBase
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

        $text = $f->text("label", "byline");

        $this->assertInstanceOf(\ILIAS\UI\Component\Input\Container\Form\FormInput::class, $text);
        $this->assertInstanceOf(Field\Text::class, $text);
    }

    public function testRender(): void
    {
        $f = $this->getFieldFactory();
        $label = "label";
        $byline = "byline";
        $text = $f->text($label, $byline)->withNameFrom($this->name_source);
        $expected = $this->getFormWrappedHtml(
            'text-field-input',
            $label,
            '<input id="id_1" type="text" name="name_0" class="c-field-text" />',
            $byline,
            'id_1'
        );
        $this->assertEquals($expected, $this->render($text));
    }

    public function testCommonRendering(): void
    {
        $f = $this->getFieldFactory();
        $label = "label";
        $text = $f->text($label)->withNameFrom($this->name_source);

        $this->testWithError($text);
        $this->testWithNoByline($text);
        $this->testWithRequired($text);
        $this->testWithDisabled($text);
        $this->testWithAdditionalOnloadCodeRendersId($text);
    }

    public function testRenderValue(): void
    {
        $f = $this->getFieldFactory();
        $label = "label";
        $value = "value";
        $text = $f->text($label)->withValue($value)->withNameFrom($this->name_source);
        $expected = $this->getFormWrappedHtml(
            'text-field-input',
            $label,
            '<input id="id_1" type="text" value="value" name="name_0" class="c-field-text" />',
            null,
            'id_1'
        );
        $this->assertEquals($expected, $this->render($text));
    }

    public function testMaxLength(): void
    {
        $f = $this->getFieldFactory();

        $text = $f->text("")
        ->withMaxLength(4);

        $this->assertEquals(4, $text->getMaxLength());

        $text1 = $text->withValue("1234");
        $this->assertEquals("1234", $text1->getValue());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Argument 'value': Display value does not match input type.");
        $text->withValue("12345");
    }

    public function testRenderMaxValue(): void
    {
        $f = $this->getFieldFactory();
        $label = "label";
        $text = $f->text($label)->withNameFrom($this->name_source)->withMaxLength(8);
        $expected = $this->getFormWrappedHtml(
            'text-field-input',
            $label,
            '<input id="id_1" type="text" name="name_0" maxlength="8" class="c-field-text" />',
            null,
            'id_1'
        );
        $this->assertEquals($expected, $this->render($text));
    }

    public function testValueRequired(): void
    {
        $f = $this->getFieldFactory();
        $label = "label";
        $name = "name_0";
        $text = $f->text($label)->withNameFrom($this->name_source)->withRequired(true);

        $text1 = $text->withInput(new DefInputData([$name => "0"]));
        $value1 = $text1->getContent();
        $this->assertTrue($value1->isOk());
        $this->assertEquals("0", $value1->value());

        $text2 = $text->withInput(new DefInputData([$name => ""]));
        $value2 = $text2->getContent();
        $this->assertTrue($value2->isError());
    }

    public function testStripsTags(): void
    {
        $f = $this->getFieldFactory();
        $name = "name_0";
        $text = $f->text("")
            ->withNameFrom($this->name_source)
            ->withInput(new DefInputData([$name => "<script>alert()</script>"]));

        $content = $text->getContent();
        $this->assertEquals("alert()", $content->value());
    }
}
