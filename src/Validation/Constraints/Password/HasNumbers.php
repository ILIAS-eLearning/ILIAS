<?php
/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Validation\Constraints\Password;

use ILIAS\Validation\Constraints;
use ILIAS\Validation\Constraint;
use ILIAS\Data;

class HasNumbers extends Constraints\Custom implements Constraint
{
    public function __construct(Data\Factory $data_factory, \ilLanguage $lng)
    {
        parent::__construct(
            function (Data\Password $value) {
                return (bool) preg_match('/[0-9]/', $value->toString());
            },
            function ($value) {
                return "Password must contain numbers.";
            },
            $data_factory,
            $lng
        );
    }
}
