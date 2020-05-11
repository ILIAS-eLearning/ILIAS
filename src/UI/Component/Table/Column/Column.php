<?php declare(strict_types=1);

/* Copyright (c) 2020 Nils Haagen <nhaagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Table\Column;

use ILIAS\Refinery\Transformation;

/**
 * A Column describes the form of presentation for a certain aspect of data,
 * i.e. a field of a record within a table.
 */
interface Column
{
    public function getTitle() : string;
    public function getType() : string;

    public function withIsSortable(bool $flag) : Column;
    public function isSortable() : bool;

    public function withIsOptional(bool $flag) : Column;
    public function isOptional() : bool;

    public function withIsInitiallyVisible(bool $flag) : Column;
    public function isInitiallyVisible() : bool;

    public function withIndex(int $index) : Column;
    public function getIndex() : int;
}
