<?php

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Handles general object notification settings, see e.g.
 * https://www.ilias.de/docu/goto_docu_wiki_wpage_3457_1357.html
 *
 * @author Alex Killing <killing@leifos.de>
 * @ingroup ServiceNotification
 */
class ilObjNotificationSettings
{
    const MODE_DEF_OFF_USER_ACTIVATION = 0;
    const MODE_DEF_ON_OPT_OUT = 1;
    const MODE_DEF_ON_NO_OPT_OUT = 2;

    /**
     * @var int
     */
    protected $obj_id;

    /**
     * @var int
     */
    protected $mode = 0;

    /**
     * @var ilDB
     */
    protected $db;

    /**
     * Constructor
     *
     * @param int $a_obj_id object id
     */
    public function __construct($a_obj_id)
    {
        global $DIC;

        $this->obj_id = $a_obj_id;
        $this->db = $DIC->database();
        $this->read();
    }

    /**
     * Set mode
     *
     * @param  $a_val
     */
    public function setMode($a_val)
    {
        $this->mode = $a_val;
    }

    /**
     * Get mode
     *
     * @return int mode
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Save
     */
    public function save()
    {
        $db = $this->db;

        if ($this->obj_id  > 0) {
            $db->replace(
                "obj_noti_settings",
                array("obj_id" => array("integer", $this->obj_id)),
                array("noti_mode" => array("integer", (int) $this->getMode()))
            );
        }
    }

    /**
     * Read
     */
    public function read()
    {
        $db = $this->db;

        $set = $db->query(
            "SELECT * FROM obj_noti_settings " .
            " WHERE obj_id = " . $db->quote($this->obj_id, "integer")
        );
        $rec = $db->fetchAssoc($set);
        $this->setMode((int) $rec["noti_mode"]);
    }


    /**
     * Delete
     */
    public function delete()
    {
        $db = $this->db;

        $db->manipulate("DELETE FROM obj_noti_settings WHERE " .
            " obj_id = " . $db->quote($this->obj_id, "integer"));
    }
}
