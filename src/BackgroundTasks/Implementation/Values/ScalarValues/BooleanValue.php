<?php

namespace ILIAS\BackgroundTasks\Implementation\Values\ScalarValues;

class BooleanValue extends ScalarValue
{

    public function setValue($boolean)
    {
        if (!is_bool($boolean)) {
            throw new InvalidArgumentException("The value given must be a boolean! See php-documentation is_bool().");
        }
        $this->value = $boolean;
    }
}