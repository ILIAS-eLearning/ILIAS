<?php declare(strict_types=1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\UI\Component\Input\Field;

use ILIAS\UI\Implementation\Component\Input\Field\DynamicInputsAwareInput;
use ILIAS\UI\Implementation\Component\Input\NameSource;
use ILIAS\UI\Component\Input\Field\DynamicInputsAware;
use ILIAS\UI\Component\Input\Field\Input;
use PHPUnit\Framework\TestCase;
use ILIAS\Refinery\Constraint;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;
use ilLanguage;
use Closure;

/**
 * @author  Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class DynamicInputsAwareTest extends TestCase
{
    protected DynamicInputsAware $input;
    protected DataFactory $data_factory;
    protected ilLanguage $language;
    protected Refinery $refinery;

    public function setUp() : void
    {
        $this->data_factory = $this->createMock(DataFactory::class);
        $this->language = $this->createMock(ilLanguage::class);
        $this->refinery = $this->createMock(Refinery::class);
        $this->input = new class(
            $this->language,
            $this->data_factory,
            $this->refinery,
            'test_input_name',
            $this->getTestInputTemplate(),
            'test_byline'
        ) extends DynamicInputsAwareInput {
            public function getUpdateOnLoadCode() : Closure
            {
                return static function () {
                };
            }

            protected function getConstraintForRequirement() : ?Constraint
            {
                return null;
            }

            protected function isClientSideValueOk($value) : bool
            {
                return true;
            }
        };
    }

    public function testDynamicInputTemplateDuplication() : void
    {
        $dynamic_input = $this->input->withValue([
            'val1',
            'val2'
        ]);

        $this->assertCount(
            2,
            $dynamic_input->getDynamicInputs()
        );
    }

    public function testDynamicInputWithValue() : void
    {
        $input_value_1 = 'val1';
        $input_value_2 = 'val2';
        $dynamic_input = $this->input->withValue([
            $input_value_1,
            $input_value_2,
        ]);

        $generated_inputs = $dynamic_input->getDynamicInputs();
        $this->assertEquals($input_value_1, $generated_inputs[0]->getValue());
        $this->assertEquals($input_value_2, $generated_inputs[1]->getValue());
    }

    public function testDynamicInputDisabilityBeforeDuplication() : void
    {
        $dynamic_input = $this->input;
        $this->assertFalse($dynamic_input->getTemplateForDynamicInputs()->isDisabled());
        $this->assertFalse($dynamic_input->isDisabled());

        $dynamic_input = $this->input->withDisabled(true);

        $this->assertTrue($dynamic_input->getTemplateForDynamicInputs()->isDisabled());
        $this->assertTrue($dynamic_input->isDisabled());
    }

    public function testDynamicInputDisabilityAfterDuplication() : void
    {
        $dynamic_input = $this->input->withValue(['', '']);
        $generated_inputs = $dynamic_input->getDynamicInputs();

        $this->assertFalse($generated_inputs[0]->isDisabled());
        $this->assertFalse($generated_inputs[1]->isDisabled());
        $this->assertFalse($dynamic_input->getTemplateForDynamicInputs()->isDisabled());
        $this->assertFalse($dynamic_input->isDisabled());

        $dynamic_input = $dynamic_input->withDisabled(true);
        $generated_inputs = $dynamic_input->getDynamicInputs();

        $this->assertTrue($generated_inputs[0]->isDisabled());
        $this->assertTrue($generated_inputs[1]->isDisabled());
        $this->assertTrue($dynamic_input->getTemplateForDynamicInputs()->isDisabled());
        $this->assertTrue($dynamic_input->isDisabled());
    }

    /**
     * the input names are always the same, because the names generated from
     * DynamicInputsNameSource are stackable.
     */
    public function testDynamicInputNameGeneration() : void
    {
        $input_name = 'test_name[form_input_0][]';
        $dynamic_input = $this->input->withValue(['', '']);
        $dynamic_input = $dynamic_input->withNameFrom(
            $this->getTestNameSource()
        );

        $this->assertEquals(
            $input_name,
            $dynamic_input->getTemplateForDynamicInputs()->getName()
        );

        $generated_inputs = $dynamic_input->getDynamicInputs();
        $this->assertEquals(
            $input_name,
            $generated_inputs[0]->getName()
        );

        $this->assertEquals(
            $input_name,
            $generated_inputs[1]->getName()
        );
    }
    
    protected function getTestNameSource() : NameSource
    {
        return new class() implements NameSource {
            public function getNewName() : string
            {
                return 'test_name';
            }
        };
    }

    protected function getTestInputTemplate() : Input
    {
        return new class(
            $this->data_factory,
            $this->refinery,
            'input_template_name',
            'input_template_byline'
        ) extends \ILIAS\UI\Implementation\Component\Input\Field\Input {
            public function getUpdateOnLoadCode() : Closure
            {
                return static function () {
                };
            }

            protected function getConstraintForRequirement() : ?Constraint
            {
                return null;
            }

            protected function isClientSideValueOk($value) : bool
            {
                return true;
            }
        };
    }
}