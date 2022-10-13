<?php

namespace ILIAS\UI\Implementation\Component\Input\Field;

use Closure;
use ILIAS\Refinery\Constraint;

/**
 * Class ColorPicker
 *
 */
class ColorPicker extends Input implements \ILIAS\UI\Component\Input\Field\ColorPicker
{
    /**
     * Get update code
     *
     * This method has to return JS code that calls
     * il.UI.filter.onFieldUpdate(event, '$id', string_value);
     * - initially "onload" and
     * - on every input change.
     * It must pass a readable string representation of its value in parameter 'string_value'.
     */
    public function getUpdateOnLoadCode(): Closure
    {
        // TODO: Implement getUpdateOnLoadCode() method.
        throw new \ILIAS\UI\NotImplementedException();
    }

    /**
     * This may return a constraint that will be checked first if the field is
     * required.
     */
    protected function getConstraintForRequirement(): ?Constraint
    {
        // TODO: Implement getConstraintForRequirement() method.
        throw new \ILIAS\UI\NotImplementedException();
    }

    /**
     * Check if the value is good to be displayed client side.
     *
     * @param mixed $value
     */
    protected function isClientSideValueOk($value): bool
    {
        // TODO: Implement isClientSideValueOk() method.
        throw new \ILIAS\UI\NotImplementedException();
    }
}
