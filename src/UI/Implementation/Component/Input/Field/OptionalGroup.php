<?php declare(strict_types=1);

/* Copyright (c) 2017 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\Refinery\Constraint;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Triggerer;
use ILIAS\UI\Implementation\Component\Input\InputData;
use ILIAS\UI\Component\Input\Field;
use LogicException;

/**
 * This implements the optional group.
 */
class OptionalGroup extends Group implements Field\OptionalGroup
{
    use JavaScriptBindable;
    use Triggerer;

    protected bool $null_value_was_explicitly_set = false;

    /**
     * @inheritdoc
     */
    protected function getConstraintForRequirement() : ?Constraint
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

    public function withRequired($is_required) : Field\Input
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Input::withRequired($is_required);
    }

    /**
     * @inheritdoc
     */
    public function withValue($value) : Field\Input
    {
        if ($value === null) {
            $clone = clone $this;
            $clone->value = $value;
            $clone->null_value_was_explicitly_set = true;
            return $clone;
        }

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
    public function withInput(InputData $input) : Field\Input
    {
        if ($this->getName() === null) {
            throw new LogicException("Can only collect if input has a name.");
        }

        if (!$this->isDisabled()) {
            $value = $input->getOr($this->getName(), null);
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
        return parent::withInput($input);
    }
}
