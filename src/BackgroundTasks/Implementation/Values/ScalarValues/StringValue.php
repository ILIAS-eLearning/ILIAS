<?php

namespace ILIAS\BackgroundTasks\Implementation\Values\ScalarValues;

use ILIAS\BackgroundTasks\Exceptions\InvalidArgumentException;

class StringValue extends ScalarValue
{
    public function setValue($string)
    {
        if (!is_string($string)) {
            throw new InvalidArgumentException("The value given must be a string! See php-documentation is_string().");
        }
        parent::setValue($string);
    }
}
