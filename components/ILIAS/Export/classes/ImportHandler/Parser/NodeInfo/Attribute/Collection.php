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

namespace ILIAS\Export\ImportHandler\Parser\NodeInfo\Attribute;

use ILIAS\Export\ImportHandler\I\Parser\NodeInfo\Attribute\CollectionInterface as ParserNodeInfoAttributeCollectionInterface;
use ILIAS\Export\ImportHandler\I\Parser\NodeInfo\Attribute\HandlerInterface as ParserNodeInfoAttributeInterface;
use ILIAS\Export\ImportHandler\I\Parser\NodeInfo\HandlerInterface as ParserNodeInfoInterface;
use ilLogger;

class Collection implements ParserNodeInfoAttributeCollectionInterface
{
    /**
     * @var ParserNodeInfoAttributeInterface[]
     */
    protected array $elements;
    protected int $index;
    protected ilLogger $logger;

    public function __construct(
        ilLogger $logger
    ) {
        $this->elements = [];
        $this->index = 0;
        $this->logger = $logger;
    }

    public function matches(ParserNodeInfoInterface $node_info): bool
    {
        foreach ($this->elements as $element) {
            if (
                $node_info->hasAttribute($element->getKey()) &&
                $node_info->getValueOfAttribute($element->getKey()) === $element->getValue()
            ) {
                continue;
            }
            return false;
        }
        return true;
    }

    public function withElement(
        ParserNodeInfoAttributeInterface $element
    ): ParserNodeInfoAttributeCollectionInterface {
        $clone = clone $this;
        $clone->elements[] = $element;
        return $clone;
    }

    public function toArray(): array
    {
        return $this->elements;
    }

    public function current(): ParserNodeInfoAttributeInterface
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

    public function count(): int
    {
        return count($this->elements);
    }
}
