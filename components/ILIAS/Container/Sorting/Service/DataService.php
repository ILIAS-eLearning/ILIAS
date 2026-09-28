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

namespace ILIAS\Container\Sorting\Service;

use ILIAS\Container\Sorting\Settings\Settings as SortingSettings;
use ILIAS\Container\Sorting\Positions\PositionData;
use ILIAS\Container\Sorting\Positions\Grouping;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class DataService
{
    public function settings(
        int $obj_id,
        int $sort_mode,
        int $sort_direction,
        int $new_items_position,
        int $new_items_order
    ): SortingSettings {
        return new SortingSettings(
            $obj_id,
            $sort_mode,
            $sort_direction,
            $new_items_position,
            $new_items_order
        );
    }

    public function positionGrouping(
        int $obj_id,
        string $parent_type,
        int $parent_id,
        PositionData ...$positions
    ): Grouping {
        return new Grouping(
            $obj_id,
            $parent_type,
            $parent_id,
            ...$positions
        );
    }

    public function positionData(
        int $child_id,
        int $position,
    ): PositionData {
        return new PositionData(
            $child_id,
            $position
        );
    }
}
