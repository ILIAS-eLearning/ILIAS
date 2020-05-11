<?php declare(strict_types=1);

/* Copyright (c) 2020 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Table\Column;

use ILIAS\UI\Component\Table\Column as C;

class Number extends Column implements C\Number
{
    /**
     * @var int
     */
    protected $decimals = 0;

    public function getType() : string
    {
        return self::COLUMN_TYPE_NUMBER;
    }

    public function withDecimals(int $number_of_decimals) : C\Number
    {
        $clone = clone $this;
        $clone->decimals = $number_of_decimals;
        return $clone;
    }
    public function getDecimals() : int
    {
        return $this->decimals;
    }
}