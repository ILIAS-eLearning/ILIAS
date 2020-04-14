<?php declare(strict_types=1);

/* Copyright (c) 2020 Nils Haagen <nhaagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Table\Column;

use ILIAS\UI\Component\Table\Column as I;

class Number extends Column implements I\Number
{
    const TYPE = 'Number';

    public function withDecimals(int $number_of_decimals) : I\Number
    {
        return $this;
    }

    public function getDecimals() : int
    {
        return 2;
    }
}
