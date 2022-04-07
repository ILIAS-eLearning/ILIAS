<?php declare(strict_types=1);

/* Copyright (c) 2017 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\Numeric;

use ILIAS\Refinery\Custom\Constraint as CustomConstraint;
use ILIAS\Data;
use ilLanguage;

class IsNumeric extends CustomConstraint
{
    public function __construct(Data\Factory $data_factory, ilLanguage $lng)
    {
        parent::__construct(
            function ($value) {
                return is_numeric($value);
            },
            function ($txt, $value) {
                if ('' === $value) {
                    return $txt("not_numeric_empty_string");
                }

                return $txt("not_numeric", $value);
            },
            $data_factory,
            $lng
        );
    }
}
