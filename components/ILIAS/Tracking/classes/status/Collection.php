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

namespace ILIAS\Tracking\Status;

class Collection implements CollectionInterface
{
    /** @var LPStatusInterface[] */
    protected array $elements;
    protected int $index;

    public function __construct(
        protected FactoryInterface $factory,
        LPStatusInterface ...$elements
    ) {
        $this->elements = $elements;
        $this->index = 0;
    }

    public function getElementsByStatusIds(
        string ...$lp_status_ids
    ): CollectionInterface {
        $elements = [];
        foreach ($lp_status_ids as $lp_status_id) {
            $element = $this->getElementByStatusId($lp_status_id);
            if (is_null($element)) {
                continue;
            }
            $elements[] = $element;
        }
        return $this->factory->collection(...$elements);
    }

    public function getElementByStatusId(
        string $lp_status_id
    ): LPStatusInterface|null {
        foreach ($this->elements as $element) {
            if ($element->getLPStatusId() === $lp_status_id) {
                return $element;
            }
        }
        return null;
    }

    public function current(): LPStatusInterface
    {
        return $this->elements[$this->index];
    }

    public function key(): int
    {
        return $this->index;
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
}
