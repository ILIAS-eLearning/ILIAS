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

namespace ILIAS\BookingManager\BookingProcess;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class SelectedObjectsDBRepository
{
    protected \ilDBInterface $db;

    public function __construct(
        \ilDBInterface $db
    ) {
        $this->db = $db;
    }

    public function getSelectedObjects(int $pool_id, int $user_id) : array
    {
        $db = $this->db;
        $set = $db->queryF(
            "SELECT * FROM book_sel_object " .
            " WHERE user_id = %s ".
            " AND pool_id = %s ",
            ["integer", "integer"],
            [$user_id, $pool_id]
        );
        $obj_ids = [];
        while ($rec = $db->fetchAssoc($set)) {
            $obj_ids[] = (int) $rec["object_id"];
        }
        return $obj_ids;
    }

    public function setSelectedObjects(int $pool_id, int $user_id, array $obj_ids) : void
    {
        $db = $this->db;
        $this->deleteSelectedObjects($pool_id, $user_id);
        foreach ($obj_ids as $obj_id) {
            $db->insert("book_sel_object", [
                "user_id" => ["integer", $user_id],
                "pool_id" => ["integer", $pool_id],
                "object_id" => ["integer", $obj_id]
            ]);
        }
    }

    protected function deleteSelectedObjects(int $pool_id, int $user_id) : void
    {
        $db = $this->db;
        $db->manipulateF(
            "DELETE FROM book_sel_object WHERE " .
            " user_id = %s".
            " AND pool_id = %s",
            ["integer", "integer"],
            [$user_id, $pool_id]
        );
    }
}
