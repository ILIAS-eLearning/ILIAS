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

namespace ILIAS\Export\ImportHandler\Path\Node;

use ILIAS\Export\ImportHandler\I\Path\Comparison\HandlerInterface;
use ILIAS\Export\ImportHandler\I\Path\Node\IndexInterface as IndexFilePathNodeInterface;

class Index implements IndexFilePathNodeInterface
{
    protected HandlerInterface $comparison;
    protected int $index;
    protected bool $indexing_from_end_enabled;

    public function __construct()
    {
        $this->index = 0;
        $this->indexing_from_end_enabled = false;
    }

    public function withIndex(int $index): IndexFilePathNodeInterface
    {
        $clone = clone $this;
        $clone->index = $index;
        return $clone;
    }

    public function withComparison(HandlerInterface $comparison): IndexFilePathNodeInterface
    {
        $clone = clone $this;
        $clone->comparison = $comparison;
        return $clone;
    }

    public function withIndexingFromEndEnabled(bool $enabled): IndexFilePathNodeInterface
    {
        $clone = clone $this;
        $clone->indexing_from_end_enabled = $enabled;
        return $clone;
    }

    public function toString(): string
    {
        $indexing = '';
        if (!isset($this->comparison)) {
            $indexing = $this->indexing_from_end_enabled
                ? '(last)-' . $this->index
                : $this->index;
        } else {
            $indexing = 'position()' . $this->comparison->toString();
        }
        return '[' . $indexing . ']';
    }

    public function requiresPathSeparator(): bool
    {
        return false;
    }
}
