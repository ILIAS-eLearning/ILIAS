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

namespace ImportHandler\File\XML\Manifest;

use ImportHandler\I\File\XML\Manifest\ilHandlerInterface as ilManifestXMLFileHandlerInterface;
use ImportHandler\I\File\XML\Manifest\ilHandlerCollectionInterface as ilManifestXMLFileHandlerCollectionInterface;
use ImportStatus\Exception\ilException as ilImportStatusException;
use ImportStatus\I\ilCollectionInterface as ilImportStatusHandlerCollectionInterface;
use ImportStatus\I\ilFactoryInterface as ilImportStatusFactoryInterface;

class ilHandlerCollection implements ilManifestXMLFileHandlerCollectionInterface
{
    /**
     * @var ilManifestXMLFileHandlerInterface[];
     */
    protected array $elements;
    protected ilImportStatusFactoryInterface $import_status;
    protected int $index;

    /**
     * @param ilManifestXMLFileHandlerInterface[] $initial_elements
     */
    public function __construct(
        ilImportStatusFactoryInterface $import_status,
        array $initial_elements = []
    ) {
        $this->import_status = $import_status;
        $this->elements = $initial_elements;
        $this->index = 0;
    }

    public function count(): int
    {
        return count($this->elements);
    }

    public function current(): ilManifestXMLFileHandlerInterface
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

    public function withMerged(
        ilManifestXMLFileHandlerCollectionInterface $other
    ): ilManifestXMLFileHandlerCollectionInterface {
        $clone = clone $this;
        $clone->elements = array_merge($clone->toArray(), $other->toArray());
        return $clone;
    }

    public function withElement(ilManifestXMLFileHandlerInterface $element): ilManifestXMLFileHandlerCollectionInterface
    {
        $clone = clone $this;
        $clone->elements[] = $element;
        return $clone;
    }

    public function containsExportObjectType(ilExportObjectType $type): bool
    {
        foreach ($this->toArray() as $manifest_file_handler) {
            if ($manifest_file_handler->getExportObjectType() === $type) {
                return true;
            }
        }
        return false;
    }

    public function validateElements(): ilImportStatusHandlerCollectionInterface
    {
        $status_collection = $this->import_status->collection()->withNumberingEnabled(true);
        foreach ($this as $manfiest_file_handler) {
            $status_collection = $status_collection->getMergedCollectionWith(
                $manfiest_file_handler->validateManifestXML()
            );
        }
        return $status_collection;
    }

    /**
     * @throws ilImportStatusException
     */
    public function findNextFiles(): ilManifestXMLFileHandlerCollectionInterface
    {
        $collection = clone $this;
        $collection->rewind();
        $collection->elements = [];
        foreach ($this as $manfiest_file_handler) {
            $collection = $collection->withMerged($manfiest_file_handler->findManifestXMLFileHandlers());
        }
        return $collection;
    }

    /**
     * @return ilManifestXMLFileHandlerInterface[]
     */
    public function toArray(): array
    {
        return $this->elements;
    }
}
