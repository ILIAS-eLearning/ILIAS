<?php declare(strict_types=1);

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\Password;

use ILIAS\Refinery\Custom\Constraint as CustomConstraint;
use ILIAS\Data;
use ilLanguage;

class HasUpperChars extends CustomConstraint
{
    public function __construct(Data\Factory $data_factory, ilLanguage $lng)
    {
        parent::__construct(
            static function (Data\Password $value) : bool {
                return (bool) preg_match('/[A-Z]/', $value->toString());
            },
            static function ($value) : string {
                return "Password must contain upper-case characters.";
            },
            $data_factory,
            $lng
        );
    }
}
