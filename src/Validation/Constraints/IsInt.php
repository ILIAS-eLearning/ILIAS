<?php
/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Validation\Constraints;

use ILIAS\Validation\Constraint;
use ILIAS\Data;
use ILIAS\Data\Result;

class IsInt extends Custom implements Constraint
{
    public function __construct(Data\Factory $data_factory, \ilLanguage $lng)
    {
        parent::__construct(
            function ($value) {
                return is_int($value);
            },
            function ($txt, $value) {
                return $txt("not_an_int", $value);
            },
            $data_factory,
            $lng
        );
    }
}
