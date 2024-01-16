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

namespace ILIAS\Export\ImportHandler\File\Validation\Set;

use ILIAS\Export\ImportHandler\I\File\Validation\Set\ilCollectionInterface as ilFileValidationSetCollectionInterface;
use ILIAS\Export\ImportHandler\I\File\Validation\Set\ilHandlerInterface as ilFileValidationPairHandlerInterface;

class ilCollection implements ilFileValidationSetCollectionInterface
{
    /**
     * @var ilFileValidationPairHandlerInterface[]
     */
    protected array $elements;
    protected int $index;

    public function __construct()
    {
        $this->elements = [];
        $this->index = 0;
    }

    public function count(): int
    {
        return count($this->elements);
    }

    public function withElement(ilFileValidationPairHandlerInterface $element): ilFileValidationSetCollectionInterface
    {
        $clone = clone $this;
        $clone->elements[] = $element;
        return $clone;
    }

    public function withMerged(ilFileValidationSetCollectionInterface $other): ilFileValidationSetCollectionInterface
    {
        $clone = clone $this;
        $clone->elements = array_merge($this->toArray(), $other->toArray());
        return $clone;
    }

    public function toArray(): array
    {
        return $this->elements;
    }

    public function current(): ilFileValidationPairHandlerInterface
    {
        return $this->elements[$this->index];
    }

    public function next(): void
    {
        $this->index++;
    }

    public function key(): int
    {
        return $this->index;
    }

    public function valid(): bool
    {
        return 0 <= $this->index && $this->index < $this->count();
    }

    public function rewind(): void
    {
        $this->index = 0;
    }
}
