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

namespace ILIAS\Container\Sorting\Positions;

use Generator;

class Grouping
{
    /**
     * @var PositionData[]
     */
    protected array $positions;

    public function __construct(
        protected int $obj_id,
        protected string $parent_type,
        protected int $parent_id,
        PositionData ...$positions
    ) {
        $this->positions = $positions;
    }

    public function getObjId(): int
    {
        return $this->obj_id;
    }

    public function getParentType(): string
    {
        return $this->parent_type;
    }

    public function getParentID(): int
    {
        return $this->parent_id;
    }

    /**
     * @return PositionData[]
     */
    public function getPositions(): Generator
    {
        yield from $this->positions;
    }
}
