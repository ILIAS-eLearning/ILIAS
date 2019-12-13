<?php
/*
        +-----------------------------------------------------------------------------+
        | ILIAS open source                                                           |
        +-----------------------------------------------------------------------------+
        | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ServicesCalendar
*/

class ilCalendarCategoryAssignments
{
    protected $db;
    
    protected $cal_entry_id = 0;
    protected $assignments = array();

    /**
     * Constructor
     *
     * @access public
     * @param int calendar entry id
     */
    public function __construct($a_cal_entry_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $this->db = $ilDB;
        $this->cal_entry_id = $a_cal_entry_id;
        
        $this->read();
    }
    
    /**
     * lookup categories
     *
     * @access public
     * @param int cal_id
     * @return array of categories
     * @static
     */
    public static function _lookupCategories($a_cal_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT cat_id FROM cal_cat_assignments " .
            "WHERE cal_id = " . $ilDB->quote($a_cal_id, 'integer') . " ";
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $cat_ids[] = $row->cat_id;
        }
        return $cat_ids ? $cat_ids : array();
    }
    
    /**
     * Lookup category id
     *
     * @access public
     * @param
     * @return
     * @static
     */
    public static function _lookupCategory($a_cal_id)
    {
        if (count($cats = self::_lookupCategories($a_cal_id))) {
            return $cats[0];
        }
        return 0;
    }

    /**
     * lookup calendars for appointment ids
     *
     * @access public
     * @param	array	$a_cal_ids
     * @static
     */
    public static function _getAppointmentCalendars($a_cal_ids)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT * FROM cal_cat_assignments " .
            "WHERE " . $ilDB->in('cal_id', $a_cal_ids, false, 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $map[$row->cal_id] = $row->cat_id;
        }
        return $map ? $map : array();
    }
    
    /**
     * Get assigned apointments
     *
     * @access public
     * @param	array	$a_cat_id
     * @static
     */
    public static function _getAssignedAppointments($a_cat_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT * FROM cal_cat_assignments " .
            "WHERE " . $ilDB->in('cat_id', $a_cat_id, false, 'integer');

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $cal_ids[] = $row->cal_id;
        }
        return $cal_ids ? $cal_ids : array();
    }
    
    /**
     * Get number of assigned appoitments
     * @param type $a_cat_id
     */
    public static function lookupNumberOfAssignedAppointments($a_cat_ids)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = 'SELECT COUNT(*) num FROM cal_cat_assignments ' .
                'WHERE ' . $ilDB->in('cat_id', $a_cat_ids, false, 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->num;
        }
        return 0;
    }

    /**
     * get automatic generated appointments of category
     *
     * @access public
     * @param int obj_id
     * @return
     * @static
     */
    public static function _getAutoGeneratedAppointmentsByObjId($a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT ce.cal_id FROM cal_categories cc " .
            "JOIN cal_cat_assignments cca ON cc.cat_id = cca.cat_id " .
            "JOIN cal_entries ce ON cca.cal_id = ce.cal_id " .
            "WHERE auto_generated = 1 " .
            "AND obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " ";
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $apps[] = $row->cal_id;
        }
        return $apps ? $apps : array();
    }
    
    /**
     * Delete appointment assignment
     *
     * @access public
     * @param int appointment id
     * @static
     */
    public static function _deleteByAppointmentId($a_app_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "DELETE FROM cal_cat_assignments " .
            "WHERE cal_id = " . $ilDB->quote($a_app_id, 'integer') . " ";
        $res = $ilDB->manipulate($query);
        
        return true;
    }
    
    /**
     * Delete assignments by category id
     *
     * @access public
     * @param int category_id
     * @return
     * @static
     */
    public static function _deleteByCategoryId($a_cat_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "DELETE FROM cal_cat_assignments " .
            "WHERE cat_id = " . $ilDB->quote($a_cat_id, 'integer') . " ";
        $res = $ilDB->manipulate($query);
        return true;
    }
    
    /**
     * get first assignment
     *
     * @access public
     * @return
     */
    public function getFirstAssignment()
    {
        return isset($this->assignments[0]) ? $this->assignments[0] : false;
    }
    
    /**
     * get assignments
     *
     * @access public
     * @return
     */
    public function getAssignments()
    {
        return $this->assignments ? $this->assignments : array();
    }
    
    /**
     * add assignment
     *
     * @access public
     * @param int calendar category id
     * @return
     */
    public function addAssignment($a_cal_cat_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "INSERT INTO cal_cat_assignments (cal_id,cat_id) " .
            "VALUES ( " .
            $this->db->quote($this->cal_entry_id, 'integer') . ", " .
            $this->db->quote($a_cal_cat_id, 'integer') . " " .
            ")";
        $res = $ilDB->manipulate($query);
        $this->assignments[] = (int) $a_cal_cat_id;
        
        return true;
    }
    
    /**
     * delete assignment
     *
     * @access public
     * @param int calendar category id
     * @return
     */
    public function deleteAssignment($a_cat_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "DELETE FROM cal_cat_assignments " .
            "WHERE cal_id = " . $this->db->quote($this->cal_entry_id, 'integer') . ", " .
            "AND cat_id = " . $this->db->quote($a_cat_id, 'integer') . " ";
        $res = $ilDB->manipulate($query);
        
        if (($key = array_search($a_cat_id, $this->assignments)) !== false) {
            unset($this->assignments[$key]);
        }
        return true;
    }
    
    /**
     * delete assignments
     *
     * @access public
     */
    public function deleteAssignments()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "DELETE FROM cal_cat_assignments " .
            "WHERE cal_id = " . $this->db->quote($this->cal_entry_id, 'integer') . " ";
        $res = $ilDB->manipulate($query);
        return true;
    }

    
    /**
     * read assignments
     *
     * @access private
     * @return
     */
    private function read()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM cal_cat_assignments " .
            "WHERE cal_id = " . $this->db->quote($this->cal_entry_id, 'integer') . " ";
        
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->assignments[] = $row->cat_id;
        }
    }
}
