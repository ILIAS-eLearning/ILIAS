<?php declare(strict_types=1);

/* Copyright (c) 2021 Luka Stocker <luka.stocker@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\Integer;

use ILIAS\Refinery\Constraint;
use ILIAS\Data;
use ILIAS\Refinery\Custom\Constraint as CustomConstraint;

class LessThanOrEqual extends CustomConstraint implements Constraint
{
    protected int $max;

    public function __construct(int $max, Data\Factory $data_factory, \ilLanguage $lng)
    {
        $this->max = $max;
        parent::__construct(
            function ($value) : bool {
                return $value <= $this->max;
            },
            function ($txt, $value) : string {
                return (string) $txt("not_less_than_or_equal", $this->max);
            },
            $data_factory,
            $lng
        );
    }
}
