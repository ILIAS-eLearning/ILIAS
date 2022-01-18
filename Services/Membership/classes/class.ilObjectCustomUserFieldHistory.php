<?php declare(strict_types=1);/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Editing history for object custom user fields
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @ingroup ServicesMembership
 */
class ilObjectCustomUserFieldHistory
{
    private int $obj_id = 0;
    private int $user_id = 0;
    private int $update_user = 0;
    private ?ilDateTime $editing_time = null;

    protected ilDBInterface $db;

    public function __construct(int $a_obj_id, int $a_user_id)
    {
        global $DIC;

        $this->db = $DIC->database();

        $this->obj_id = $a_obj_id;
        $this->user_id = $a_user_id;
        $this->read();
    }

    /**
     * @param int $a_obj_id
     * @return array<int, array<{update_user: int, editing_time: ilDateTime}>>
     * @throws ilDateTimeException
     */
    public static function lookupEntriesByObjectId(int $a_obj_id) : array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'SELECT * FROM obj_user_data_hist ' .
            'WHERE obj_id = ' . $ilDB->quote($a_obj_id, 'integer');
        $res = $ilDB->query($query);

        $users = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $users[$row->usr_id]['update_user'] = $row->update_user;
            $users[$row->usr_id]['editing_time'] = new ilDateTime($row->editing_time, IL_CAL_DATETIME, ilTimeZone::UTC);
        }
        return $users;
    }

    public function setUpdateUser(int $a_id) : void
    {
        $this->update_user = $a_id;
    }

    public function getUpdateUser() : int
    {
        return $this->update_user;
    }

    public function setEditingTime(ilDateTime $dt) : void
    {
        $this->editing_time = $dt;
    }

    public function getEditingTime() : ilDateTime
    {
        return $this->editing_time;
    }

    public function save() : void
    {
        $this->delete();
        $query = 'INSERT INTO obj_user_data_hist (obj_id, usr_id, update_user, editing_time) ' .
            'VALUES( ' .
            $this->db->quote($this->obj_id, 'integer') . ', ' .
            $this->db->quote($this->user_id, 'integer') . ', ' .
            $this->db->quote($this->getUpdateUser(), 'integer') . ', ' .
            $this->db->quote($this->getEditingTime()->get(IL_CAL_DATETIME, '', ilTimeZone::UTC),
                ilDBConstants::T_INTEGER) . ' ' .
            ')';
        $this->db->manipulate($query);
    }

    public function delete()
    {
        $query = 'DELETE FROM obj_user_data_hist ' .
            'WHERE obj_id = ' . $this->db->quote($this->obj_id, 'integer') . ' ' .
            'AND usr_id = ' . $this->db->quote($this->user_id, 'integer');
        $this->db->manipulate($query);
    }

    protected function read()
    {
        $query = 'SELECT * FROM obj_user_data_hist ' .
            'WHERE obj_id = ' . $this->db->quote($this->obj_id, 'integer') . ' ' .
            'AND usr_id = ' . $this->db->quote($this->user_id, 'integer');
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->setEditingTime(new ilDateTime($row->editing_time, IL_CAL_DATETIME, ilTimeZone::UTC));
            $this->setUpdateUser($row->update_user);
        }
    }
}
