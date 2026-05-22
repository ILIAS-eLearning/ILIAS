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

use ilDBInterface;
use ilDBConstants;
use ilContainer;
use ILIAS\Container\InternalDataService;

class Repository
{
    public function __construct(
        protected InternalDataService $data,
        protected ilDBInterface $db
    ) {
    }

    public function getSortModeForObject(int $obj_id): int
    {
        $query = "SELECT sort_mode FROM container_sorting_set " .
            "WHERE obj_id = " . $this->db->quote($obj_id, ilDBConstants::T_INTEGER) . " ";
        $res = $this->db->query($query);

        if ($row = $res->fetchAssoc()) {
            return (int) $row['sort_mode'];
        }
        return ilContainer::SORT_INHERIT;
    }

    public function save(
        int $obj_id,
        int $sort_mode,
        int $sort_direction,
        int $new_items_position,
        int $new_items_order
    ): void {
        $query = "INSERT INTO container_sorting_set " .
            "(obj_id, sort_mode, sort_direction, new_items_position, new_items_order) " .
            "VALUES ( " .
            $this->db->quote($obj_id, ilDBConstants::T_INTEGER) . ", " .
            $this->db->quote($sort_mode, ilDBConstants::T_INTEGER) . ", " .
            $this->db->quote($sort_direction, ilDBConstants::T_INTEGER) . ', ' .
            $this->db->quote($new_items_position, ilDBConstants::T_INTEGER) . ', ' .
            $this->db->quote($new_items_order, ilDBConstants::T_INTEGER) .
            ") ON DUPLICATE KEY UPDATE " .
            "sort_mode = " . $this->db->quote($sort_mode, ilDBConstants::T_INTEGER) . ", " .
            "sort_direction = " . $this->db->quote($sort_direction, ilDBConstants::T_INTEGER) . ', ' .
            "new_items_position = " . $this->db->quote($new_items_position, ilDBConstants::T_INTEGER) . ', ' .
            "new_items_order = " . $this->db->quote($new_items_order, ilDBConstants::T_INTEGER);

        $this->db->manipulate($query);
    }

    public function delete(int $obj_id): void
    {
        $query = 'DELETE FROM container_sorting_set WHERE obj_id = ' .
            $this->db->quote($obj_id, ilDBConstants::T_INTEGER);
        $this->db->query($query);
    }

    public function getSettings(int $obj_id): ?Settings
    {
        $query = "SELECT * FROM container_sorting_set " .
            "WHERE obj_id = " . $this->db->quote($obj_id, ilDBConstants::T_INTEGER);

        $res = $this->db->query($query);
        if ($row = $res->fetchAssoc()) {
            return $this->data->sorting()->settings(
                $obj_id,
                (int) $row['sort_mode'],
                (int) $row['sort_direction'],
                (int) $row['new_items_position'],
                (int) $row['new_items_order']
            );
        }
        return null;
    }
}
