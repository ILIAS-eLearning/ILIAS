<?php
/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Validation\Constraints;

use ILIAS\Validation\Constraint;
use ILIAS\Data;
use ILIAS\Data\Result;

class HasMinLength extends Custom implements Constraint
{
    /**
     * @var int
     */
    protected $min_length;

    public function __construct(int $min_length, Data\Factory $data_factory, \ilLanguage $lng)
    {
        $this->min_length = $min_length;
        parent::__construct(
            function ($value) {
                return strlen($value) >= $this->min_length;
            },
            function ($txt, $value) {
                $len = strlen($value);
                return $txt("not_min_length", $len, $this->min_length);
            },
            $data_factory,
            $lng
        );
    }
}
