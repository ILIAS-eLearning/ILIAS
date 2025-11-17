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

class LPCollectionElement implements LPCollectionElementInterface
{
    protected int $item_id;
    protected int $grouping_id;
    protected int $num_obligatory;
    protected int $lp_mode;
    protected bool $active;

    public function __construct()
    {
    }

    public function getItemId(): int
    {
        return $this->item_id;
    }

    public function getGroupingId(): int
    {
        return $this->grouping_id;
    }

    public function getNumObligatory(): int
    {
        return $this->num_obligatory;
    }

    public function getLpMode(): int
    {
        return $this->lp_mode;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function withItemId(
        int $item_id
    ): LPCollectionElementInterface {
        $clone = clone $this;
        $clone->item_id = $item_id;
        return $clone;
    }

    public function withGroupingId(
        int $grouping_id
    ): LPCollectionElementInterface {
        $clone = clone $this;
        $clone->grouping_id = $grouping_id;
        return $clone;
    }

    public function withNumObligatory(
        int $num_obligatory
    ): LPCollectionElementInterface {
        $clone = clone $this;
        $clone->num_obligatory = $num_obligatory;
        return $clone;
    }

    public function withLPMode(
        int $lp_mode
    ): LPCollectionElementInterface {
        $clone = clone $this;
        $clone->lp_mode = $lp_mode;
        return $clone;
    }

    public function withIsActive(
        bool $active
    ): LPCollectionElementInterface {
        $clone = clone $this;
        $clone->active = $active;
        return $clone;
    }
}
