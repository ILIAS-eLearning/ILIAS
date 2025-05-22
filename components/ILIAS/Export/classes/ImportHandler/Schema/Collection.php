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

namespace ILIAS\Export\ImportHandler\Schema;

use ILIAS\Export\ImportHandler\I\Schema\CollectionInterface as SchemaCollectionInterface;
use ILIAS\Export\ImportHandler\I\Schema\HandlerInterface as SchemaInterface;

class Collection implements SchemaCollectionInterface
{
    /**
     * @var SchemaInterface[]
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

    public function withElement(
        SchemaInterface $element
    ): SchemaCollectionInterface {
        $clone = clone $this;
        $clone->elements[] = $element;
        return $clone;
    }

    public function withMerged(
        SchemaCollectionInterface $other
    ): SchemaCollectionInterface {
        $clone = clone $this;
        $clone->elements = array_merge($clone->elements, $other->toArray());
        return $clone;
    }

    public function toArray(): array
    {
        return $this->elements;
    }

    public function current(): SchemaInterface
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
        return isset($this->elements[$this->index]);
    }

    public function rewind(): void
    {
        $this->index = 0;
    }
}
