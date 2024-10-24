<?php

namespace ILIAS\Export\ImportHandler\Parser\NodeInfo;

use ILIAS\Export\ImportHandler\I\Parser\NodeInfo\CollectionInterface as ParserNodeInfoCollectionInterface;
use ILIAS\Export\ImportHandler\I\Parser\NodeInfo\HandlerInterface as ParserNodeInfoInterface;

class Collection implements ParserNodeInfoCollectionInterface
{
    /**
     * @var ParserNodeInfoInterface[]
     */
    protected array $elements;
    protected int $index;

    public function __construct()
    {
        $this->elements = [];
        $this->index = 0;
    }

    public function getFirst(): ParserNodeInfoInterface
    {
        return $this->elements[0];
    }

    public function removeFirst(): ParserNodeInfoCollectionInterface
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

    public function withMerged(
        ParserNodeInfoCollectionInterface $other
    ): ParserNodeInfoCollectionInterface {
        $clone = clone $this;
        $clone->elements = array_merge($this->toArray(), $other->toArray());
        return $clone;
    }

    public function withElement(
        ParserNodeInfoInterface $element
    ): ParserNodeInfoCollectionInterface {
        $clone = clone $this;
        $clone->elements[] = $element;
        return $clone;
    }

    public function toArray(): array
    {
        return $this->elements;
    }

    public function current(): ParserNodeInfoInterface
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
}
