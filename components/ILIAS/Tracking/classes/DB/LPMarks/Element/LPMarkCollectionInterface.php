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

use Countable;
use Iterator;

interface LPMarkCollectionInterface extends Iterator, Countable
{
    public function asDataArray(): array;

    public function asUserIdArray(): array;

    public function getSubCollectionOfElementsByUserIds(
        int ...$user_ids
    ): LPMarkCollectionInterface;

    public function getSubCollectionOfElementsByCompletedStatus(
        bool $completed
    ): LPMarkCollectionInterface;

    public function getSubCollectionOfElementsByStatus(
        int $status
    ): LPMarkCollectionInterface;

    public function getSubCollectionOfElementsByStatusDirty(
        int $status_dirty
    ): LPMarkCollectionInterface;

    public function getSubCollectionOfElementsWithDistinctUsers(): LPMarkCollectionInterface;

    public function withChangedStatusDirtyOfAllElements(
        int $status_dirty
    ): LPMarkCollectionInterface;

    public function current(): LPMarkInterface;

    public function key(): int;
}
