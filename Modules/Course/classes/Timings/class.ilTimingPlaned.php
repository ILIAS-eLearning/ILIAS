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
* class ilTimingPlaned
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*/


class ilTimingPlaned
{
    public $ilErr;
    public $ilDB;
    public $lng;

    /**
     * Constructor
     * @param int $item_id
     * @param int $a_usr_id
     */
    public function __construct($item_id, $a_usr_id)
    {
        global $DIC;

        $ilErr = $DIC['ilErr'];
        $ilDB = $DIC['ilDB'];
        $lng = $DIC['lng'];
        $tree = $DIC['tree'];

        $this->ilErr = &$ilErr;
        $this->db = &$ilDB;
        $this->lng = &$lng;

        $this->item_id = $item_id;
        $this->user_id = $a_usr_id;

        $this->__read();
    }
    
    public function getUserId()
    {
        return $this->user_id;
    }
    public function getItemId()
    {
        return $this->item_id;
    }

    public function getPlanedStartingTime()
    {
        return $this->start;
    }
    public function setPlanedStartingTime($a_time)
    {
        $this->start = $a_time;
    }
    public function getPlanedEndingTime()
    {
        return $this->end;
    }
    public function setPlanedEndingTime($a_end)
    {
        $this->end = $a_end;
    }

    public function validate()
    {
        include_once './Services/Object/classes/class.ilObjectActivation.php';
        $item = ilObjectActivation::getItem($this->getItemId());
        return true;
    }

    public function update()
    {
        ilTimingPlaned::_delete($this->getItemId(), $this->getUserId());
        $this->create();
        return true;
    }

    public function create()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "INSERT INTO crs_timings_planed (item_id,usr_id,planed_start,planed_end) " .
            "VALUES( " .
            $ilDB->quote($this->getItemId(), 'integer') . ", " .
            $ilDB->quote($this->getUserId(), 'integer') . ", " .
            $ilDB->quote($this->getPlanedStartingTime(), 'integer') . ", " .
            $ilDB->quote($this->getPlanedEndingTime(), 'integer') . " " .
            ")";
        $res = $ilDB->manipulate($query);
    }

    public function delete()
    {
        return ilTimingPlaned::_delete($this->getItemId(), $this->getUserId());
    }

    public static function _delete($a_item_id, $a_usr_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "DELETE FROM crs_timings_planed " .
            "WHERE item_id = " . $ilDB->quote($a_item_id, 'integer') . " " .
            "AND usr_id = " . $ilDB->quote($a_usr_id, 'integer') . " ";
        $res = $ilDB->manipulate($query);
    }

    // Static
    public static function _getPlanedTimings($a_usr_id, $a_item_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM crs_timings_planed " .
            "WHERE item_id = " . $ilDB->quote($a_item_id, 'integer') . " " .
            "AND usr_id = " . $ilDB->quote($a_usr_id, 'integer') . " ";
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $data['planed_start'] = $row->planed_start;
            $data['planed_end'] = $row->planed_end;
        }
        return $data ? $data : array();
    }


    public static function _getPlanedTimingsByItem($a_item_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM crs_timings_planed " .
            "WHERE item_id = " . $ilDB->quote($a_item_id, 'integer') . " ";
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $data[$row->usr_id]['start'] = $row->planed_start;
            $data[$row->usr_id]['end'] = $row->planed_end;
        }
        return $data ? $data : array();
    }

    public static function _deleteByItem($a_item_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "DELETE FROM crs_timings_planed " .
            "WHERE item_id = " . $ilDB->quote($a_item_id, 'integer') . " ";
        $res = $ilDB->manipulate($query);
    }

    public static function _deleteByUser($a_usr_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "DELETE FROM crs_timings_planed " .
            "WHERE usr_id = " . $ilDB->quote($a_usr_id, 'integer') . " ";
        $res = $ilDB->manipulate($query);
    }

    public function __read()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT * FROM crs_timings_planed " .
            "WHERE item_id = " . $ilDB->quote($this->getItemId(), 'integer') . " " .
            "AND usr_id = " . $ilDB->quote($this->getUserId(), 'integer') . " ";
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->setPlanedStartingTime($row->planed_start);
            $this->setPlanedEndingTime($row->planed_end);
        }
        return true;
    }
}
