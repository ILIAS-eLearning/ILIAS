<?php declare(strict_types=1);

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\Password;

use ILIAS\Refinery\Custom\Constraint as CustomConstraint;
use ILIAS\Data;
use ilLanguage;

class HasSpecialChars extends CustomConstraint
{
    protected static string $ALLOWED_CHARS = '/[,_.\-#\+\*?!%§\(\)\$]/u';

    public function __construct(Data\Factory $data_factory, ilLanguage $lng)
    {
        parent::__construct(
            function (Data\Password $value) {
                return (bool) preg_match(static::$ALLOWED_CHARS, $value->toString());
            },
            function ($value) {
                return "Password must contain special chars.";
            },
            $data_factory,
            $lng
        );
    }
}
