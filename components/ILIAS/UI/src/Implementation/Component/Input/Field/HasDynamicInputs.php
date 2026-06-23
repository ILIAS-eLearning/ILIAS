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

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Implementation\Component\Input\DynamicInputDataIterator;
use ILIAS\UI\Implementation\Component\Input\DynamicInputsNameSource;
use ILIAS\UI\Implementation\Component\Input\NameSource;
use ILIAS\UI\Component\Input\InputData;
use ILIAS\UI\Component\Input\Container\Form\FormInput as FormInputInterface;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;
use InvalidArgumentException;
use LogicException;
use ILIAS\Language\Language;

/**
 * Describes an Input Field which dynamically generates inputs according to a template.
 * This happens on both server and client when values are provided.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
abstract class HasDynamicInputs extends FormInput
{
    // ==========================================
    // BEGIN IMPLEMENTATION OF HasDynamicInputs
    // ==========================================

    /**
     * @var FormInputInterface[]
     */
    protected array $generated_dynamic_inputs = [];

    public function __construct(
        protected Language $language,
        DataFactory $data_factory,
        Refinery $refinery,
        protected FormInputInterface $dynamic_input_template,
        string $label,
        ?string $byline
    ) {
        parent::__construct($data_factory, $refinery, $label, $byline);
    }

    /**
     * Returns an Input Field which will be used to generate similar inputs
     * on both server and client.
     */
    public function getTemplateForDynamicInputs(): FormInputInterface
    {
        return $this->dynamic_input_template;
    }

    /**
     * Returns serverside generated dynamic Inputs, which happens when
     * providing values with @see Input::withValue()
     *
     * @return FormInputInterface[]
     */
    public function getGeneratedDynamicInputs(): array
    {
        return $this->generated_dynamic_inputs;
    }

    // ==========================================
    // END IMPLEMENTATION OF HasDynamicInputs
    // ==========================================

    // ==========================================
    // BEGIN OVERWRITTEN METHODS OF Input
    // ==========================================

    /**
     * @param mixed $value
     */
    public function withValue($value): self
    {
        $this->checkArg('value', null === $value || $this->isClientSideValueOk($value), "Display value does not match input(-template) type.");
        $clone = clone $this;

        if (null === $value) {
            $clone->generated_dynamic_inputs = [];
            return $clone;
        }

        if (!is_array($value)) {
            $clone->generated_dynamic_inputs[] = $clone->getTemplateForDynamicInputs()->withValue($value);
            return $clone;
        }

        foreach ($value as $input_name => $input_value) {
            $clone->generated_dynamic_inputs[$input_name] = $clone->getTemplateForDynamicInputs()->withValue($input_value);
        }

        return $clone;
    }

    public function withDisabled(bool $is_disabled): self
    {
        $clone = parent::withDisabled($is_disabled);
        $clone->dynamic_input_template = $clone->getTemplateForDynamicInputs()->withDisabled($is_disabled);

        foreach ($clone->generated_dynamic_inputs as $key => $input) {
            $clone->generated_dynamic_inputs[$key] = $input->withDisabled($is_disabled);
        }

        return $clone;
    }

    public function withNameFrom(NameSource $source, ?string $parent_name = null): self
    {
        $clone = parent::withNameFrom($source, $parent_name);

        $clone->dynamic_input_template = $clone->getTemplateForDynamicInputs()->withNameFrom(
            new DynamicInputsNameSource($clone->getName())
        );

        foreach ($clone->generated_dynamic_inputs as $key => $input) {
            $clone->generated_dynamic_inputs[$key] = $input->withNameFrom(
                new DynamicInputsNameSource($clone->getName())
            );
        }

        return $clone;
    }

    public function withInput(InputData $post_data): self
    {
        if (null === $this->getName()) {
            throw new LogicException(static::class . '::withNameFrom must be called first.');
        }

        $clone = clone $this;
        $contains_error = false;
        $contents = [];

        foreach ((new DynamicInputDataIterator($post_data, $clone->getName())) as $index => $input_data) {
            $clone->generated_dynamic_inputs[$index] = $clone->getTemplateForDynamicInputs()->withInput($input_data);
            if ($clone->generated_dynamic_inputs[$index]->getContent()->isOk()) {
                $contents[] = $clone->generated_dynamic_inputs[$index]->getContent()->value();
            } else {
                $contains_error = true;
            }
        }

        if ($contains_error) {
            $clone->content = $clone->data_factory->error($this->language->txt("ui_error_in_group"));
        } else {
            $clone->content = $clone->applyOperationsTo($contents);
        }

        if ($clone->content->isError()) {
            $clone = $clone->withError((string) $clone->content->error());
        }

        return $clone;
    }

    public function getValue(): array
    {
        $values = [];
        foreach ($this->getGeneratedDynamicInputs() as $key => $input) {
            $values[$key] = $input->getValue();
        }

        return $values;
    }

    // ==========================================
    // END OVERWRITTEN METHODS OF Input
    // ==========================================

    /**
     * @param mixed $value
     */
    protected function isClientSideValueOk($value): bool
    {
        if (!is_array($value)) {
            return $this->getTemplateForDynamicInputs()->isClientSideValueOk($value);
        }

        foreach ($value as $input_value) {
            if (!$this->getTemplateForDynamicInputs()->isClientSideValueOk($input_value)) {
                return false;
            }
        }

        return true;
    }
}
