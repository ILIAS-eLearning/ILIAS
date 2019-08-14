<?php
/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\Integer;

use ILIAS\Refinery\Constraint;
use ILIAS\Data;
use ILIAS\Refinery\Custom\Constraint as CustomConstraint;

class GreaterThan extends CustomConstraint implements Constraint
{
    /**
     * @var int
     */
    protected $min;

    public function __construct(int $min, Data\Factory $data_factory, \ilLanguage $lng)
    {
        $this->min = $min;
        parent::__construct(
            function ($value) {
                return $value > $this->min;
            },
            function ($txt, $value) {
                return $txt("not_greater_than", $value, $this->min);
            },
            $data_factory,
            $lng
        );
    }
}
