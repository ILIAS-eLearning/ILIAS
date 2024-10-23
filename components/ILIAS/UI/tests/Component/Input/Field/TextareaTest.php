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

class TextareaTest extends ILIAS_UI_TestBase
{
    use CommonFieldRendering;

    private DefNamesource $name_source;

    public function setUp(): void
    {
        $this->name_source = new DefNamesource();
    }

    public function testImplementsFactoryInterface(): void
    {
        $f = $this->getFieldFactory();
        $textarea = $f->textarea("label", "byline");
        $this->assertInstanceOf(\ILIAS\UI\Component\Input\Container\Form\FormInput::class, $textarea);
        $this->assertInstanceOf(Field\Textarea::class, $textarea);
    }

    public function testImplementsFactoryInterface_without_byline(): void
    {
        $f = $this->getFieldFactory();
        $textarea = $f->textarea("label");
        $this->assertInstanceOf(\ILIAS\UI\Component\Input\Container\Form\FormInput::class, $textarea);
        $this->assertInstanceOf(Field\Textarea::class, $textarea);
    }

    public function testWithMinLimit(): void
    {
        $f = $this->getFieldFactory();
        $limit = 5;
        $textarea = $f->textarea('label')->withMinLimit($limit);
        $this->assertInstanceOf(\ILIAS\UI\Component\Input\Container\Form\FormInput::class, $textarea);
        $this->assertInstanceOf(Field\Textarea::class, $textarea);
        $this->assertEquals($textarea->getMinLimit(), $limit);
    }

    public function testWithMaxLimit(): void
    {
        $f = $this->getFieldFactory();
        $limit = 15;
        $textarea = $f->textarea('label')->withMaxLimit($limit);
        $this->assertInstanceOf(\ILIAS\UI\Component\Input\Container\Form\FormInput::class, $textarea);
        $this->assertInstanceOf(Field\Textarea::class, $textarea);
        $this->assertEquals($textarea->getMaxLimit(), $limit);
    }

    public function testIsLimited(): void
    {
        $f = $this->getFieldFactory();

        // with min limit
        $textarea = $f->textarea('label')->withMinLimit(5);
        $this->assertTrue($textarea->isLimited());

        // with max limit
        $textarea = $f->textarea('label')->withMaxLimit(5);
        $this->assertTrue($textarea->isLimited());

        // with min-max limit
        $textarea = $f->textarea('label')->withMinLimit(5)->withMaxLimit(20);
        $this->assertTrue($textarea->isLimited());

        // without limit
        $textarea = $f->textarea('label');
        $this->assertFalse($textarea->isLimited());
    }

    public function testGetMinLimit(): void
    {
        $f = $this->getFieldFactory();
        $limit = 5;
        $textarea = $f->textarea('label')->withMinLimit($limit);
        $this->assertEquals($textarea->getMinLimit(), $limit);
    }

    public function testGetMaxLimit(): void
    {
        $f = $this->getFieldFactory();
        $limit = 15;
        $textarea = $f->textarea('label')->withMaxLimit($limit);
        $this->assertEquals($textarea->getMaxLimit(), $limit);
    }

    // RENDERER
    public function testRenderer(): void
    {
        $f = $this->getFieldFactory();
        $label = "label";
        $byline = "byline";
        $textarea = $f->textarea($label, $byline)->withNameFrom($this->name_source);
        $expected = $this->getFormWrappedHtml(
            'textarea-field-input',
            $label,
            '
            <textarea id="id_1" class="c-field-textarea" name="name_0"></textarea>
            ',
            $byline,
            'id_1',
            'id_2',
        );
        $this->assertEquals($expected, $this->render($textarea));
    }

    public function testCommonRendering(): void
    {
        $f = $this->getFieldFactory();
        $label = "label";
        $textarea = $f->textarea($label)->withNameFrom($this->name_source);

        $this->testWithError($textarea);
        $this->testWithNoByline($textarea);
        $this->testWithRequired($textarea);
        $this->testWithDisabled($textarea);
        $this->testWithAdditionalOnloadCodeRendersId($textarea);
    }

    public function testRendererWithMinLimit(): void
    {
        $f = $this->getFieldFactory();
        $label = "label";
        $min = 5;
        $byline = "This is just a byline Min: " . $min;
        $textarea = $f->textarea($label, $byline)->withMinLimit($min)->withNameFrom($this->name_source);
        $expected = $this->getFormWrappedHtml(
            'textarea-field-input',
            $label,
            '
            <textarea id="id_1" class="c-field-textarea" name="name_0" minlength="5"></textarea>
            ',
            $byline,
            'id_1',
            'id_2'
        );
        $this->assertEquals($expected, $this->render($textarea));
    }

    public function testRendererWithMaxLimit(): void
    {
        $f = $this->getFieldFactory();
        $label = "label";
        $max = 20;
        $byline = "This is just a byline Max: " . $max;
        $textarea = $f->textarea($label, $byline)->withMaxLimit($max)->withNameFrom($this->name_source);
        $expected = $this->getFormWrappedHtml(
            'textarea-field-input',
            $label,
            '
                <textarea id="id_1" class="c-field-textarea" name="name_0" maxlength="20"></textarea>
                <div class="ui-input-textarea-remainder"> ui_chars_remaining<span data-action="remainder">20</span></div>
            ',
            $byline,
            'id_1',
            'id_2'
        );
        $this->assertEquals($expected, $this->render($textarea));
    }

    public function testRendererWithMinAndMaxLimit(): void
    {
        $f = $this->getFieldFactory();
        $r = $this->getDefaultRenderer();
        $name = "name_0";
        $id = "id_1";
        $label = "label";
        $min = 5;
        $max = 20;
        $byline = "This is just a byline Min: " . $min . " Max: " . $max;
        $textarea = $f->textarea($label, $byline)->withMinLimit($min)->withMaxLimit($max)->withNameFrom(
            $this->name_source
        );

        $expected = $this->brutallyTrimHTML("
            <textarea id=\"$id\" class=\"c-field-textarea\" name=\"$name\" minlength=\"5\" maxlength=\"20\"></textarea>
            <div class=\"ui-input-textarea-remainder\"> ui_chars_remaining <span data-action=\"remainder\">$max</span> </div>
        ");
        $this->assertStringContainsString($expected, $this->render($textarea));
    }

    public function testRendererCounterWithValue(): void
    {
        $f = $this->getFieldFactory();
        $id = 'id_1';
        $label = "label";
        $byline = "byline";
        $name = "name_0";
        $value = "Lorem ipsum dolor sit";
        $textarea = $f->textarea($label, $byline)->withValue($value)->withNameFrom($this->name_source);

        $expected = $this->brutallyTrimHTML("
            <div class=\"c-input__field\">
                <textarea id=\"$id\" class=\"c-field-textarea\" name=\"$name\">$value</textarea>
            </div>
        ");
        $this->assertStringContainsString($expected, $this->render($textarea));
    }

    public function testStripsTags(): void
    {
        $f = $this->getFieldFactory();
        $name = "name_0";
        $text = $f->textarea("")
            ->withNameFrom($this->name_source)
            ->withInput(new DefInputData([$name => "<script>alert()</script>"]));

        $content = $text->getContent();
        $this->assertEquals("alert()", $content->value());
    }
}
