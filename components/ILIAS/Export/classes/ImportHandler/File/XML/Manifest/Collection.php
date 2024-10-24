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

use ILIAS\Export\ImportHandler\I\File\XML\Manifest\HandlerCollectionInterface as ManifestXMLFileCollectionInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Manifest\HandlerInterface as ManifestXMLFileInterface;
use ILIAS\Export\ImportStatus\Exception\ilException as ImportStatusException;
use ILIAS\Export\ImportStatus\I\ilCollectionInterface as ImportStatusHandlerCollectionInterface;
use ILIAS\Export\ImportStatus\I\ilFactoryInterface as ImportStatusFactoryInterface;

class Collection implements ManifestXMLFileCollectionInterface
{
    /**
     * @var ManifestXMLFileInterface[];
     */
    protected array $elements;
    protected ImportStatusFactoryInterface $import_status;
    protected int $index;

    public function __construct(
        ImportStatusFactoryInterface $import_status,
    ) {
        $this->import_status = $import_status;
        $this->elements = [];
        $this->index = 0;
    }

    public function count(): int
    {
        return count($this->elements);
    }

    public function current(): ManifestXMLFileInterface
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
        ManifestXMLFileCollectionInterface $other
    ): ManifestXMLFileCollectionInterface {
        $clone = clone $this;
        $clone->elements = array_merge($clone->toArray(), $other->toArray());
        return $clone;
    }

    public function withElement(
        ManifestXMLFileInterface $element
    ): ManifestXMLFileCollectionInterface {
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

    public function validateElements(): ImportStatusHandlerCollectionInterface
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
     * @throws ImportStatusException
     */
    public function findNextFiles(): ManifestXMLFileCollectionInterface
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
     * @return ManifestXMLFileInterface[]
     */
    public function toArray(): array
    {
        return $this->elements;
    }
}
