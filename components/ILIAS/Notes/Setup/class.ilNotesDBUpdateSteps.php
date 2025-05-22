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

namespace ILIAS\Notes\Setup;

class ilNotesDBUpdateSteps implements \ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        //
    }

    public function step_2(): void
    {
        if (!$this->db->tableColumnExists('note', 'recipient')) {
            $this->db->addTableColumn('note', 'recipient', array(
                'type' => 'integer',
                'notnull' => true,
                'length' => 4,
                'default' => 0
            ));
        }
    }

    public function step_3(): void
    {
        $db = $this->db;
        $set1 = $db->queryF(
            "SELECT * FROM note_settings " .
            " WHERE obj_type = %s AND obj_id = %s",
            ["text", "integer"],
            ["0", "0"]
        );
        while ($rec1 = $db->fetchAssoc($set1)) {
            // get type
            $set2 = $db->queryF(
                "SELECT type FROM object_data " .
                " WHERE obj_id = %s ",
                ["integer"],
                [$rec1["rep_obj_id"]]
            );
            if ($rec2 = $db->fetchAssoc($set2)) {

                // get activation with current query
                $set3 = $db->query(
                    "SELECT rep_obj_id FROM note_settings " .
                    " WHERE rep_obj_id = " . $db->quote($rec1["rep_obj_id"], "integer") .
                    " AND activated = " . $db->quote(1, "integer")
                );
                $active = 0;
                if ($db->fetchAssoc($set3)) {
                    $active = 1;
                }
                $db->replace(
                    "note_settings",
                    [
                        "rep_obj_id" => ["integer", $rec1["rep_obj_id"]],
                        "obj_id" => ["integer", $rec1["obj_id"]]
                    ],
                    [
                        "obj_type" => ["text", $rec2["type"]],
                        "activated" => ["integer", $active],
                    ]
                );
                $db->manipulateF(
                    "DELETE FROM note_settings WHERE " .
                    " rep_obj_id = %s AND obj_id = %s AND obj_type = %s",
                    ["integer", "integer", "text"],
                    [$rec1["rep_obj_id"], $rec1["obj_id"], $rec1["obj_type"]]
                );

            }
        }
    }
}
