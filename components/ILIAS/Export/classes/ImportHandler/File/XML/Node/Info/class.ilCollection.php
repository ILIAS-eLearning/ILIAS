<?php

namespace ImportHandler\File\XML\Node\Info;

use ImportHandler\I\File\XML\Node\Info\ilCollectionInterface as ilXMLFileNodeInfoCollectionInterface;
use ImportHandler\I\File\XML\Node\Info\ilHandlerInterface as ilXMLFileNodeInfoInterface;

class ilCollection implements ilXMLFileNodeInfoCollectionInterface
{
    /**
     * @var ilXMLFileNodeInfoInterface[]
     */
    protected array $elements;
    protected int $index;

    public function __construct()
    {
        $this->elements = [];
        $this->index = 0;
    }

    public function getFirst(): ilXMLFileNodeInfoInterface
    {
        return $this->elements[0];
    }

    public function removeFirst(): ilXMLFileNodeInfoCollectionInterface
    {
        $clone = clone $this;
        $clone->index = $this->index;
        $clone->elements = array_slice($this->elements, 1);
        return $clone;
    }

    public function count(): int
    {
        return count($this->elements);
    }

    public function withMerged(ilXMLFileNodeInfoCollectionInterface $other): ilXMLFileNodeInfoCollectionInterface
    {
        $clone = clone $this;
        $clone->elements = array_merge($this->toArray(), $other->toArray());
        return $clone;
    }

    public function withElement(ilXMLFileNodeInfoInterface $element): ilXMLFileNodeInfoCollectionInterface
    {
        $clone = clone $this;
        $clone->elements[] = $element;
        return $clone;
    }

    public function toArray(): array
    {
        return $this->elements;
    }

    public function current(): ilXMLFileNodeInfoInterface
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
