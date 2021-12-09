<?php declare(strict_types=1);

/* Copyright (c) 2021 Luka Stocker <luka.stocker@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\Integer;

use ILIAS\Refinery\Constraint;
use ILIAS\Data;
use ILIAS\Refinery\Custom\Constraint as CustomConstraint;

class GreaterThanOrEqual extends CustomConstraint implements Constraint
{
    protected int $min;

    public function __construct(int $min, Data\Factory $data_factory, \ilLanguage $lng)
    {
        $this->min = $min;
        parent::__construct(
            function ($value) {
                return $value >= $this->min;
            },
            function ($txt, $value) {
                return $txt("not_greater_than_or_equal", $this->min);
            },
            $data_factory,
            $lng
        );
    }
}
