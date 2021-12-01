<?php declare(strict_types=1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Implementation\Component\Input\Container\Form\ArrayInputData;
use ILIAS\UI\Implementation\Component\Input\DynamicInputsNameSource;
use ILIAS\UI\Implementation\Component\Input\NameSource;
use ILIAS\UI\Implementation\Component\Input\InputData;
use ILIAS\UI\Component\Input\Field\Input as InputInterface;
use ILIAS\UI\Component\Input\Field\DynamicInputsAware;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\Result\Ok;
use InvalidArgumentException;
use LogicException;
use ilLanguage;

/**
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
abstract class DynamicInputsAwareInput extends Input implements DynamicInputsAware
{
    // ==========================================
    // BEGIN IMPLEMENTATION OF DynamicInputsAware
    // ==========================================

    /**
     * @var InputInterface[]
     */
    protected array $dynamic_inputs = [];
    protected ?InputInterface $dynamic_input_template = null;
    protected ilLanguage $language;

    public function __construct(
        ilLanguage $language,
        DataFactory $data_factory,
        Refinery $refinery,
        string $label,
        ?string $byline
    ) {
        parent::__construct($data_factory, $refinery, $label, $byline);
        $this->language = $language;
    }

    /**
     * Provides an instance of Input, which is used to generate dynamic
     * inputs on clientside.
     */
    public function withTemplateForDynamicInputs(InputInterface $template) : self
    {
        $clone = clone $this;
        $clone->dynamic_input_template = $template;

        return $clone;
    }

    /**
     * Returns the instance of Input which should be used to generate
     * dynamic inputs on clientside.
     */
    public function getTemplateForDynamicInputs() : ?InputInterface
    {
        return $this->dynamic_input_template;
    }

    /**
     * Returns serverside generated dynamic Inputs, which happens when
     * providing this @return InputInterface[]
     * @see InputInterface::withValue().
     */
    public function getDynamicInputs() : array
    {
        return $this->dynamic_inputs;
    }

    // ==========================================
    // END IMPLEMENTATION OF DynamicInputsAware
    // ==========================================

    // ==========================================
    // BEGIN OVERWRITTEN METHODS OF Input
    // ==========================================

    /**
     * @param mixed $value
     */
    public function withValue($value) : self
    {
        // apply default behaviour if this input was not provided
        // with an actual input template.
        if (null === $this->getTemplateForDynamicInputs()) {
            return parent::withValue($value);
        }

        if (!$this->isDynamicInputsValueOk($value)) {
            throw new InvalidArgumentException("Display value does not match input type.");
        }

        $clone = clone $this;

        foreach ($value as $input_value) {
            $clone->dynamic_inputs[] = $clone->dynamic_input_template->withValue($input_value);
        }

        return $clone;
    }

    public function withDisabled(bool $is_disabled) : self
    {
        $clone = parent::withDisabled($is_disabled);

        if (null !== $clone->dynamic_input_template) {
            $clone->dynamic_input_template = $clone->dynamic_input_template->withDisabled($is_disabled);
        }

        foreach ($clone->dynamic_inputs as $key => $input) {
            $clone->dynamic_inputs[$key] = $input->withDisabled($is_disabled);
        }

        return $clone;
    }

    public function withNameFrom(NameSource $source) : self
    {
        $clone = parent::withNameFrom($source);
        $name_source = new DynamicInputsNameSource($clone->getName());

        if (null !== $clone->dynamic_input_template) {
            $clone->dynamic_input_template = $clone->dynamic_input_template->withNameFrom($name_source);
        }

        foreach ($clone->dynamic_inputs as $key => $input) {
            $clone->dynamic_inputs[$key] = $input->withNameFrom($name_source);
        }

        return $clone;
    }

    public function withInput(InputData $post_data) : self
    {
        if (null === $this->getName()) {
            throw new LogicException(static::class . '::withNameFrom must be called first.');
        }

        $clone = clone $this;
        $post_data = $post_data->getOr($clone->getName(), null);
        $template = $clone->getTemplateForDynamicInputs();

        if (null === $post_data || $template instanceof NullInput) {
            $clone->content = new Ok(null);
            return $clone;
        }

        $contents = [];
        $error = false;
        foreach ($post_data as $input_data) {

            // because the post data cannot be processed by other
            // inputs, due to the special array structure, the data
            // sort of gets built back together, so it can.
            $processable_data = [];
            foreach ($input_data as $key => $value) {
                $input_name = "{$clone->getName()}[" . DynamicInputsNameSource::INDEX_PLACEHOLDER . "][$key]";
                $processable_data[$input_name] = $value;
            }

            $template = $template->withInput(new ArrayInputData($processable_data));
            $content = $template->getContent();

            if ($content->isOk()) {
                $content = $content->value();
                // keeps the content mapped to the input name, if
                // e.g. a group or inputs with multiple values are
                // the provided template.
                if (is_array($content) && !empty($content)) {
                    foreach ($content as $key => $val) {
                        $contents[$key] = $val;
                    }
                } else {
                    $contents[] = $content;
                }
            } else {
                $error = true;
            }
        }

        if ($error) {
            $clone->content = $clone->data_factory->error("error" /*$this->language->txt("ui_error_in_group")*/);
        } else {
            $clone->content = $clone->applyOperationsTo($contents);
        }

        if ($clone->content->isError()) {
            $clone = $clone->withError((string) $clone->content->error());
        }

        return $clone;
    }

    // ==========================================
    // END OVERWRITTEN METHODS OF Input
    // ==========================================

    /**
     * @param mixed $value
     */
    protected function isDynamicInputsValueOk($value) : bool
    {
        if (!is_array($value)) {
            return $this->dynamic_input_template->isClientSideValueOk($value);
        }

        if (empty($value)) {
            return false;
        }

        foreach ($value as $input_value) {
            if (!$this->dynamic_input_template->isClientSideValueOk($input_value)) {
                return false;
            }
        }

        return true;
    }
}