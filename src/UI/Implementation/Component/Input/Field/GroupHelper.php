<?php

/* Copyright (c) 2017 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see
docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Implementation\Component\Input\PostData;
use ILIAS\UI\Implementation\Component\Input\NameSource;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Validation\Factory as ValidationFactory;
use ILIAS\Transformation\Factory as TransformationFactory;

/**
 * The code of Group is used in Checkbox, e.g., but a checkbox is not a group.
 * Thus, classes should rather share a trait.
 *
 * @author Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
trait GroupHelper
{
    /**
     * Inputs that are contained by this group
     *
     * @var    Input[]
     */
    protected $inputs = [];


    /**
     * @inheritdoc
     */
    protected function isClientSideValueOk($value)
    {
        return true;
    }


    /**
     * Get the value that is displayed in the groups input as Generator instance.
     *
     * @return array
     */
    public function getGroupValues()
    {
        $values = [];
        foreach ($this->getInputs() as $key => $input) {
            $values[$key] = $input->getValue();
        }

        return $values;
    }


    /**
     * Get an input like this with children with other values displayed on the
     * client side. Note that the number of values passed must match the number of inputs.
     *
     * @param    array
     *
     * @throws  \InvalidArgumentException    if value does not fit client side input
     * @return Input
     */
    public function withGroupValues($values)
    {
        $this->checkArg("value", $this->isClientSideValueOk($values), "Values given do not match given inputs in group.");

        $clone = clone $this;
        $inputs = [];

        foreach ($this->getInputs() as $key => $input) {
            $inputs[$key] = $input->withValue($values[$key]);
        }

        $clone->inputs = $inputs;

        return $clone;
    }


    /**
     * Default implementation for groups. May be overriden if more specific checks are needed.
     *
     * @param    mixed $value
     *
     * @return    bool
     */
    protected function isClientGroupSideValueOk($value)
    {
        if (!is_array($value)) {
            return false;
        }
        if (!sizeof($this->getInputs() == sizeof($value))) {
            return false;
        }

        foreach ($this->getInputs() as $key => $input) {
            if (!array_key_exists($key, $value)) {
                return false;
            }
        }

        return true;
    }


    /**
     * Collects the input, applies trafos and forwards the input to its children and returns
     * a new input group reflecting the inputs with data that was putted in.
     *
     * @inheritdoc
     */
    public function withInput(PostData $post_input)
    {
        $clone = parent::withInput($post_input);
        /**
         * @var $clone Group
         */
        if ($clone->getError()) {
            return $clone;
        }

        return $clone->withGroupInput($post_input);
    }


    /**
     * @param PostData $post_input
     *
     * @return Group|Input
     */
    protected function withGroupInput(PostData $post_input)
    {
        $clone = $this;

        if (sizeof($this->getInputs()) === 0) {
            return $clone;
        }

        $inputs = [];
        $values = [];
        $error = false;
        foreach ($this->getInputs() as $key => $input) {
            $filled = $input->withInput($post_input);
            /**
             * @var $filled Input
             */
            //Todo: Is this correct here or should it be getValue? Design decision...
            $content = $filled->getContent();
            if ($content->isOk()) {
                $values[$key] = $content->value();
            } else {
                $error = true;
            }
            $inputs[$key] = $filled;
        }
        $clone->inputs = $inputs;
        if ($error) {
            //Todo: Improve this error message on the group
            $clone->content = $clone->data_factory->error("error in grouped input");

            return $clone;
        }

        if ($clone->content->value()) {
            $group_content = $clone->applyOperationsTo($values);
            $f = $this->data_factory;
            $clone->content = $clone->content->then(function ($value) use ($f, $group_content) {
                if ($group_content->isError()) {
                    return $f->error($group_content->error());
                }

                return $f->ok(["value" => $value, "group_values" => $group_content->value()]);
            });
        } else {
            $clone->content = $clone->applyOperationsTo($values);
        }

        if ($clone->content->isError()) {
            return $clone->withError($clone->content->error());
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
}
