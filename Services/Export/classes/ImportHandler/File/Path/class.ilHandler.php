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

namespace ImportHandler\File\Path;

use ImportHandler\I\File\Path\Node\ilNodeInterface as ilFilePathNodeInterface;
use ImportHandler\I\File\Path\ilHandlerInterface as ilParserPathHandlerInterface;

class ilHandler implements ilParserPathHandlerInterface
{
    /**
     * @var ilFilePathNodeInterface[]
     */
    protected array $nodes;
    protected int $index;
    protected bool $with_start_at_root_enabled;

    public function __construct()
    {
        $this->nodes = [];
        $this->index = 0;
        $this->with_start_at_root_enabled = false;
    }

    public function withStartAtRoot(bool $enabled): ilParserPathHandlerInterface
    {
        $clone = clone $this;
        $clone->with_start_at_root_enabled = $enabled;
        return $clone;
    }

    public function withNode(ilFilePathNodeInterface $node): ilParserPathHandlerInterface
    {
        $clone = clone $this;
        $clone->nodes[] = $node;
        return $clone;
    }

    public function toString(): string
    {
        $first_separator = true;
        $path_str = '';
        for ($i = 0; $i < count($this->nodes); $i++) {
            $node = $this->nodes[$i];
            if (
                ($node->requiresPathSeparator() && $first_separator && $this->with_start_at_root_enabled) ||
                ($node->requiresPathSeparator() && !$first_separator)
            ) {
                $path_str .= '/';
                $first_separator = false;
            }
            if ($node->requiresPathSeparator() && $first_separator && !$this->with_start_at_root_enabled) {
                $path_str .= '//';
                $first_separator = false;
            }
            $path_str .= $node->toString();
        }
        return $path_str;
    }

    public function subPath(int $start, ?int $end = null): ilParserPathHandlerInterface
    {
        $clone = clone $this;
        $clone->nodes = is_null($end)
            ? array_slice($this->nodes, $start)
            : array_slice($this->nodes, $start, $end - $start);
        return $clone;
    }

    public function firstElement(): ilFilePathNodeInterface|null
    {
        return $this->count() > 0
            ? $this->nodes[0]
            : null;
    }

    public function lastElement(): ilFilePathNodeInterface|null
    {
        return $this->count() > 0
            ? $this->nodes[$this->count() - 1]
            : null;
    }

    /**
     * @return ilFilePathNodeInterface[]
     */
    public function toArray(): array
    {
        return $this->nodes;
    }

    public function current(): ilFilePathNodeInterface
    {
        return $this->nodes[$this->index];
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
        return count($this->nodes);
    }
}
