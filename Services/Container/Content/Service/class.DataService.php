<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

namespace ILIAS\Container\Content;

use ILIAS\Container\Content\ItemBlock\ItemBlock;
use ILIAS\Container\Content\ItemBlock\ItemBlockSequence;
use ILIAS\Container\Content\ItemBlock\BlockItemsInfo;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class DataService
{
    public function typeBlock(string $type): TypeBlock
    {
        return new TypeBlock($type);
    }

    public function typeBlocks(): TypeBlocks
    {
        return new TypeBlocks();
    }

    public function otherBlock(): OtherBlock
    {
        return new OtherBlock();
    }

    public function objectivesBlock(): ObjectivesBlock
    {
        return new ObjectivesBlock();
    }

    public function sessionBlock(): SessionBlock
    {
        return new SessionBlock();
    }

    public function itemGroupBlock(int $ref_id): ItemGroupBlock
    {
        return new ItemGroupBlock($ref_id);
    }

    public function itemGroupBlocks(): ItemGroupBlocks
    {
        return new ItemGroupBlocks();
    }

    /**
     * @param BlockSequencePart[] $parts
     */
    public function blockSequence(array $parts): BlockSequence
    {
        return new BlockSequence($parts);
    }

    //
    // Blocks with items
    //

    /**
     * @param int[] $item_ref_ids
     */
    public function itemBlock(
        string $id,
        Block $block,
        array $item_ref_ids,
        bool $exhausted,
        array $objective_ids = []
    ): ItemBlock {
        return new ItemBlock($id, $block, $item_ref_ids, $exhausted, $objective_ids);
    }

    public function itemBlockSequence(array $blocks): ItemBlockSequence
    {
        return new ItemBlockSequence($blocks);
    }

    public function blockItemsInfo(array $ref_ids, bool $limit_exhausted): BlockItemsInfo
    {
        return new BlockItemsInfo($ref_ids, $limit_exhausted);
    }
}
