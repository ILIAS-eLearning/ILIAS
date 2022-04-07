<?php declare(strict_types=1);

/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\Integer;

use ILIAS\Data;
use ILIAS\Refinery\Custom\Constraint as CustomConstraint;
use ilLanguage;

class GreaterThan extends CustomConstraint
{
    protected int $min;

    public function __construct(int $min, Data\Factory $data_factory, ilLanguage $lng)
    {
        $this->min = $min;
        parent::__construct(
            function ($value) : bool {
                return $value > $this->min;
            },
            function ($txt, $value) : string {
                return (string) $txt("not_greater_than", $value, $this->min);
            },
            $data_factory,
            $lng
        );
    }
}
