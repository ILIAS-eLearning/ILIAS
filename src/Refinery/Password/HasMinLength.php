<?php
/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\Password;

use ILIAS\Refinery\Custom\Constraint as CustomConstraint;
use ILIAS\Refinery\Constraint;
use ILIAS\Data;

class HasMinLength extends CustomConstraint implements Constraint
{
    /**
     * @var int
     */
    protected $min_length;

    public function __construct(int $min_length, Data\Factory $data_factory, \ilLanguage $lng)
    {
        $this->min_length = $min_length;
        parent::__construct(
            function (Data\Password $value) {
                return strlen($value->toString()) >= $this->min_length;
            },
            function ($value) {
                return "Password has a length less than '{$this->min_length}'.";
            },
            $data_factory,
            $lng
        );
    }
}
