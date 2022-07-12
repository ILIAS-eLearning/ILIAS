<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Handles general object notification settings, see e.g.
 * https://www.ilias.de/docu/goto_docu_wiki_wpage_3457_1357.html
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjNotificationSettings
{
    public const MODE_DEF_OFF_USER_ACTIVATION = 0;
    public const MODE_DEF_ON_OPT_OUT = 1;
    public const MODE_DEF_ON_NO_OPT_OUT = 2;

    protected int $obj_id;
    protected int $mode = 0;
    protected ilDBInterface $db;

    public function __construct(int $a_obj_id)
    {
        global $DIC;

        $this->obj_id = $a_obj_id;
        $this->db = $DIC->database();
        $this->read();
    }

    public function setMode(int $a_val) : void
    {
        $this->mode = $a_val;
    }

    public function getMode() : int
    {
        return $this->mode;
    }

    public function save() : void
    {
        $db = $this->db;

        if ($this->obj_id > 0) {
            $db->replace(
                "obj_noti_settings",
                array("obj_id" => array("integer", $this->obj_id)),
                array("noti_mode" => array("integer", $this->getMode()))
            );
        }
    }

    public function read() : void
    {
        $db = $this->db;

        $set = $db->query(
            "SELECT * FROM obj_noti_settings " .
            " WHERE obj_id = " . $db->quote($this->obj_id, "integer")
        );
        $rec = $db->fetchAssoc($set);
        $this->setMode((int) ($rec["noti_mode"] ?? 0));
    }

    public function delete() : void
    {
        $db = $this->db;

        $db->manipulate("DELETE FROM obj_noti_settings WHERE " .
            " obj_id = " . $db->quote($this->obj_id, "integer"));
    }
}
