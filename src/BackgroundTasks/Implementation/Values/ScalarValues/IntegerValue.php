<?php

namespace ILIAS\BackgroundTasks\Implementation\Values\ScalarValues;

use ILIAS\BackgroundTasks\Exceptions\InvalidArgumentException;

class IntegerValue extends ScalarValue
{

    public function setValue($integer)
    {
        if (!is_integer($integer)) {
            throw new InvalidArgumentException("The value given must be an integer! See php-documentation is_integer().");
        }

        parent::setValue($integer);
    }
}