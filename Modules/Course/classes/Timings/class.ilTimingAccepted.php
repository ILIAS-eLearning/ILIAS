<?php declare(strict_types=0);
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

/**
 * class ilTimingAccepted
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilTimingAccepted
{
    protected ilDBInterface $db;

    private int $obj_id = 0;
    private int $user_id = 0;
    private bool $visible = false;
    private string $remark = '';
    private bool $accepted = false;

    public function __construct(int $crs_id, int $a_usr_id)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->obj_id = $crs_id;
        $this->user_id = $a_usr_id;
        $this->__read();
    }

    public function getUserId() : int
    {
        return $this->user_id;
    }

    public function getCourseId() : int
    {
        return $this->obj_id;
    }

    public function accept(bool $a_status) : void
    {
        $this->accepted = $a_status;
    }

    public function isAccepted() : bool
    {
        return $this->accepted;
    }

    public function setRemark(string $a_remark) : void
    {
        $this->remark = $a_remark;
    }

    public function getRemark() : string
    {
        return $this->remark;
    }

    public function setVisible(bool $a_visible) : void
    {
        $this->visible = $a_visible;
    }

    public function isVisible() : bool
    {
        return $this->visible;
    }

    public function update() : void
    {
        ilTimingAccepted::_delete($this->getCourseId(), $this->getUserId());
        $this->create();
    }

    public function create() : void
    {
        $query = "INSERT INTO crs_timings_usr_accept (crs_id,usr_id,visible,accept,remark) " .
            "VALUES( " .
            $this->db->quote($this->getCourseId(), 'integer') . ", " .
            $this->db->quote($this->getUserId(), 'integer') . ", " .
            $this->db->quote($this->isVisible(), 'integer') . ", " .
            $this->db->quote($this->isAccepted(), 'integer') . ", " .
            $this->db->quote($this->getRemark(), 'text') . " " .
            ")";
        $res = $this->db->manipulate($query);
    }

    public function delete() : void
    {
        ilTimingAccepted::_delete($this->getCourseId(), $this->getUserId());
    }

    public function _delete(int $a_crs_id, int $a_usr_id) : void
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = "DELETE FROM crs_timings_usr_accept " .
            "WHERE crs_id = " . $this->db->quote($a_crs_id, 'integer') . " " .
            "AND usr_id = " . $this->db->quote($a_usr_id, 'integer') . " ";
        $res = $this->db->manipulate($query);
    }

    public function _deleteByCourse(int $a_crs_id) : void
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = "DELETE FROM crs_timings_usr_accept " .
            "WHERE crs_id = " . $this->db->quote($a_crs_id, 'integer') . " ";
        $res = $this->db->manipulate($query);
    }

    public static function _deleteByUser(int $a_usr_id) : void
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = "DELETE FROM crs_timings_usr_accept " .
            "WHERE usr_id = " . $ilDB->quote($a_usr_id, 'integer') . "";
        $res = $ilDB->manipulate($query);
    }

    public function __read() : void
    {
        $query = "SELECT * FROM crs_timings_usr_accept " .
            "WHERE crs_id = " . $this->db->quote($this->getCourseId(), 'integer') . " " .
            "AND usr_id = " . $this->db->quote($this->getUserId(), 'integer') . "";
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->setVisible((bool) $row->visible);
            $this->setRemark((string) $row->remark);
            $this->accept((bool) $row->accept);
        }
    }
}
