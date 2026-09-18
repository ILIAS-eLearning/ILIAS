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

class LPMark implements LPMarkInterface
{
    protected string $status_changed;
    protected string|null $comment;
    protected string|null $mark;
    protected int $object_id;
    protected int $user_id;
    protected int $status;
    protected int $status_dirty;
    protected int $percentage;
    protected bool $completed;

    public function __construct()
    {
        $this->status_changed = '';
        $this->comment = null;
        $this->mark = null;
        $this->status = 0;
        $this->status_dirty = 0;
        $this->percentage = 0;
        $this->completed = false;
    }

    public function withStatusChanged(
        string $status_changed
    ): LPMarkInterface {
        $clone = clone $this;
        $clone->status_changed = $status_changed;
        return $clone;
    }

    public function withComment(
        string|null $comment
    ): LPMarkInterface {
        $clone = clone $this;
        $clone->comment = $comment;
        return $clone;
    }

    public function withMark(
        string|null $mark
    ): LPMarkInterface {
        $clone = clone $this;
        $clone->mark = $mark;
        return $clone;
    }

    public function withObjectId(
        int $object_id
    ): LPMarkInterface {
        $clone = clone $this;
        $clone->object_id = $object_id;
        return $clone;
    }

    public function withUserId(
        int $user_id
    ): LpMarkInterface {
        $clone = clone $this;
        $clone->user_id = $user_id;
        return $clone;
    }

    public function withStatus(
        int $status
    ): LPMarkInterface {
        $clone = clone $this;
        $clone->status = $status;
        return $clone;
    }

    public function withStatusDirty(
        int $status_dirty
    ): LPMarkInterface {
        $clone = clone $this;
        $clone->status_dirty = $status_dirty;
        return $clone;
    }

    public function withPercentage(
        int $percentage
    ): LPMarkInterface {
        $clone = clone $this;
        $clone->percentage = $percentage;
        return $clone;
    }

    public function withCompletedStatus(
        bool $completed
    ): LPMarkInterface {
        $clone = clone $this;
        $clone->completed = $completed;
        return $clone;
    }

    public function getStatusChanged(): string
    {
        return $this->status_changed;
    }

    public function getComment(): string|null
    {
        return $this->comment;
    }

    public function getMark(): string|null
    {
        return $this->mark;
    }

    public function getObjectId(): int
    {
        return $this->object_id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getStatusDirty(): int
    {
        return $this->status_dirty;
    }

    public function getPercentage(): int
    {
        return $this->percentage;
    }

    public function isCompleted(): bool
    {
        return $this->completed;
    }
}
