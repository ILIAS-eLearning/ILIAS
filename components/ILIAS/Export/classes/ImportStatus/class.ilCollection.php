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

namespace ImportStatus;

use ImportStatus\I\Content\ilHandlerInterface as ilImportStatusContentHandlerInterface;
use ImportStatus\I\ilCollectionInterface;
use ImportStatus\I\ilHandlerInterface;
use ImportStatus\I\ilFactoryInterface as ilImportStatusFactoryInterface;

class ilCollection implements ilCollectionInterface
{
    protected ilImportStatusFactoryInterface $status;
    /**
     * @var ilHandlerInterface[]
     */
    protected array $elements;
    protected int $index;
    protected int $minIndex;
    protected bool $is_numbering_enabled;

    public function __construct(
        ilImportStatusFactoryInterface $status,
    ) {
        $this->elements = [];
        $this->minIndex = 0;
        $this->index = $this->minIndex;
        $this->is_numbering_enabled = false;
        $this->status = $status;
    }

    /**
     * @return ilHandlerInterface[]
     */
    protected function getArrayOfElementsWithType(StatusType $type): array
    {
        return array_filter(
            $this->toArray(),
            function (ilHandler $s) use ($type) {
                return $s->getType() === $type;
            }
        );
    }

    public function hasStatusType(StatusType $type): bool
    {
        return count($this->getArrayOfElementsWithType($type)) > 0;
    }

    public function withAddedStatus(ilHandlerInterface $import_status): ilCollection
    {
        $clone = clone $this;
        $clone->elements[] = $import_status;
        return $clone;
    }

    public function getCollectionOfAllByType(StatusType $type): ilCollectionInterface
    {
        $clone = clone $this;
        $clone->elements = $this->getArrayOfElementsWithType($type);
        return $clone;
    }

    public function getMergedCollectionWith(ilCollectionInterface $other): ilCollectionInterface
    {
        $clone = clone $this;
        $clone->elements = array_merge($this->toArray(), $other->toArray());
        return $clone;
    }

    public function current(): ilHandlerInterface
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
        return $this->minIndex <= $this->index && $this->index < $this->count();
    }

    public function rewind(): void
    {
        $this->index = $this->minIndex;
    }

    public function count(): int
    {
        return count($this->elements);
    }

    /**
     * @return ilHandlerInterface[]
     */
    public function toArray(): array
    {
        return $this->elements;
    }

    public function withNumberingEnabled(bool $enabled): ilCollectionInterface
    {
        $clone = clone $this;
        $clone->is_numbering_enabled = $enabled;
        return $clone;
    }

    public function toString(StatusType ...$types): string
    {
        $collection = $this->status->collection();
        $msg = "<br>Listing status messages (of type(s)";
        foreach ($types as $type) {
            $msg .= " " . $type->name;
            $collection = $collection->getMergedCollectionWith($this->getCollectionOfAllByType($type));
        }
        $msg .= "):<br>";
        $elements = $collection->toArray();
        for($i = 0; $i < count($elements); $i++) {
            $faied_status = $elements[$i];
            $msg .= "<br>";
            $msg .= $this->is_numbering_enabled
                ? '[' . $i . ']: '
                : '';
            $msg .= $faied_status->getContent()->toString();
        }
        return $msg;
    }

    public function mergeContentToElements(
        ilImportStatusContentHandlerInterface $content,
        bool $at_front = true
    ): ilCollectionInterface {
        $clone = clone $this;
        $new_elements = [];
        foreach ($clone->toArray() as $element) {
            $new_elements[] = $at_front
                ? $this->status->handler()
                    ->withType($element->getType())
                    ->withContent($content->mergeWith($element->getContent()))
                : $this->status->handler()
                    ->withType($element->getType())
                    ->withContent($element->getContent()->mergeWith($content));
        }
        $clone->elements = $new_elements;
        return $clone;
    }
}
