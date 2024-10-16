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

namespace ILIAS\Export\ExportHandler\Consumer\ExportOption;

use ILIAS\Export\ExportHandler\I\Consumer\ExportOption\CollectionInterface as ilExportHandlerConsumerExportOptionCollectionInterface;
use ILIAS\Export\ExportHandler\I\Consumer\ExportOption\HandlerInterface as ilExportHandlerConsumerExportOptionInterface;
use ILIAS\Export\ExportHandler\I\FactoryInterface as ilExportHandlerFactoryInterface;

class Collection implements ilExportHandlerConsumerExportOptionCollectionInterface
{
    protected ilExportHandlerFactoryInterface $export_handler;
    /**
     * @var ilExportHandlerConsumerExportOptionInterface[]
     */
    protected array $elements;
    protected int $index;

    public function __construct(
        ilExportHandlerFactoryInterface $export_handler
    ) {
        $this->elements = [];
        $this->index = 0;
        $this->export_handler = $export_handler;
    }

    public function withElement(ilExportHandlerConsumerExportOptionInterface $element): ilExportHandlerConsumerExportOptionCollectionInterface
    {
        $clone = clone $this;
        $clone->elements[] = $element;
        return $clone;
    }

    public function getById(string $id): ?ilExportHandlerConsumerExportOptionInterface
    {
        foreach ($this->elements as $element) {
            if ($element->getExportOptionId() === $id) {
                return $element;
            }
        }
        return null;
    }

    public function current(): ilExportHandlerConsumerExportOptionInterface
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
