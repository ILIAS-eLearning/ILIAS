<?php
/* Copyright (c) 2017 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see
docs/LICENSE */

namespace ILIAS\Refinery\Numeric;

use ILIAS\Refinery\Constraint;
use ILIAS\Refinery\Custom\Constraint as CustomConstraint;
use ILIAS\Data;

class IsNumeric extends CustomConstraint implements Constraint
{
    public function __construct(Data\Factory $data_factory, \ilLanguage $lng)
    {
        parent::__construct(
            function ($value) {
                return is_numeric($value);
            },
            function ($txt, $value) {
                return $txt("not_numeric", $value);
            },
            $data_factory,
            $lng
        );
    }
}
