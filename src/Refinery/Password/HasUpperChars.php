<?php declare(strict_types=1);

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\Password;

use ILIAS\Refinery\Custom\Constraint as CustomConstraint;
use ILIAS\Refinery\Constraint;
use ILIAS\Data;

class HasUpperChars extends CustomConstraint
{
    public function __construct(Data\Factory $data_factory, \ilLanguage $lng)
    {
        parent::__construct(
            function (Data\Password $value) {
                return (bool) preg_match('/[A-Z]/', $value->toString());
            },
            function ($value) {
                return "Password must contain upper-case characters.";
            },
            $data_factory,
            $lng
        );
    }
}
