<?php

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Component\Input\Field\AdditionalFormInputsAware;
use ILIAS\UI\Component\Input\Field\Input as InputInterface;
use ILIAS\UI\Implementation\Component\Input\NameSource;
use ILIAS\UI\Component\Input\Field\FormInput;
use ILIAS\UI\Implementation\Component\Input\SubordinateNameSource;
use ILIAS\UI\Implementation\Component\Input\InputData;

/**
 * Class AdditionalFormInputAwareInput can be inherited in order
 * to enable sub-inputs.
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
abstract class AdditionalFormInputAwareInput extends Input implements AdditionalFormInputsAware
{
    /**
     * Holds the input template for additional sub-inputs.
     *
     * @var FormInput|null
     */
    protected ?InputInterface $input_template = null;

    /**
     * Holds the additional inputs generated from the current
     * input template, after withValue() is called.
     *
     * @var InputInterface[]|null
     */
    protected ?array $additional_inputs = null;

    /**
     * @inheritDoc
     */
    public function withTemplateForAdditionalInputs(FormInput $template) : AdditionalFormInputsAware
    {
        $this->checkArgInstanceOf('template', $template, FormInput::class);

        $clone = clone $this;
        $clone->input_template = $template;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getTemplateForAdditionalInputs() : ?FormInput
    {
        return $this->input_template;
    }

    /**
     * @inheritDoc
     */
    public function getAdditionalInputs() : ?array
    {
        return $this->additional_inputs;
    }

    /**
     * @inheritDoc
     */
    public function withRequired($is_required)
    {
        /** @var $clone self */
        $clone = parent::withRequired($is_required);
        if (null !== $clone->input_template) {
            $clone->input_template = $clone->input_template->withRequired($is_required);
        }

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function withDisabled($is_disabled)
    {
        /** @var $clone self */
        $clone = parent::withDisabled($is_disabled);
        if (null !== $clone->input_template) {
            $clone->input_template = $clone->input_template->withDisabled($is_disabled);
        }

        return $clone;
    }

    /**
     * @TODO: discuss if values should be separated e.g. by
     *        withValueForAdditionalInputs().
     *
     * @inheritDoc
     */
    public function withValue($value)
    {
        $this->checkArg("value", $this->isClientSideValueOk($value), "Display value does not match input type.");
        $clone = clone $this;

        foreach ($value as $input_value) {
            $clone->additional_inputs[] = $clone->input_template->withValue($input_value);
        }

        return $clone;
    }

    /**
     * @TODO: discuss how transformation should be applied and/or
     *        handled.
     *
     * @inheritDoc
     */
    public function withInput(InputData $input)
    {
        if (null === $this->getName()) {
            throw new \LogicException("Can only collect if input has a name.");
        }

        $clone = clone $this;
        $post_data = $input->getOr($this->getName(), null);

        if (!empty($post_data) && null !== $clone->input_template) {
            foreach ($post_data as $input_value) {
                /** @var $tpl_input FormInput */
                $tpl_input = $clone->input_template->withValue($input_value);
                $tpl_input->content = $tpl_input->applyOperationsTo($input_value);
                if ($tpl_input->content->isError()) {
                    $tpl_input = $tpl_input->withError($tpl_input->content->error());
                }

                $clone->additional_inputs[] = $tpl_input;
            }
        }

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getValue()
    {
        $values = [];
        foreach ($this->getAdditionalInputs() as $input) {
            $values[] = $input->getValue();
        }

        return $values;
    }

    /**
     * @inheritDoc
     */
    public function withNameFrom(NameSource $source)
    {
        /** @var $clone self */
        $clone = parent::withNameFrom($source);
        if (null !== $clone->input_template) {
            $clone->input_template = $clone->input_template->withNameFrom(
                new SubordinateNameSource($clone->getName())
            );
        }

        return $clone;
    }

    /**
     * @inheritDoc
     */
    protected function isClientSideValueOk($value) : bool
    {
        if (!is_array($value)) {
            return $this->input_template->isClientSideValueOk($value);
        }

        if (empty($value)) {
            return false;
        }

        foreach ($value as $input_value) {
            if (!$this->input_template->isClientSideValueOk($input_value)) {
                return false;
            }
        }

        return true;
    }
}