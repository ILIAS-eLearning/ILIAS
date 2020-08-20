<?php declare(strict_types=1);

/* Copyright (c) 2020 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Table;

use ILIAS\UI\Component\Table as T;

class StandardRow extends Row
{
    /**
     * @var bool
     */
    protected $selectable;

    public function isSelectable() : bool
    {
        return $this->selectable;
    }

    public function withIsSelectable(bool $selectable) : StandardRow
    {
        $clone = clone $this;
        $clone->selectable = $selectable;
        return $clone;
    }
}
