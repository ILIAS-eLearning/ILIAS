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

interface LPMarkInterface
{
    public function withStatusChanged(
        string $status_changed
    ): LPMarkInterface;

    public function withComment(
        string|null $comment
    ): LPMarkInterface;

    public function withMark(
        string|null $mark
    ): LPMarkInterface;

    public function withObjectId(
        int $object_id
    ): LPMarkInterface;

    public function withUserId(
        int $user_id
    ): LpMarkInterface;

    public function withStatus(
        int $status
    ): LPMarkInterface;

    public function withStatusDirty(
        int $status_dirty
    ): LPMarkInterface;

    public function withPercentage(
        int $percentage
    ): LPMarkInterface;

    public function withCompletedStatus(
        bool $completed
    ): LPMarkInterface;

    public function getStatusChanged(): string;

    public function getComment(): string|null;

    public function getMark(): string|null;

    public function getObjectId(): int;

    public function getUserId(): int;

    public function getStatus(): int;

    public function getStatusDirty(): int;

    public function getPercentage(): int;

    public function isCompleted(): bool;
}
