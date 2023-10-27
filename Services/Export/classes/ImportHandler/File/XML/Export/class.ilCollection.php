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

namespace ImportHandler\File\XML\Export;

use ImportHandler\I\File\XML\Export\ilCollectionInterface as ilXMLExportFileCollectionInterface;
use ImportHandler\I\File\XML\Export\ilHandlerInterface as ilXMLExportFileHandlerInterface;

class ilCollection implements ilXMLExportFileCollectionInterface
{
    /**
     * @var ilXMLExportFileHandlerInterface[]
     */
    protected array $elements;
    protected int $index;

    public function __construct()
    {
        $this->elements = [];
        $this->index = 0;
    }

    public function withElement(ilXMLExportFileHandlerInterface $element): ilXMLExportFileCollectionInterface
    {
        $clone = clone $this;
        $clone->elements[] = $element;
        return $clone;
    }

    public function withMerged(ilXMLExportFileCollectionInterface $other): ilXMLExportFileCollectionInterface
    {
        $clone = clone $this;
        $clone->elements = array_merge($this->toArray(), $other->toArray());
        return $clone;
    }

    public function count(): int
    {
        return count($this->elements);
    }

    public function toArray(): array
    {
        return $this->elements;
    }

    public function current(): ilXMLExportFileHandlerInterface
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
