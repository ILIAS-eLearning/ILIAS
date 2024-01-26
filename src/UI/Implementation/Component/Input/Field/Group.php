<?php

/* Copyright (c) 2017 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see
docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\Data\Result;
use ILIAS\UI\Component as C;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\Input\InputData;
use ILIAS\UI\Implementation\Component\Input\NameSource;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\Data\Factory as DataFactory;

/**
 * This implements the group input.
 */
class Group extends Input implements C\Input\Field\Group
{
    use ComponentHelper;

    /**
     * Inputs that are contained by this group
     *
     * @var    Input[]
     */
    protected $inputs = [];

    /**
     * @var	\ilLanguage
     */
    protected $lng;

    /**
     * Group constructor.
     *
     * @param DataFactory             $data_factory
     * @param \ILIAS\Refinery\Factory $refinery
     * @param \ilLanguage             $lng
     * @param InputInternal[]         $inputs
     * @param                         $label
     * @param                         $byline
     */
    public function __construct(
        DataFactory $data_factory,
        \ILIAS\Refinery\Factory $refinery,
        \ilLanguage $lng,
        array $inputs,
        string $label,
        string $byline = null
    ) {
        parent::__construct($data_factory, $refinery, $label, $byline);
        $this->checkArgListElements("inputs", $inputs, InputInternal::class);
        $this->inputs = $inputs;
        $this->lng = $lng;
    }

    public function withDisabled($is_disabled)
    {
        $clone = parent::withDisabled($is_disabled);
        $clone->inputs = array_map(function ($i) use ($is_disabled) {
            return $i->withDisabled($is_disabled);
        }, $this->inputs);
        return $clone;
    }

    public function withRequired($is_required)
    {
        $clone = parent::withRequired($is_required);
        $inputs = [];
        $clone->inputs = array_map(function ($i) use ($is_required) {
            return $i->withRequired($is_required);
        }, $this->inputs);
        return $clone;
    }

    public function isRequired()
    {
        if($this->is_required) {
            return true;
        }
        foreach ($this->getInputs() as $input) {
            if ($input->isRequired()) {
                return true;
            }
        }
        return false;
    }

    public function withOnUpdate(Signal $signal)
    {
        $clone = parent::withOnUpdate($signal);
        $clone->inputs = array_map(function ($i) use ($signal) {
            return $i->withOnUpdate($signal);
        }, $this->inputs);
        return $clone;
    }

    /**
     * @inheritdoc
     */
    protected function isClientSideValueOk($value) : bool
    {
        if (!is_array($value)) {
            return false;
        }
        if (count($this->getInputs()) !== count($value)) {
            return false;
        }
        foreach ($this->getInputs() as $key => $input) {
            if (!array_key_exists($key, $value)) {
                return false;
            }
            if (!$input->isClientSideValueOk($value[$key])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get the value that is displayed in the input client side.
     *
     * @return    mixed
     */
    public function getValue()
    {
        return array_map(function ($i) {
            return $i->getValue();
        }, $this->inputs);
    }


    /**
     * Get an input like this with another value displayed on the
     * client side.
     *
     * @param    mixed
     *
     * @throws  \InvalidArgumentException    if value does not fit client side input
     * @return Input
     */
    public function withValue($value)
    {
        $this->checkArg("value", $this->isClientSideValueOk($value), "Display value does not match input type.");
        $clone = clone $this;
        foreach ($this->inputs as $k => $i) {
            $clone->inputs[$k] = $i->withValue($value[$k]);
        }
        return $clone;
    }

    /**
     * Collects the input, applies trafos and forwards the input to its children and returns
     * a new input group reflecting the inputs with data that was putted in.
     *
     * @inheritdoc
     */
    public function withInput(InputData $post_input)
    {
        if (sizeof($this->getInputs()) === 0) {
            return $this;
        }

        /**
         * @var $clone Group
         */
        $clone = clone $this;

        $inputs = [];
        $contents = [];
        $error = false;

        foreach ($this->getInputs() as $key => $input) {
            $inputs[$key] = $input->withInput($post_input);
            $content = $inputs[$key]->getContent();
            if ($content->isError()) {
                $error = true;
            } else {
                $contents[$key] = $content->value();
            }
        }

        $clone->inputs = $inputs;
        if ($error) {
            $clone->content = $clone->data_factory->error($this->lng->txt("ui_error_in_group"));
        } else {
            $clone->content = $clone->applyOperationsTo($contents);
        }

        if ($clone->content->isError()) {
            $clone = $clone->withError("" . $clone->content->error());
        }

        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function withNameFrom(NameSource $source)
    {
        $clone = parent::withNameFrom($source);
        /**
         * @var $clone Group
         */
        $named_inputs = [];
        foreach ($this->getInputs() as $key => $input) {
            $named_inputs[$key] = $input->withNameFrom($source);
        }

        $clone->inputs = $named_inputs;

        return $clone;
    }

    /**
     * @return Input[]
     */
    public function getInputs()
    {
        return $this->inputs;
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
    public function getUpdateOnLoadCode() : \Closure
    {
        return function () {
            /*
             * Currently, there is no use case for Group here. The single Inputs
             * within the Group are responsible for handling getUpdateOnLoadCode().
             */
        };
    }

    /**
     * @inheritdoc
     */
    public function getContent()
    {
        if (0 === count($this->getInputs())) {
            return new \ILIAS\Data\Result\Ok([]);
        }
        return parent::getContent();
    }
}
