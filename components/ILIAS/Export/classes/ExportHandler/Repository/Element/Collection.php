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

namespace ILIAS\Export\ExportHandler\Repository\Element;

use ILIAS\Export\ExportHandler\I\Repository\Element\CollectionInterface as ilExportHandlerRepositoryElementCollectionInterface;
use ILIAS\Export\ExportHandler\I\Repository\Element\HandlerInterface as ilExportHandlerRepositoryElementInterface;

class Collection implements ilExportHandlerRepositoryElementCollectionInterface
{
    protected array $elements;
    protected int $index;

    public function __construct()
    {
        $this->elements = [];
        $this->index = 0;
    }

    public function withElement(ilExportHandlerRepositoryElementInterface $element): ilExportHandlerRepositoryElementCollectionInterface
    {
        $clone = clone $this;
        $clone->elements[] = $element;
        return $clone;
    }

    public function newest(): ?ilExportHandlerRepositoryElementInterface
    {
        $tmp_elements = $this->elements;
        usort($tmp_elements, function (
            ilExportHandlerRepositoryElementInterface $a,
            ilExportHandlerRepositoryElementInterface $b
        ) {
            if ($a->getValues()->getCreationDate() === $b->getValues()->getCreationDate()) {
                return 0;
            }
            return $a->getValues()->getCreationDate() < $b->getValues()->getCreationDate() ? 1 : -1;
        });
        return $tmp_elements[0] ?? null;
    }

    public function current(): ilExportHandlerRepositoryElementInterface
    {
        return $this->elements[$this->index];
    }

    public function key(): int
    {
        return $this->index;
    }

    public function next(): void
    {
        $this->index++;
    }

    public function rewind(): void
    {
        $this->index = 0;
    }

    public function valid(): bool
    {
        return isset($this->elements[$this->index]);
    }

    public function count(): int
    {
        return count($this->elements);
    }
}
