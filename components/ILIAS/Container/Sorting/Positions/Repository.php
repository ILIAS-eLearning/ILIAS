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

use ilDBInterface;
use ilDBConstants;
use Generator;
use ILIAS\Container\InternalDataService;

class Repository
{
    public function __construct(
        protected InternalDataService $data,
        protected ilDBInterface $db
    ) {
    }

    /**
     * @return Grouping[]
     */
    public function getPositions(int $obj_id): Generator
    {
        $data_by_parent = [];
        $query = "SELECT * FROM container_sorting " .
            "WHERE obj_id = " . $this->db->quote($obj_id, ilDBConstants::T_INTEGER) . " ORDER BY position";
        $res = $this->db->query($query);
        while ($row = $res->fetchAssoc()) {
            $data = $this->data->sorting()->positionData($row['child_id'], $row['position']);
            if ($row['parent_id'] ?? 0) {
                $data_by_parent[$row['parent_type']][$row['parent_id']][] = $data;
            } else {
                $data_by_parent['all'][0][] = $data;
            }
        }
        foreach ($data_by_parent as $parent_type => $parents) {
            foreach ($parents as $parent_id => $positions) {
                yield $this->data->sorting()->positionGrouping(
                    $obj_id,
                    (string) $parent_type,
                    (int) $parent_id,
                    ...$positions
                );
            }
        }
    }

    public function savePositionForChild(
        int $obj_id,
        int $child_id,
        int $position,
        string $parent_type,
        int $parent_id
    ): void {
        $this->db->replace(
            'container_sorting',
            [
                'obj_id' => [ilDBConstants::T_INTEGER, $obj_id],
                'child_id' => [ilDBConstants::T_INTEGER, $child_id],
                'parent_id' => [ilDBConstants::T_INTEGER, $parent_id]
            ],
            [
                'parent_type' => [ilDBConstants::T_TEXT, $parent_type],
                'position' => [ilDBConstants::T_INTEGER, $position]
            ]
        );
    }

    public function deletePositions(int $obj_id): void
    {
        $this->db->manipulate(
            "DELETE FROM container_sorting WHERE obj_id = " .
            $this->db->quote($obj_id, ilDBConstants::T_INTEGER)
        );
    }

    public function deleteGrouping(int $obj_id, int $parent_id): void
    {
        $this->db->manipulate(
            "DELETE FROM container_sorting WHERE obj_id = " .
            $this->db->quote($obj_id, ilDBConstants::T_INTEGER) .
            " AND parent_id = " . $this->db->quote($parent_id, ilDBConstants::T_INTEGER)
        );
    }

    public function deletePositionsForChild(int $obj_id, int $child_id, int $parent_id): void
    {
        $this->db->manipulate(
            "DELETE FROM container_sorting WHERE obj_id = " .
            $this->db->quote($obj_id, ilDBConstants::T_INTEGER) .
            " AND child_id = " . $this->db->quote($child_id, ilDBConstants::T_INTEGER) .
            " AND parent_id = " . $this->db->quote($parent_id, ilDBConstants::T_INTEGER)
        );
    }
}
