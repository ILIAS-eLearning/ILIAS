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

class NumericInputTest extends ILIAS_UI_TestBase
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

        $numeric = $f->numeric("label", "byline");

        $this->assertInstanceOf(\ILIAS\UI\Component\Input\Container\Form\FormInput::class, $numeric);
        $this->assertInstanceOf(Field\Numeric::class, $numeric);
    }


    public function testRender(): void
    {
        $f = $this->getFieldFactory();
        $label = "label";
        $byline = "byline";
        $numeric = $f->numeric($label, $byline)->withNameFrom($this->name_source);

        $expected = $this->getFormWrappedHtml(
            'numeric-field-input',
            $label,
            '<input id="id_1" type="number" name="name_0" class="c-field-number" />',
            $byline,
            'id_1'
        );
        $this->assertEquals($expected, $this->render($numeric));
    }

    public function testCommonRendering(): void
    {
        $f = $this->getFieldFactory();
        $label = "label";
        $numeric = $f->numeric($label)->withNameFrom($this->name_source);

        $this->testWithError($numeric);
        $this->testWithNoByline($numeric);
        $this->testWithRequired($numeric);
        $this->testWithDisabled($numeric);
        $this->testWithAdditionalOnloadCodeRendersId($numeric);
    }

    public function testRenderValue(): void
    {
        $f = $this->getFieldFactory();
        $label = "label";
        $value = "10";
        $numeric = $f->numeric($label)->withValue($value)->withNameFrom($this->name_source);

        $expected = $this->getFormWrappedHtml(
            'numeric-field-input',
            $label,
            '<input id="id_1" type="number" value="10" name="name_0" class="c-field-number" />',
            null,
            'id_1'
        );
        $this->assertEquals($expected, $this->render($numeric));
    }

    public function testNullValue(): \ILIAS\UI\Component\Input\Container\Form\FormInput
    {
        $f = $this->getFieldFactory();
        $post_data = new DefInputData(['name_0' => null]);
        $field = $f->numeric('')->withNameFrom($this->name_source);
        $field_required = $field->withRequired(true);

        $value = $field->withInput($post_data)->getContent();
        $this->assertTrue($value->isOk());
        $this->assertNull($value->value());

        $value = $field_required->withInput($post_data)->getContent();
        $this->assertTrue($value->isError());
        return $field;
    }

    /**
     * @depends testNullValue
     */
    public function testEmptyValue(\ILIAS\UI\Component\Input\Container\Form\FormInput $field): void
    {
        $post_data = new DefInputData(['name_0' => '']);
        $field_required = $field->withRequired(true);

        $value = $field->withInput($post_data)->getContent();
        $this->assertTrue($value->isOk());
        $this->assertNull($value->value());

        $field_required = $field_required->withInput($post_data);
        $value = $field_required->getContent();
        $this->assertTrue($value->isError());
    }

    /**
     * @depends testNullValue
     */
    public function testZeroIsValidValue(\ILIAS\UI\Component\Input\Container\Form\FormInput $field): void
    {
        $post_data = new DefInputData(['name_0' => 0]);
        $field_required = $field->withRequired(true);

        $value = $field->withInput($post_data)->getContent();
        $this->assertTrue($value->isOk());
        $this->assertEquals(0, $value->value());

        $value = $field_required->withInput($post_data)->getContent();
        $this->assertTrue($value->isOK());
        $this->assertEquals(0, $value->value());
    }

    /**
     * @depends testNullValue
     */
    public function testConstraintForRequirementForFloat(\ILIAS\UI\Component\Input\Container\Form\FormInput $field): void
    {
        $post_data = new DefInputData(['name_0' => 1.1]);
        $field_required = $field->withRequired(true);

        $value = $field->withInput($post_data)->getContent();
        $this->assertTrue($value->isOk());
        //Note, this float will be Transformed to int, since this input only accepts int
        $this->assertEquals(1, $value->value());

        $value = $field_required->withInput($post_data)->getContent();
        $this->assertTrue($value->isOK());
        $this->assertEquals(1, $value->value());
    }
}
