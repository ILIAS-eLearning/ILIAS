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

use ILIAS\UI\Component\Input\InputData;
use ILIAS\UI\Component\Input\Field;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data;

class CheckboxInputTest extends ILIAS_UI_TestBase
{
    use CommonFieldRendering;

    protected DefNamesource $name_source;
    protected Refinery $refinery;

    public function setUp(): void
    {
        $this->name_source = new DefNamesource();
        $this->refinery = new Refinery($this->createMock(Data\Factory::class), $this->createMock(ILIAS\Language\Language::class));
    }

    public function testImplementsFactoryInterface(): void
    {
        $f = $this->getFieldFactory();

        $checkbox = $f->checkbox("label", "byline");

        $this->assertInstanceOf(\ILIAS\UI\Component\Input\Container\Form\FormInput::class, $checkbox);
        $this->assertInstanceOf(Field\Checkbox::class, $checkbox);
    }

    public function testRender(): void
    {
        $f = $this->getFieldFactory();
        $label = "label";
        $byline = "byline";
        $checkbox = $f->checkbox($label, $byline)->withNameFrom($this->name_source);
        $expected = $this->getFormWrappedHtml(
            'checkbox-field-input',
            $label,
            '<input type="checkbox" id="id_1" value="checked" name="name_0" class="c-field-checkbox" />',
            $byline,
            'id_1'
        );
        $this->assertEquals($expected, $this->render($checkbox));
    }

    public function testRenderValue(): void
    {
        $f = $this->getFieldFactory();
        $label = "label";
        $value = true;
        $checkbox = $f->checkbox($label)->withValue($value)->withNameFrom($this->name_source);

        $expected = '<input type="checkbox" id="id_1" value="checked" checked="checked" name="name_0" class="c-field-checkbox" />';
        $this->assertStringContainsString($expected, $this->render($checkbox));
    }

    public function testHandleInvalidValue(): void
    {
        $f = $this->getFieldFactory();
        $label = "label";
        $value = "invalid";
        try {
            $f->checkbox($label)->withValue($value);
            $this->fail();
        } catch (InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function testCommonRendering(): void
    {
        $f = $this->getFieldFactory();
        $label = "label";
        $checkbox = $f->checkbox($label, null)->withNameFrom($this->name_source);

        $this->testWithError($checkbox);
        $this->testWithNoByline($checkbox);
        $this->testWithRequired($checkbox);
        $this->testWithDisabled($checkbox);
        $this->testWithAdditionalOnloadCodeRendersId($checkbox);
    }

    public function testTrueContent(): void
    {
        $f = $this->getFieldFactory();
        $label = "label";
        $checkbox = $f->checkbox($label)->withNameFrom($this->name_source);

        $input_data = $this->createMock(InputData::class);
        $input_data
            ->expects($this->atLeastOnce())
            ->method("getOr")
            ->with("name_0", "")
            ->willReturn("checked");

        $checkbox_true = $checkbox->withInput($input_data);

        $this->assertIsBool($checkbox_true->getContent()->value());
        $this->assertTrue($checkbox_true->getContent()->value());
    }

    public function testFalseContent(): void
    {
        $f = $this->getFieldFactory();
        $label = "label";
        $checkbox = $f->checkbox($label)->withNameFrom($this->name_source);

        $input_data = $this->createMock(InputData::class);
        $input_data
            ->expects($this->atLeastOnce())
            ->method("getOr")
            ->with("name_0", "")
            ->willReturn("");

        $checkbox_false = $checkbox->withInput($input_data);

        $this->assertIsBool($checkbox_false->getContent()->value());
        $this->assertFalse($checkbox_false->getContent()->value());
    }

    public function testDisabledContent(): void
    {
        $f = $this->getFieldFactory();
        $label = "label";
        $checkbox = $f->checkbox($label)
            ->withNameFrom($this->name_source)
            ->withDisabled(true)
            ->withValue(true)
            ->withInput($this->createMock(InputData::class))
        ;

        $this->assertIsBool($checkbox->getContent()->value());
        $this->assertTrue($checkbox->getContent()->value());
    }

    public function testTransformation(): void
    {
        $f = $this->getFieldFactory();
        $label = "label";
        $new_value = "NEW_VALUE";
        $checkbox = $f->checkbox($label)
            ->withNameFrom($this->name_source)
            ->withDisabled(true)
            ->withValue(true)
            ->withAdditionalTransformation($this->refinery->custom()->transformation(function ($v) use (&$called, $new_value): string {
                $called = $v;
                return $new_value;
            }))
            ->withInput($this->createMock(InputData::class))
        ;

        $this->assertIsString($checkbox->getContent()->value());
        $this->assertEquals($new_value, $checkbox->getContent()->value());
    }

    public function testNullValue(): void
    {
        $f = $this->getFieldFactory();
        $checkbox = $f->checkbox("label");
        $checkbox->withValue(null);
        $this->assertEquals(false, $checkbox->getValue());
    }
}
