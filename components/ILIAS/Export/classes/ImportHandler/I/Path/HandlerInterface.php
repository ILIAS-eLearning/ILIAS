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

namespace ILIAS\Export\ImportHandler\I\Path;

use Countable;
use ILIAS\Export\ImportHandler\I\Path\Node\NodeInterface as PathNodeInterface;
use Iterator;

interface HandlerInterface extends Iterator, Countable
{
    public function withStartAtRoot(bool $enabled): HandlerInterface;

    public function withNode(
        PathNodeInterface $node
    ): HandlerInterface;

    public function toString(): string;

    public function subPath(int $start, ?int $end = null): HandlerInterface;

    public function firstElement(): PathNodeInterface|null;

    public function lastElement(): PathNodeInterface|null;

    /**
     * @return PathNodeInterface[]
     */
    public function toArray(): array;

    public function current(): PathNodeInterface;

    public function next(): void;

    public function key(): int;

    public function valid(): bool;

    public function rewind(): void;

    public function count(): int;
}
