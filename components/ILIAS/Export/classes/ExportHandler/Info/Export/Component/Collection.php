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

namespace ILIAS\Export\ExportHandler\Info\Export\Component;

use ILIAS\Export\ExportHandler\I\Info\Export\Component\CollectionInterface as ilExportHandlerExportComponentInfoCollectionInterface;
use ILIAS\Export\ExportHandler\I\Info\Export\Component\HandlerInterface as ilExportHandlerExportComponentInfoInterface;

class Collection implements ilExportHandlerExportComponentInfoCollectionInterface
{
    protected array $elements;
    protected int $index;

    public function __construct()
    {
        $this->elements = [];
        $this->index = 0;
    }

    public function withComponent(ilExportHandlerExportComponentInfoInterface $component): ilExportHandlerExportComponentInfoCollectionInterface
    {
        $clone = clone $this;
        $clone->elements[] = $component;
        return $clone;
    }

    public function mergedWith(ilExportHandlerExportComponentInfoCollectionInterface $other): ilExportHandlerExportComponentInfoCollectionInterface
    {
        $clone = clone $this;
        $clone->elements = array_merge($this->elements, $other->elements);
        return $clone;
    }

    public function pop(): ilExportHandlerExportComponentInfoInterface
    {
        return array_pop($this->elements);
    }

    public function current(): ilExportHandlerExportComponentInfoInterface
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
