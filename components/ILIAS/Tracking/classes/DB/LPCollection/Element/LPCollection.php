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

namespace ILIAS\Tracking\DB\LPCollection\Element;

use ILIAS\Tracking\DB\LPCollection\Element\FactoryInterface as ElementFactoryInterface;

class LPCollection implements LPCollectionInterface
{
    /** @var LPCollectionElementInterface[] */
    protected array $elements;
    protected int $index;
    protected int $object_id;

    public function __construct(
        protected ElementFactoryInterface $element_factory,
        LPCollectionElementInterface ...$elements
    ) {
        $this->elements = $elements;
        $this->index = 0;
    }

    public function next(): void
    {
        $this->index++;
    }

    public function valid(): bool
    {
        return isset($this->elements[$this->index]);
    }

    public function rewind(): void
    {
        $this->index = 0;
    }

    public function count(): int
    {
        return count($this->elements);
    }

    public function withFixedNumObligatory(): LPCollectionInterface
    {
        $mapping = [];
        foreach ($this->elements as $element) {
            if (!isset($mapping[$element->getGroupingId()])) {
                $mapping[$element->getGroupingId()] = [];
            }
            $mapping[$element->getGroupingId()][] = $element;
        }
        $new_elements = [];
        foreach ($mapping as $grouping_id => $elements) {
            $elements_in_group = count($elements);
            foreach ($elements as $element) {
                if ($grouping_id === 0) {
                    $new_elements[] = $element
                        ->withNumObligatory(0);
                    continue;
                }
                if ($elements_in_group === 1) {
                    $new_elements[] = $element
                        ->withNumObligatory(0)
                        ->withGroupingId(0);
                    continue;
                }
                if ($elements_in_group > $element->getNumObligatory()) {
                    $new_elements[] = $element
                        ->withNumObligatory(max(1, $element->getNumObligatory()));;
                    continue;
                }
                if ($elements_in_group <= $element->getNumObligatory()) {
                    $new_elements[] = $element
                        ->withNumObligatory(max(0, $elements_in_group - 1));
                    continue;
                }
            }
        }
        $clone = clone $this;
        $clone->elements = $new_elements;
        return $clone;
    }

    public function withObjectId(
        int $object_id
    ): LPCollectionInterface {
        $clone = clone $this;
        $clone->object_id = $object_id;
        return $clone;
    }

    public function withChangedNumObligatoryIdOfAllElements(
        int $num_obligatory
    ): LPCollectionInterface {
        $new_elements = [];
        foreach ($this->elements as $element) {
            $new_elements[] = $element
                ->withNumObligatory($num_obligatory);
        }
        $clone = clone $this;
        $clone->elements = $new_elements;
        return $clone;
    }

    public function withChangedGroupingIdOfAllElements(
        int $grouping_id
    ): LPCollectionInterface {
        $new_elements = [];
        foreach ($this->elements as $element) {
            $new_elements[] = $element
                ->withGroupingId($grouping_id);
        }
        $clone = clone $this;
        $clone->elements = $new_elements;
        return $clone;
    }

    public function withChangedActiveStatusOfAllElements(
        bool $active
    ): LPCollectionInterface {
        $new_elements = [];
        foreach ($this->elements as $element) {
            $new_elements[] = $element
                ->withIsActive($active);
        }
        $clone = clone $this;
        $clone->elements = $new_elements;
        return $clone;
    }

    public function getSubCollectionOfActiveItems(): LPCollectionInterface
    {
        $elements = [];
        foreach ($this->elements as $element) {
            if ($element->isActive()) {
                $elements[] = $element;
            }
        }
        $clone = clone $this;
        $clone->elements = $elements;
        return $clone;
    }

    public function getSubCollectionOfItemsByGroupingId(
        int $grouping_id
    ): LPCollectionInterface {
        return $this->getSubCollectionOfItemsByGroupingIds($grouping_id);
    }

    public function getSubCollectionOfItemsByGroupingIds(
        int ...$grouping_ids
    ): LPCollectionInterface {
        $elements = [];
        foreach ($this->elements as $element) {
            if (in_array($element->getGroupingId(), $grouping_ids)) {
                $elements[] = $element;
            }
        }
        $clone = clone $this;
        $clone->elements = $elements;
        return $clone;
    }

    public function getSubCollectionOfItemsByItemIds(
        int ...$item_ids
    ): LPCollectionInterface {
        $elements = [];
        foreach ($this->elements as $element) {
            if (in_array($element->getItemId(), $item_ids)) {
                $elements[] = $element;
            }
        }
        $clone = clone $this;
        $clone->elements = $elements;
        return $clone;
    }

    public function getSubCollectionOfItemsByActiveStatus(
        bool $active
    ): LPCollectionInterface {
        $elements = [];
        foreach ($this->elements as $element) {
            if (
                ($element->isActive() && $active) ||
                (!$element->isActive() && !$active)
            ) {
                $elements[] = $element;
            }
        }
        $clone = clone $this;
        $clone->elements = $elements;
        return $clone;
    }

    public function getElementByItemId(
        int $item_id
    ): LPCollectionElementInterface|null {
        foreach ($this->elements as $element) {
            if ($element->getItemId() === $item_id) {
                return $element;
            }
        }
        return null;
    }

    public function getObjectId(): int
    {
        return $this->object_id;
    }

    /**
     * @return int[]
     */
    public function getItemIds(): array
    {
        $ids = [];
        foreach ($this->elements as $element) {
            $ids[] = $element->getItemId();
        }
        return $ids;
    }

    /**
     * @return int[]
     */
    public function getGroupingIds(): array
    {
        $ids = [];
        foreach ($this->elements as $element) {
            $ids[] = $element->getGroupingId();
        }
        return $ids;
    }

    /**
     * @return int[]
     */
    public function getGroupingIdsGreaterZero(): array
    {
        return array_filter($this->getGroupingIds(), fn($id) => $id > 0);
    }

    public function getMaxGroupingNumber(): int
    {
        $ids = [];
        foreach ($this->elements as $element) {
            $ids[] = $element->getGroupingId();
        }
        return count($ids) === 0 ? 0 : max($ids);
    }

    public function current(): LPCollectionElementInterface
    {
        return $this->elements[$this->index];
    }

    public function key(): int
    {
        return $this->index;
    }
}
