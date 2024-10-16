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

namespace ILIAS\Export\ExportHandler\Table\RowId;

use ILIAS\Export\ExportHandler\I\Table\RowId\CollectionInterface as ilExportHandlerTableRowIdCollectionInterface;
use ILIAS\Export\ExportHandler\I\Table\RowId\HandlerInterface as ilExportHandlerTableRowIdInterface;

class Collection implements ilExportHandlerTableRowIdCollectionInterface
{
    /** @var ilExportHandlerTableRowIdInterface[] */
    protected array $elements;
    protected int $index;

    public function __construct()
    {
        $this->elements = [];
        $this->index = 0;
    }

    public function withElement(ilExportHandlerTableRowIdInterface $row_id): ilExportHandlerTableRowIdCollectionInterface
    {
        $clone = clone $this;
        $clone->elements[] = $row_id;
        return $clone;
    }

    public function fileIdentifiers(): array
    {
        return array_map(function (ilExportHandlerTableRowIdInterface $row_id) { return $row_id->getFileIdentifier(); }, $this->elements);
    }

    public function count(): int
    {
        return count($this->elements);
    }

    public function current(): ilExportHandlerTableRowIdInterface
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
}
