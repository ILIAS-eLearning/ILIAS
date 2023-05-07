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
 * This implements the optional group.
 */
class OptionalGroup extends Group implements Field\OptionalGroup
{
    use JavaScriptBindable;
    use Triggerer;

    /**
     * @var	bool
     */
    protected $null_value_was_explicitly_set = false;

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
        if ($value === null) {
            return true;
        }
        return parent::isClientSideValueOk($value);
    }

    public function withRequired($is_required)
    {
        return Input::withRequired($is_required);
    }

    public function isRequired(): bool
    {
        return $this->is_required;
    }

    /**
     * @inheritdoc
     * @return OptionalGroup
     */
    public function withValue($value)
    {
        if ($value === null) {
            $clone = clone $this;
            $clone->value = $value;
            $clone->null_value_was_explicitly_set = true;
            return $clone;
        }
        /**
         * @var $clone OptionalGroup
         */
        $clone = parent::withValue($value);
        $clone->null_value_was_explicitly_set = false;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getValue()
    {
        if ($this->null_value_was_explicitly_set) {
            return null;
        }
        return parent::getValue();
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
            $value = $post_input->getOr($this->getName(), null);
            if ($value === null) {
                $clone = $this->withValue(null);
                // Ugly hack to prevent shortcutting behaviour of applyOperationsTo
                $temp = $clone->is_required;
                $clone->is_required = true;
                $clone->content = $clone->applyOperationsTo(null);
                $clone->is_required = $temp;
                return $clone;
            }
        }
        return parent::withInput($post_input);
    }
}
