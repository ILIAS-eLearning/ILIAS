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
     * @return array<int, array<{update_user: int, editing_time: ilDateTime}>>
     * @throws ilDateTimeException
     */
    public static function lookupEntriesByObjectId(int $a_obj_id): array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'SELECT * FROM obj_user_data_hist ' .
            'WHERE obj_id = ' . $ilDB->quote($a_obj_id, 'integer');
        $res = $ilDB->query($query);

        $users = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $users[(int) $row->usr_id]['update_user'] = (int) $row->update_user;
            $users[(int) $row->usr_id]['editing_time'] = new ilDateTime($row->editing_time, IL_CAL_DATETIME, ilTimeZone::UTC);
        }
        return $users;
    }

    public function setUpdateUser(int $a_id): void
    {
        $this->update_user = $a_id;
    }

    public function getUpdateUser(): int
    {
        return $this->update_user;
    }

    public function setEditingTime(ilDateTime $dt): void
    {
        $this->editing_time = $dt;
    }

    public function getEditingTime(): ?\ilDateTime
    {
        return $this->editing_time;
    }

    public function save(): void
    {
        $this->delete();
        $query = 'INSERT INTO obj_user_data_hist (obj_id, usr_id, update_user, editing_time) ' .
            'VALUES( ' .
            $this->db->quote($this->obj_id, 'integer') . ', ' .
            $this->db->quote($this->user_id, 'integer') . ', ' .
            $this->db->quote($this->getUpdateUser(), 'integer') . ', ' .
            $this->db->quote(
                $this->getEditingTime()->get(IL_CAL_DATETIME, '', ilTimeZone::UTC),
                ilDBConstants::T_TIMESTAMP
            ) . ' ' .
            ')';
        $this->db->manipulate($query);
    }

    public function delete(): void
    {
        $query = 'DELETE FROM obj_user_data_hist ' .
            'WHERE obj_id = ' . $this->db->quote($this->obj_id, 'integer') . ' ' .
            'AND usr_id = ' . $this->db->quote($this->user_id, 'integer');
        $this->db->manipulate($query);
    }

    protected function read(): void
    {
        $query = 'SELECT * FROM obj_user_data_hist ' .
            'WHERE obj_id = ' . $this->db->quote($this->obj_id, 'integer') . ' ' .
            'AND usr_id = ' . $this->db->quote($this->user_id, 'integer');
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->setEditingTime(new ilDateTime($row->editing_time, IL_CAL_DATETIME, ilTimeZone::UTC));
            $this->setUpdateUser((int) $row->update_user);
        }
    }
}
