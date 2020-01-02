<?php
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
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*/


class ilTimingAccepted
{
    public $ilErr;
    public $ilDB;
    public $lng;

    /**
     * Constructor
     * @param int $crs_id
     * @param int $a_usr_id
     */
    public function __construct($crs_id, $a_usr_id)
    {
        global $DIC;

        $ilErr = $DIC['ilErr'];
        $ilDB = $DIC['ilDB'];
        $lng = $DIC['lng'];
        $tree = $DIC['tree'];

        $this->ilErr =&$ilErr;
        $this->db  =&$ilDB;
        $this->lng =&$lng;

        $this->crs_id = $crs_id;
        $this->user_id = $a_usr_id;

        $this->__read();
    }
    
    public function getUserId()
    {
        return $this->user_id;
    }
    public function getCourseId()
    {
        return $this->crs_id;
    }
    public function accept($a_status)
    {
        $this->accepted = $a_status;
    }
    public function isAccepted()
    {
        return $this->accepted ? true : false;
    }
    public function setRemark($a_remark)
    {
        $this->remark = $a_remark;
    }
    public function getRemark()
    {
        return $this->remark;
    }
    public function setVisible($a_visible)
    {
        $this->visible = $a_visible;
    }
    public function isVisible()
    {
        return $this->visible ? true : false;
    }

    public function update()
    {
        ilTimingAccepted::_delete($this->getCourseId(), $this->getUserId());
        $this->create();
        return true;
    }

    public function create()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "INSERT INTO crs_timings_usr_accept (crs_id,usr_id,visible,accept,remark) " .
            "VALUES( " .
            $ilDB->quote($this->getCourseId(), 'integer') . ", " .
            $ilDB->quote($this->getUserId(), 'integer') . ", " .
            $ilDB->quote($this->isVisible(), 'integer') . ", " .
            $ilDB->quote($this->isAccepted(), 'integer') . ", " .
            $ilDB->quote($this->getRemark(), 'text') . " " .
            ")";
        $res = $ilDB->manipulate($query);
    }

    public function delete()
    {
        return ilTimingAccepted::_delete($this->getCourseId(), $this->getUserId());
    }

    public function _delete($a_crs_id, $a_usr_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "DELETE FROM crs_timings_usr_accept " .
            "WHERE crs_id = " . $ilDB->quote($a_crs_id, 'integer') . " " .
            "AND usr_id = " . $ilDB->quote($a_usr_id, 'integer') . " ";
        $res = $ilDB->manipulate($query);
    }

    public function _deleteByCourse($a_crs_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "DELETE FROM crs_timings_usr_accept " .
            "WHERE crs_id = " . $ilDB->quote($a_crs_id, 'integer') . " ";
        $res = $ilDB->manipulate($query);
    }

    public static function _deleteByUser($a_usr_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "DELETE FROM crs_timings_usr_accept " .
            "WHERE usr_id = " . $ilDB->quote($a_usr_id, 'integer') . "";
        $res = $ilDB->manipulate($query);
    }

    public function __read()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT * FROM crs_timings_usr_accept " .
            "WHERE crs_id = " . $ilDB->quote($this->getCourseId(), 'integer') . " " .
            "AND usr_id = " . $ilDB->quote($this->getUserId(), 'integer') . "";
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->setVisible($row->visible);
            $this->setRemark($row->remark);
            $this->accept($row->accept);
        }
        return true;
    }
}
