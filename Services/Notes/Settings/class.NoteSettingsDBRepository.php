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

namespace ILIAS\Notes;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class NoteSettingsDBRepository
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

    /**
     * Are comments activated for object?
     */
    public function commentsActive(
        int $obj_id
    ): bool {
        $db = $this->db;
        $set = $db->query(
            "SELECT rep_obj_id FROM note_settings " .
            " WHERE rep_obj_id = " . $db->quote($obj_id, "integer") .
            " AND activated = " . $db->quote(1, "integer")
        );
        if ($db->fetchAssoc($set)) {
            return true;
        }
        return false;
    }

    public function commentsActiveMultiple(
        array $obj_ids
    ): array {
        $db = $this->db;

        $set = $db->query("SELECT * FROM note_settings " .
            " WHERE " . $db->in("rep_obj_id", $obj_ids, false, "integer") .
            " AND obj_id = 0 ");
        $activations = [];
        while ($rec = $db->fetchAssoc($set)) {
            if ($rec["activated"]) {
                $activations[$rec["rep_obj_id"]] = true;
            }
        }

        return $activations;
    }


    /**
     * Activate notes feature
     */
    public function activateComments(
        int $obj_id,
        int $sub_obj_id,
        string $obj_type,
        bool $a_activate = true
    ): void {
        $db = $this->db;

        if ($obj_type === "") {
            $obj_type = "-";
        }

        $db->replace(
            "note_settings",
            [
           "rep_obj_id" => ["integer", $obj_id],
           "obj_id" => ["integer", $sub_obj_id],
           "obj_type" => ["integer", $obj_type],
        ],
            [
                "activated" => ["integer", (int) $a_activate]
            ]
        );
    }
}
