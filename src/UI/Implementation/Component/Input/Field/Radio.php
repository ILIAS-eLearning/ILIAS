<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\Input\PostData;
use ILIAS\UI\Implementation\Component\Input\NameSource;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Triggerer;

/**
 * This implements the radio input.
 */
class Radio extends Input implements C\Input\Field\Radio, C\JavaScriptBindable
{
    use JavaScriptBindable;
    use Triggerer;

    const DEPENDANT_FIELD_ERROR = 'ilradio_dependant_field_error';

    /**
     * @var array <string,string> {$value => $label}
     */
    protected $options = [];

    /**
     * @var array <string,array> {$option_value => $bylines}
     */
    protected $bylines = [];

    /**
     * @var array <string,array> {$option_value => $fields}
     */
    protected $dependant_fields = [];

    /**
     * @inheritdoc
     */
    protected function isClientSideValueOk($value)
    {
        return ($value === '' || array_key_exists($value, $this->getOptions()));
    }

    /**
     * @inheritdoc
     */
    protected function getConstraintForRequirement()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function withNameFrom(NameSource $source)
    {
        $clone = parent::withNameFrom($source);

        foreach ($clone->dependant_fields as $option_value => $fields) {
            $named_inputs = [];
            foreach ($fields as $key => $input) {
                $named_inputs[$key] = $input->withNameFrom($source);
            }
            $clone->dependant_fields[$option_value] = $named_inputs;
        }
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function withOption(string $value, string $label, string $byline=null, $dependant_fields=null) : C\Input\Field\Radio
    {
        $clone = clone $this;
        $clone->options[$value] = $label;
        if (!is_null($byline)) {
            $clone->bylines[$value] = $byline;
        }
        if (!is_null($dependant_fields)) {
            $clone->dependant_fields[$value] = $dependant_fields;
        }
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getOptions() : array
    {
        return $this->options;
    }


    public function getBylineFor(string $value)
    {
        if (!array_key_exists($value, $this->bylines)) {
            return null;
        }
        return $this->bylines[$value];
    }


    /**
     * @inheritdoc
     */
    public function getDependantFieldsFor(string $value)
    {
        if (!array_key_exists($value, $this->dependant_fields)) {
            return null;
        }
        return $this->dependant_fields[$value];
    }

    /**
     * @inheritdoc
     */
    public function withInput(PostData $post_input)
    {
        if ($this->getName() === null) {
            throw new \LogicException("Can only collect if input has a name.");
        }
        $value = $post_input->getOr($this->getName(), "");

        $clone = $this->withValue($value);

        $clone->content = $this->applyOperationsTo($value);
        if ($clone->content->isError()) {
            return $clone->withError("" . $clone->content->error());
        }

        $dep_fields = $this->getDependantFieldsFor($value);
        if (is_null($dep_fields)) {
            $clone->content = $this->applyOperationsTo($value);
        } else {
            $values = [
                'value' => $value,
                'group_values' => []
            ];

            foreach ($dep_fields as $name => $field) {
                $filled = $field->withInput($post_input);
                $content = $filled->getContent();

                if ($content->isOk()) {
                    $values['group_values'][$name] = $content->value();
                } else {
                    $clone = $clone->withError(self::DEPENDANT_FIELD_ERROR);
                }

                $clone->dependant_fields[$value][$name] = $filled;
            }
            $clone->content = $clone->applyOperationsTo($values);
        }

        if ($clone->getError()) {
            $clone->content = $clone->data_factory->error($clone->getError());
        }

        return $clone;
    }
}
