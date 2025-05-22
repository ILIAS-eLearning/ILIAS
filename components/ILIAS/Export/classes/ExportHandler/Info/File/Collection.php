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

namespace ILIAS\Export\ExportHandler\Info\File;

use ILIAS\Export\ExportHandler\I\Info\File\CollectionInterface as ilExportHandlerFileInfoCollectionInterface;
use ILIAS\Export\ExportHandler\I\Info\File\HandlerInterface as ilExportHandlerFileInfoInterface;

class Collection implements ilExportHandlerFileInfoCollectionInterface
{
    protected array $elements;
    protected int $index;

    public function __construct()
    {
        $this->elements = [];
        $this->index = 0;
    }

    public function withFileInfo(ilExportHandlerFileInfoInterface $file_info): ilExportHandlerFileInfoCollectionInterface
    {
        $clone = clone $this;
        $clone->elements[] = $file_info;
        return $clone;
    }

    public function mergeWith(ilExportHandlerFileInfoCollectionInterface $other): ilExportHandlerFileInfoCollectionInterface
    {
        $clone = clone $this;
        $clone->elements = array_merge($this->elements, $other->elements);
        return $clone;
    }

    public function elementAt(int $index): ?ilExportHandlerFileInfoInterface
    {
        return $this->elements[$index] ?? null;
    }

    public function current(): ilExportHandlerFileInfoInterface
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
