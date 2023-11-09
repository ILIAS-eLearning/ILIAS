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

namespace ImportHandler\File\XML\Node\Info\Attribute;

use ilLogger;
use ImportHandler\I\File\XML\Node\Info\Attribute\ilCollectionInterface as ilXMLFileNodeInfoAttributeCollectionInterface;
use ImportHandler\I\File\XML\Node\Info\Attribute\ilPairInterface as ilXMLFileNodeInfoAttributePairInterface;
use ImportHandler\I\File\XML\Node\Info\ilHandlerInterface as ilXMLFileNodeInfoInterface;

class ilCollection implements ilXMLFileNodeInfoAttributeCollectionInterface
{
    /**
     * @var ilXMLFileNodeInfoAttributePairInterface[]
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

    public function matches(ilXMLFileNodeInfoInterface $node_info): bool
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
        ilXMLFileNodeInfoAttributePairInterface $element
    ): ilXMLFileNodeInfoAttributeCollectionInterface {
        $clone = clone $this;
        $clone->elements[] = $element;
        return $clone;
    }

    public function toArray(): array
    {
        return $this->elements;
    }

    public function current(): ilXMLFileNodeInfoAttributePairInterface
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
