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

namespace ILIAS\Tracking\DB\LPMarks;

use ILIAS\Tracking\DB\LPMarks\Element\LPMarkCollectionInterface;
use ILIAS\Tracking\DB\LPMarks\Element\LPMarkInterface;

interface RepositoryInterface
{
    /**
     * @return int number of affected rows
     */
    public function write(
        LPMarkInterface $lp_mark
    ): int;

    public function writeCollection(
        LPMarkCollectionInterface $lp_mark_collection
    ): void;

    public function readAllEntriesOfObject(
        int $object_id
    ): LPMarkCollectionInterface;

    public function readAllEntriesWithStatusChangedAfter(
        string $timestamp
    ): LPMarkCollectionInterface;

    public function readAllEntriesWithStatusOfObject(
        int $object_id,
        int $status
    ): LPMarkCollectionInterface;

    public function readEntriesForUserOfObjects(
        int $user_id,
        int ...$object_ids
    ): LPMarkCollectionInterface;

    public function readEntryForUserOfObject(
        int $object_id,
        int $user_id
    ): LPMarkInterface|null;

    public function readByUserIdAndStatusAndTimeInterval(
        int $user_id,
        int $status,
        string $from,
        string $to
    ): LPMarkCollectionInterface;

    public function delete(
        int $object_id
    ): void;

    public function deleteByUserId(
        int $object_id,
        int $user_id
    ): void;

    public function deleteByUserIds(
        int $object_id,
        int ...$user_ids
    ): void;

    public function markAllRowsAsDirty(): void;
}
