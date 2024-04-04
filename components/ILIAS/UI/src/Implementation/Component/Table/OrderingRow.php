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

namespace ILIAS\UI\Implementation\Component\Table;

use ILIAS\UI\Component\Table as T;

class OrderingRow extends DataRow implements T\OrderingRow
{
    protected int $position;
    protected bool $ordering_disabled = false;

    public function withPosition(int $position_index): self
    {
        $clone = clone $this;
        $clone->position = $position_index;
        return $clone;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function withOrderingDisabled(bool $flag): self
    {
        $clone = clone $this;
        $clone->ordering_disabled = $flag;
        return $clone;
    }

    public function isOrderingDisabled(): bool
    {
        return $this->ordering_disabled;
    }
}
