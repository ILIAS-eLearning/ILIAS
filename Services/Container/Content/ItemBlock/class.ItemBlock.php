<?php

declare(strict_types=1);

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

namespace ILIAS\Container\Content\ItemBlock;

use ILIAS\Container\Content\Block;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ItemBlock
{
    protected array $objective_ids = [];
    protected bool $page_embedded = false;
    protected int $pos = 0;
    /**
     * @var int[]
     */
    protected array $item_ref_ids = [];
    protected Block $block;
    protected string $block_id = "";
    protected bool $limit_exhausted = false;

    public function __construct(
        string $block_id,
        Block $block,
        array $item_ref_ids,
        bool $limit_exhausted,
        array $objective_ids = []
    ) {
        $this->block_id = $block_id;
        $this->block = $block;
        $this->item_ref_ids = $item_ref_ids;
        $this->limit_exhausted = $limit_exhausted;
        $this->objective_ids = $objective_ids;
    }
    public function getId(): string
    {
        return $this->block_id;
    }

    public function getBlock(): Block
    {
        return $this->block;
    }

    public function getLimitExhausted(): bool
    {
        return $this->limit_exhausted;
    }

    /**
     * @return int[]
     */
    public function getItemRefIds(): array
    {
        return $this->item_ref_ids;
    }

    /**
     * @return int[]
     */
    public function getObjectiveIds(): array
    {
        return $this->objective_ids;
    }

    public function setPosition(int $pos): void
    {
        $this->pos = $pos;
    }

    public function getPosition(): int
    {
        return $this->pos;
    }

    public function setPageEmbedded(bool $embedded): void
    {
        $this->page_embedded = $embedded;
    }

    public function getPageEmbedded(): bool
    {
        return $this->page_embedded;
    }
}
