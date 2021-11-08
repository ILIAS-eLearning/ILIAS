<?php declare(strict_types=1);

/* Copyright (c) 2017 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Implementation\Component\Input\InputData;
use ILIAS\UI\Implementation\Component\Input\NameSource;
use InvalidArgumentException;

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
    protected array $inputs = [];

    /**
     * @param mixed $value
     */
    protected function isClientSideValueOk($value) : bool
    {
        return true;
    }

    /**
     * Get the value that is displayed in the groups input as Generator instance.
     */
    public function getGroupValues() : array
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
     * @throws InvalidArgumentException    if value does not fit client side input
     * @return static
     */
    public function withGroupValues(array $values)
    {
        $clone = clone $this;
        $clone->checkArg("value", $clone->isClientSideValueOk($values), "Values given do not match given inputs in group.");

        $inputs = [];

        foreach ($this->getInputs() as $key => $input) {
            $inputs[$key] = $input->withValue($values[$key]);
        }

        $clone->inputs = $inputs;

        return $clone;
    }

    /**
     * Default implementation for groups. May be overridden if more specific checks are needed.
     *
     * @param    mixed $value
     */
    protected function isClientGroupSideValueOk($value) : bool
    {
        if (!is_array($value)) {
            return false;
        }
        if (!($this->getInputs() == sizeof($value))) {
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
     * a new input group reflecting the inputs with data that was put in.
     */
    public function withInput(InputData $post_input) : self
    {
        $clone = clone $this;
        $clone = $clone::withInput($post_input);
        /**
         * @var $clone Group
         */
        if ($clone->getError()) {
            return $clone;
        }

        return $clone->withGroupInput($post_input);
    }

    protected function withGroupInput(InputData $post_input) : self
    {
        /**
         * @var $clone Group|Input|GroupHelper
         */
        $clone = clone $this;

        if (sizeof($this->getInputs()) === 0) {
            return $clone;
        }

        $inputs = [];
        $values = [];
        $error = false;
        foreach ($this->getInputs() as $key => $input) {
            $filled = $input->withInput($post_input);
            //Todo: Is this correct here or should it be getValue? Design decision...
            $content = $filled->getContent();
            if ($content->isOK()) {
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
            $f = $clone->data_factory;
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

    public function withNameFrom(NameSource $source) : self
    {
        $clone = clone $this;
        $clone = $clone::withNameFrom($source);

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
    public function getInputs() : array
    {
        return $this->inputs;
    }

    protected function getConstraintForRequirement()
    {
        return null;
    }
}
