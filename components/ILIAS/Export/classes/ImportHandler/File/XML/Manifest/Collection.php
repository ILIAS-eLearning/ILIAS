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

namespace ILIAS\Export\ImportHandler\File\XML\Manifest;

use ILIAS\Export\ImportHandler\I\File\XML\Manifest\HandlerCollectionInterface as ilImportHandlerManifestXMLFileCollectionInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Manifest\HandlerInterface as ilImportHandlerManifestXMLFileInterface;
use ILIAS\Export\ImportStatus\Exception\ilException as ilImportStatusException;
use ILIAS\Export\ImportStatus\I\ilCollectionInterface as ilImportStatusHandlerCollectionInterface;
use ILIAS\Export\ImportStatus\I\ilFactoryInterface as ilImportStatusFactoryInterface;

class Collection implements ilImportHandlerManifestXMLFileCollectionInterface
{
    /**
     * @var ilImportHandlerManifestXMLFileInterface[];
     */
    protected array $elements;
    protected ilImportStatusFactoryInterface $import_status;
    protected int $index;

    public function __construct(
        ilImportStatusFactoryInterface $import_status,
    ) {
        $this->import_status = $import_status;
        $this->elements = [];
        $this->index = 0;
    }

    public function count(): int
    {
        return count($this->elements);
    }

    public function current(): ilImportHandlerManifestXMLFileInterface
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

    public function withMerged(
        ilImportHandlerManifestXMLFileCollectionInterface $other
    ): ilImportHandlerManifestXMLFileCollectionInterface {
        $clone = clone $this;
        $clone->elements = array_merge($clone->toArray(), $other->toArray());
        return $clone;
    }

    public function withElement(
        ilImportHandlerManifestXMLFileInterface $element
    ): ilImportHandlerManifestXMLFileCollectionInterface {
        $clone = clone $this;
        $clone->elements[] = $element;
        return $clone;
    }

    public function containsExportObjectType(
        ExportObjectType $type
    ): bool {
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
    public function findNextFiles(): ilImportHandlerManifestXMLFileCollectionInterface
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
     * @return ilImportHandlerManifestXMLFileInterface[]
     */
    public function toArray(): array
    {
        return $this->elements;
    }
}
