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

namespace ILIAS\Container\Sorting\Settings;

class Settings
{
    public function __construct(
        protected int $obj_id,
        protected int $sort_mode,
        protected int $sort_direction,
        protected int $new_items_position,
        protected int $new_items_order
    ) {
    }

    public function getObjId(): int
    {
        return $this->obj_id;
    }

    public function getSortMode(): int
    {
        return $this->sort_mode;
    }

    public function getSortDirection(): int
    {
        return $this->sort_direction;
    }

    public function getSortNewItemsPosition(): int
    {
        return $this->new_items_position;
    }

    public function getSortNewItemsOrder(): int
    {
        return $this->new_items_order;
    }
}
