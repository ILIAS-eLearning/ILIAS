<?php

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Component\Input\Field\AdditionalFormInputsAware;
use ILIAS\UI\Component\Input\Field\Input as InputInterface;
use ILIAS\UI\Implementation\Component\Input\NameSource;
use ILIAS\UI\Component\Input\Field\FormInput;
use ILIAS\UI\Implementation\Component\Input\SubordinateNameSource;
use ILIAS\UI\Implementation\Component\Input\InputData;
use ILIAS\UI\Implementation\Component\Input\Container\Form\ArrayInputData;
use ILIAS\Data\Result\Ok;

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
        $post_data = $input->getOr($clone->getName(), null);

        $contents = [];
        if (!empty($post_data) && null !== ($template = $clone->getTemplateForAdditionalInputs())) {
            foreach ($post_data as $input_data) {
                $data = [];
                foreach ($input_data as $key => $value) {
                    $input_name = "{$clone->getName()}[" . SubordinateNameSource::INDEX_PLACEHOLDER . "][$key]";
                    $data[$input_name] = $value;
                }

                $template = $template->withInput(new ArrayInputData($data));
                $content  = $template->getContent();

                if ($content->isOk()) {
                    $content = $content->value();
                    if (is_array($content) && !empty($content)) {
                        foreach ($content as $key => $val) {
                            $contents[$key] = $val;
                        }
                    } else {
                        $contents[] = $content;
                    }
                }
            }

            $clone->content = $clone->applyOperationsTo($contents);

            if ($clone->content->isError()) {
                $clone = $clone->withError($clone->content->error());
            }
        } else {
            $clone->content = new Ok(null);
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

        $named_inputs = [];
        if (null !== $clone->additional_inputs) {
            foreach ($clone->additional_inputs as $key => $input) {
                $named_inputs[$key] = $input->withNameFrom(
                    new SubordinateNameSource($clone->getName())
                );
            }
        }

        $clone->additional_inputs = $named_inputs;

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