<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Search\Result\Filter;

class FilteredResultImpl implements FilteredResult
{
    /**
     * @var int[]
     */
    protected array $result;
    protected int $pointer = 0;

    /**
     * @param int[] $result
     */
    public function __construct(
        protected bool $is_complete,
        array $result
    ) {
        $this->result = array_values($result);
    }

    public function current(): int
    {
        return $this->result[$this->pointer];
    }

    public function next(): void
    {
        $this->pointer++;
    }

    public function key(): int
    {
        return $this->pointer;
    }

    public function valid(): bool
    {
        return isset($this->result[$this->pointer]);
    }

    public function rewind(): void
    {
        $this->pointer = 0;
    }

    public function isResultComplete(): bool
    {
        return $this->is_complete;
    }
}
