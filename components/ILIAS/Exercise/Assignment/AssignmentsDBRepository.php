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

namespace ILIAS\Exercise\Assignment;

use ILIAS\Exercise\InternalDataService;

class AssignmentsDBRepository
{
    protected \ilDBInterface $db;
    protected InternalDataService $data;

    public function __construct(
        InternalDataService $data,
        \ilDBInterface $db
    ) {
        $this->db = $db;
        $this->data = $data;
    }

    protected function getAssignmentFromRecord(array $rec): Assignment
    {
        return $this->data->assignment(
            (int) $rec["id"],
            (int) $rec["exc_id"],
            (string) $rec["title"],
            (int) $rec["order_nr"],
            (int) $rec["type"],
            (string) $rec["instruction"],
            (bool) $rec["mandatory"],
            (int) $rec["deadline_mode"],
            (int) $rec["time_stamp"],
            (int) $rec["deadline2"],
            (int) $rec["relative_deadline"],
            (int) $rec["rel_deadline_last_subm"]
        );
    }

    /**
     * @return iterable<Assignment>
     */
    public function getList(int $exc_id): \Iterator
    {
        $set = $this->db->query("SELECT * FROM exc_assignment " .
            " WHERE exc_id = " . $this->db->quote($exc_id, "integer") .
            " ORDER BY order_nr");
        $order_val = 10;
        while ($rec = $this->db->fetchAssoc($set)) {
            $rec["order_nr"] = $order_val;
            $order_val += 10;
            yield $this->getAssignmentFromRecord($rec);
        }
    }

    public function get(int $exc_id, int $ass_id): ?Assignment
    {
        $set = $this->db->queryF(
            "SELECT * FROM exc_assignment " .
            " WHERE exc_id = %s AND id = %s",
            ["integer", "integer"],
            [$exc_id, $ass_id]
        );
        if ($rec = $this->db->fetchAssoc($set)) {
            return $this->getAssignmentFromRecord($rec);
        }
        return null;
    }

}
