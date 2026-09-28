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

namespace ILIAS\Tracking\DB\LPMarks\Element;

class LPMarkCollection implements LPMarkCollectionInterface
{
    /** @var LPMarkInterface[] */
    protected array $elements;
    protected int $index;

    public function __construct(
        LPMarkInterface ...$elements
    ) {
        $this->elements = $elements;
        $this->index = 0;
    }

    public function asDataArray(): array
    {
        $data = [];
        foreach ($this->elements as $element) {
            $entry = [
                'obj_id' => $element->getObjectId(),
                'usr_id' => $element->getUserId(),
                'completed' => $element->isCompleted(),
                'mark' => (string) $element->getMark(),
                'comment' => (string) $element->getComment(),
                'status' => $element->getStatus(),
                'status_changed' => $element->getStatusChanged(),
                'status_dirty' => $element->getStatusDirty(),
                'percentage' => $element->getPercentage()
            ];
            $data[] = $entry;
        }
        return $data;
    }

    /**
     * @return int[]
     */
    public function asUserIdArray(): array
    {
        $data = [];
        foreach ($this->elements as $element) {
            $data[] = $element->getUserId();
        }
        return $data;
    }

    public function getSubCollectionOfElementsByUserIds(
        int ...$user_ids
    ): LPMarkCollectionInterface {
        $clone = clone $this;
        $clone->elements = array_filter($clone->elements, fn(LPMarkInterface $element) => in_array($element->getUserId(), $user_ids));
        return $clone;
    }

    public function getSubCollectionOfElementsByCompletedStatus(
        bool $completed
    ): LPMarkCollectionInterface {
        $clone = clone $this;
        $clone->elements = array_filter($clone->elements, fn(LPMarkInterface $element) => $element->isCompleted() === $completed);
        return $clone;
    }

    public function getSubCollectionOfElementsByStatus(
        int $status
    ): LPMarkCollectionInterface {
        $clone = clone $this;
        $clone->elements = array_filter($clone->elements, fn(LPMarkInterface $element) => $element->getStatus() === $status);
        return $clone;
    }

    public function getSubCollectionOfElementsByStatusDirty(
        int $status_dirty
    ): LPMarkCollectionInterface {
        $clone = clone $this;
        $clone->elements = array_filter($clone->elements, fn(LPMarkInterface $element) => $element->getStatusDirty() === $status_dirty);
        return $clone;
    }

    public function getSubCollectionOfElementsWithDistinctUsers(): LPMarkCollectionInterface
    {
        $ids = [];
        $elements = [];
        foreach ($this->elements as $element) {
            if (in_array($element->getUserId(), $ids)) {
                continue;
            }
            $ids[] = $element->getUserId();
            $elements[] = $element;
        }
        $clone = clone $this;
        $clone->elements = $elements;
        return $clone;
    }

    public function withChangedStatusDirtyOfAllElements(
        int $status_dirty
    ): LPMarkCollectionInterface {
        $elements = [];
        foreach ($this->elements as $element) {
            $elements[] = $element
                ->withStatusDirty($status_dirty);
        }
        $clone = clone $this;
        $clone->elements = $elements;
        return $clone;
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

    public function current(): LPMarkInterface
    {
        return $this->elements[$this->index];
    }

    public function key(): int
    {
        return $this->index;
    }
}
