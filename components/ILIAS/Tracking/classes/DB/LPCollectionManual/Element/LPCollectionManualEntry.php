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

namespace ILIAS\Tracking\DB\LPCollectionManual\Element;

class LPCollectionManualEntry implements LPCollectionManualEntryInterface
{
    protected int $object_id;
    protected int $user_id;
    protected int $subitem_id;
    protected int $last_change;
    protected bool $completed;

    public function getObjectId(): int
    {
        return $this->object_id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getSubitemId(): int
    {
        return $this->subitem_id;
    }

    public function getLastChanged(): int
    {
        return $this->last_change;
    }

    public function isCompleted(): bool
    {
        return $this->completed;
    }

    public function withObjectId(
        int $object_id
    ): LPCollectionManualEntryInterface {
        $clone = clone $this;
        $clone->object_id = $object_id;
        return $clone;
    }

    public function withUserId(
        int $user_id
    ): LPCollectionManualEntryInterface {
        $clone = clone $this;
        $clone->user_id = $user_id;
        return $clone;
    }

    public function withSubitemId(
        int $subitem_id
    ): LPCollectionManualEntryInterface {
        $clone = clone $this;
        $clone->subitem_id = $subitem_id;
        return $clone;
    }

    public function withLastChanged(
        int $last_changed
    ): LPCollectionManualEntryInterface {
        $clone = clone $this;
        $clone->last_change = $last_changed;
        return $clone;
    }

    public function withCompletedStatus(
        bool $completed
    ): LPCollectionManualEntryInterface {
        $clone = clone $this;
        $clone->completed = $completed;
        return $clone;
    }
}
