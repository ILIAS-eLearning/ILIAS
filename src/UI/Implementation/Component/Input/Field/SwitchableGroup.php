<?php

/* Copyright (c) 2017 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see
docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\Component as C;
use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\Constraint;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Triggerer;
use ILIAS\UI\Implementation\Component\Input\InputData;
use ILIAS\UI\Component\Input\Field;

/**
 * This implements the switchable group.
 */
class SwitchableGroup extends Group implements Field\SwitchableGroup
{
    use JavaScriptBindable;
    use Triggerer;

    /**
     * Only adds a check to the original group-constructor.
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
        $this->checkArgListElements("inputs", $inputs, Group::class);
        parent::__construct($data_factory, $refinery, $lng, $inputs, $label, $byline);
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
    protected function isClientSideValueOk($value) : bool
    {
        if (!is_string($value) && !is_int($value)) {
            return false;
        }
        return array_key_exists($value, $this->inputs);
    }

    public function withRequired($is_required)
    {
        return Input::withRequired($is_required);
    }

    /**
     * @inheritdoc
     */
    public function withValue($value)
    {
        if (is_string($value) || is_int($value)) {
            return Input::withValue($value);
        }
        if (!is_array($value) || count($value) !== 2) {
            throw new \InvalidArgumentException(
                "Expected one key and a group value or one key only as value."
            );
        }
        list($key, $group_value) = $value;
        $clone = Input::withValue($key);
        $clone->inputs[$key] = $clone->inputs[$key]->withValue($group_value);
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getValue()
    {
        $key = Input::getValue();
        if (is_null($key)) {
            return null;
        }
        return [$key, $this->inputs[$key]->getValue()];
    }


    /**
     * @inheritdoc
     */
    public function withInput(InputData $post_input)
    {
        if ($this->getName() === null) {
            throw new \LogicException("Can only collect if input has a name.");
        }

        if (!$this->isDisabled()) {
            $key = $post_input->get($this->getName());
            $clone = $this->withValue($key);
            $clone->inputs[$key] = $clone->inputs[$key]->withInput($post_input);
        } else {
            $clone = $this;
        }

        if ($clone->inputs[$key]->getContent()->isError()) {
            $clone->content = $clone->data_factory->error($this->lng->txt("ui_error_in_group"));
        } else {
            $clone->content = $this->applyOperationsTo($clone->getValue());
            if ($clone->content->isError()) {
                return $clone->withError("" . $clone->content->error());
            }
        }

        return $clone;
    }
}
