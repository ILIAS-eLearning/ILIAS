<?php

namespace ILIAS\BackgroundTasks\Implementation\Values\ScalarValues;

use ILIAS\BackgroundTasks\Value;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
class BooleanValue extends ScalarValue
{
    public function setValue($value) : void
    {
        if (!is_bool($value)) {
            throw new \InvalidArgumentException("The value given must be a boolean! See php-documentation is_bool().");
        }
        $this->value = $value;
    }
}
