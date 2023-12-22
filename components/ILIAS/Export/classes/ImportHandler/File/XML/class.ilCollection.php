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

namespace ILIAS\Export\ImportHandler\File\XML;

use ILIAS\Export\ImportHandler\I\File\XML\ilCollectionInterface as ilXMLFileHandlerCollectionInterface;
use ILIAS\Export\ImportHandler\I\File\XML\ilHandlerInterface as ilXMLFileHandlerInterface;

class ilCollection implements ilXMLFileHandlerCollectionInterface
{
    /**
     * @var ilXMLFileHandlerInterface[];
     */
    protected array $elements;
    protected int $index;

    /**
     * @param ilXMLFileHandlerInterface[] $initial_elements
     */
    public function __construct(array $initial_elements = [])
    {
        $this->elements = $initial_elements;
        $this->index = 0;
    }

    public function count(): int
    {
        return count($this->elements);
    }

    public function current(): ilXMLFileHandlerInterface
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

    public function withMerged(ilXMLFileHandlerCollectionInterface $other): ilXMLFileHandlerCollectionInterface
    {
        $clone = clone $this;
        $clone->elements = array_merge($clone->toArray(), $other->toArray());
        return $clone;
    }

    public function withElement(ilXMLFileHandlerInterface $element): ilXMLFileHandlerCollectionInterface
    {
        $clone = clone $this;
        $clone->elements[] = $element;
        return $clone;
    }

    /**
     * @return ilXMLFileHandlerInterface[]
     */
    public function toArray(): array
    {
        return $this->elements;
    }
}
