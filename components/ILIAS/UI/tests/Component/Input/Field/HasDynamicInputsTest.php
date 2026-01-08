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

namespace ILIAS\Tests\UI\Component\Input\Field;

use ILIAS\UI\Implementation\Component\Input\Field\HasDynamicInputs;
use ILIAS\UI\Implementation\Component\Input\NameSource;
use PHPUnit\Framework\TestCase;
use ILIAS\Refinery\Constraint;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Language\Language;
use Closure;
use NameSourceStubs;
use ILIAS\UI\Implementation\Component\Input\HasDynamicInputsNameSource;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @author  Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class HasDynamicInputsTest extends TestCase
{
    use NameSourceStubs;

    protected HasDynamicInputsNameSource & MockObject $has_dynamic_inputs_name_source_stub;
    protected string $dynamic_input_name = 'dynamic_input_name';

    protected HasDynamicInputs $input;
    protected DataFactory $data_factory;
    protected Language $language;
    protected Refinery $refinery;

    public function setUp(): void
    {
        $this->has_dynamic_inputs_name_source_stub = $this->createFixedHasDynamicInputsNameSourceStub($this->dynamic_input_name);
        $this->data_factory = $this->createMock(DataFactory::class);
        $this->language = $this->createMock(Language::class);
        $this->refinery = $this->createMock(Refinery::class);
        $this->input = new class (
            $this->language,
            $this->data_factory,
            $this->refinery,
            $this->has_dynamic_inputs_name_source_stub,
            $this->getTestInputTemplate(),
            'test_input_name',
            'test_byline'
        ) extends HasDynamicInputs {
            public function getUpdateOnLoadCode(): Closure
            {
                return static function () {
                };
            }

            protected function getConstraintForRequirement(): ?Constraint
            {
                return null;
            }

            public function isClientSideValueOk($value): bool
            {
                return true;
            }
        };
    }

    public function testDynamicInputTemplateDuplication(): void
    {
        $dynamic_input = $this->input->withValue([
            'val1',
            'val2'
        ]);

        $this->assertCount(
            2,
            $dynamic_input->getGeneratedDynamicInputs()
        );
    }

    public function testDynamicInputWithValue(): void
    {
        $input_value_1 = 'val1';
        $input_value_2 = 'val2';
        $dynamic_input = $this->input->withValue([
            $input_value_1,
            $input_value_2,
        ]);

        $generated_inputs = $dynamic_input->getGeneratedDynamicInputs();
        $this->assertEquals($input_value_1, $generated_inputs[0]->getValue());
        $this->assertEquals($input_value_2, $generated_inputs[1]->getValue());
    }

    public function testDynamicInputDisabilityBeforeDuplication(): void
    {
        $dynamic_input = $this->input;
        $this->assertFalse($dynamic_input->getTemplateForDynamicInputs()->isDisabled());
        $this->assertFalse($dynamic_input->isDisabled());

        $dynamic_input = $this->input->withDisabled(true);

        $this->assertTrue($dynamic_input->getTemplateForDynamicInputs()->isDisabled());
        $this->assertTrue($dynamic_input->isDisabled());
    }

    public function testDynamicInputDisabilityAfterDuplication(): void
    {
        $dynamic_input = $this->input->withValue(['', '']);
        $generated_inputs = $dynamic_input->getGeneratedDynamicInputs();

        $this->assertFalse($generated_inputs[0]->isDisabled());
        $this->assertFalse($generated_inputs[1]->isDisabled());
        $this->assertFalse($dynamic_input->getTemplateForDynamicInputs()->isDisabled());
        $this->assertFalse($dynamic_input->isDisabled());

        $dynamic_input = $dynamic_input->withDisabled(true);
        $generated_inputs = $dynamic_input->getGeneratedDynamicInputs();

        $this->assertTrue($generated_inputs[0]->isDisabled());
        $this->assertTrue($generated_inputs[1]->isDisabled());
        $this->assertTrue($dynamic_input->getTemplateForDynamicInputs()->isDisabled());
        $this->assertTrue($dynamic_input->isDisabled());
    }

    public function testWithNameFrom(): void
    {
        $default_input_name = 'default_input_name';
        $default_name_source = $this->createFixedNameSourceStub($default_input_name);

        $this->has_dynamic_inputs_name_source_stub->expects($this->atLeast(1))->method('withReset');
        $this->has_dynamic_inputs_name_source_stub->expects($this->atLeast(1))->method('withParentName')->with($default_input_name);
        $this->has_dynamic_inputs_name_source_stub->method('withIndices')->willReturnCallback(function($arg) {
            static $count = 0;
            $expected = [false, true];
            $this->assertEquals($expected[$count], $arg);
            $count++;
            return $this->has_dynamic_inputs_name_source_stub;
        });

        $dynamic_input = $this->input->withNameFrom($default_name_source);
    }

    public function testWithNameFromWithDedicatedName(): void
    {
        $dedicated_name = 'dedicated_input_name';
        $default_name_source = $this->createRelayArgumentNameSourceStub();

        $this->has_dynamic_inputs_name_source_stub->expects($this->exactly(2))->method('withReset');
        $this->has_dynamic_inputs_name_source_stub->expects($this->exactly(2))->method('withParentName')->with($dedicated_name);
        $this->has_dynamic_inputs_name_source_stub->expects($this->exactly(2))->method('withResetDefaultNameSource');
        $this->has_dynamic_inputs_name_source_stub->method('withIndices')->willReturnCallback(function($arg) {
            static $count = 0;
            $expected = [false, true];
            $this->assertEquals($expected[$count], $arg);
            $count++;
            return $this->has_dynamic_inputs_name_source_stub;
        });

        $dynamic_input = $this->input->withDedicatedName($dedicated_name)->withValue(['', ''])->withNameFrom($default_name_source);
    }

    protected function getTestInputTemplate()
    {
        return new class ($this->data_factory, $this->refinery, 'input_template_name', 'input_template_byline') extends \ILIAS\UI\Implementation\Component\Input\Field\FormInput {
            public function getUpdateOnLoadCode(): Closure
            {
                return static function () {
                };
            }

            protected function getConstraintForRequirement(): ?Constraint
            {
                return null;
            }

            public function isClientSideValueOk($value): bool
            {
                return true;
            }
        };
    }
}
