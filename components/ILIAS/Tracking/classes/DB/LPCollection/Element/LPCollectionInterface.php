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

use Countable;
use Iterator;

interface LPCollectionInterface extends Iterator, Countable
{
    public function withFixedNumObligatory(): LPCollectionInterface;

    public function withObjectId(
        int $object_id
    ): LPCollectionInterface;

    public function withChangedNumObligatoryIdOfAllElements(
        int $num_obligatory
    ): LPCollectionInterface;

    public function withChangedGroupingIdOfAllElements(
        int $grouping_id
    ): LPCollectionInterface;

    public function withChangedActiveStatusOfAllElements(
        bool $active
    ): LPCollectionInterface;

    public function getSubCollectionOfActiveItems(): LPCollectionInterface;

    public function getSubCollectionOfItemsByGroupingId(
        int $grouping_id
    ): LPCollectionInterface;

    public function getSubCollectionOfItemsByGroupingIds(
        int ...$grouping_ids
    ): LPCollectionInterface;

    public function getSubCollectionOfItemsByItemIds(
        int ...$item_ids
    ): LPCollectionInterface;

    public function getSubCollectionOfItemsByActiveStatus(
        bool $active
    ): LPCollectionInterface;

    public function getElementByItemId(int $item_id): LPCollectionElementInterface|null;

    public function getObjectId(): int;

    /**
     * @return int[]
     */
    public function getItemIds(): array;

    /**
     * @return int[]
     */
    public function getGroupingIds(): array;

    /**
     * @return int[]
     */
    public function getGroupingIdsGreaterZero(): array;

    public function getMaxGroupingNumber(): int;

    public function current(): LPCollectionElementInterface;

    public function key(): int;
}
