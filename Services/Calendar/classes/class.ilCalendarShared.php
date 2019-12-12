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
* Handles shared calendars
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ServicesCalendar
*/

class ilCalendarShared
{
    const TYPE_USR = 1;
    const TYPE_ROLE = 2;
    
    private $calendar_id;
    
    private $shared = array();
    private $shared_users = array();
    private $shared_roles = array();
    
    protected $db;


    /**
     * constructor
     *
     * @access public
     * @param int calendar id
     */
    public function __construct($a_calendar_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $this->calendar_id = $a_calendar_id;
        $this->db = $ilDB;
        $this->read();
    }
    
    /**
     * Delete all entries for a specific calendar id
     *
     * @access public
     * @param
     * @return
     * @static
     */
    public static function deleteByCalendar($a_cal_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "DELETE FROM cal_shared WHERE cal_id = " . $ilDB->quote($a_cal_id, 'integer') . " ";
        $res = $ilDB->manipulate($query);
        return true;
    }
    
    /**
     * Delete all entries for a specific user
     *
     * @access public
     * @param int usr_id
     * @return
     * @static
     */
    public static function deleteByUser($a_user_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "DELETE FROM cal_shared WHERE obj_id = " . $ilDB->quote($a_user_id, 'integer') . " ";
        $res = $ilDB->manipulate($query);
        return true;
        
        // TODO: delete also cal_shared_user_status
    }
    
    /**
     * is shared with user
     *
     * @access public
     * @param int usr_id
     * @param int calendar id
     * @return bool
     * @static
     */
    public static function isSharedWithUser($a_usr_id, $a_calendar_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $rbacreview = $DIC['rbacreview'];
        
        $query = 'SELECT * FROM cal_shared ' .
            "WHERE cal_id = " . $ilDB->quote($a_calendar_id, 'integer') . " ";
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $obj_ids[$row->obj_id] = $row->obj_type;
        }
        $assigned_roles = $rbacreview->assignedRoles($a_usr_id);
        foreach ($obj_ids as $id => $type) {
            switch ($type) {
                case self::TYPE_USR:
                    if ($a_usr_id == $id) {
                        return true;
                    }
                    break;
                case self::TYPE_ROLE:
                    if (in_array($id, $assigned_roles)) {
                        return true;
                    }
                    break;
            }
        }
        return false;
    }
    
    /**
     * get shared calendars of user
     *
     * @access public
     * @param int user id
     * @return array shared calendar info
     * @static
     */
    public static function getSharedCalendarsForUser($a_usr_id  = 0)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $ilUser = $DIC['ilUser'];
        $rbacreview = $DIC['rbacreview'];
        
        if (!$a_usr_id) {
            $a_usr_id = $ilUser->getId();
        }

        $query = "SELECT * FROM cal_shared " .
            "WHERE obj_type = " . $ilDB->quote(self::TYPE_USR, 'integer') . " " .
            "AND obj_id = " . $ilDB->quote($a_usr_id, 'integer') . " " .
            "ORDER BY create_date";
        $res = $ilDB->query($query);
        $calendars = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $calendars[] = $row->cal_id;
            
            $shared[$row->cal_id]['cal_id'] = $row->cal_id;
            $shared[$row->cal_id]['create_date'] = $row->create_date;
            $shared[$row->cal_id]['obj_type'] = $row->obj_type;
        }
        
        $assigned_roles = $rbacreview->assignedRoles($ilUser->getId());
        
        $query = "SELECT * FROM cal_shared " .
            "WHERE obj_type = " . $ilDB->quote(self::TYPE_ROLE, 'integer') . " " .
            "AND " . $ilDB->in('obj_id', $assigned_roles, false, 'integer');

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            if (in_array($row->cal_id, $calendars)) {
                continue;
            }
            if (ilCalendarCategories::_isOwner($ilUser->getId(), $row->cal_id)) {
                continue;
            }
            
            $shared[$row->cal_id]['cal_id'] = $row->cal_id;
            $shared[$row->cal_id]['create_date'] = $row->create_date;
            $shared[$row->cal_id]['obj_type'] = $row->obj_type;
        }
        
            
        
        return $shared ? $shared : array();
        // TODO: return also role calendars
    }
    
    /**
     * get calendar id
     *
     * @access public
     * @return int calendar id
     */
    public function getCalendarId()
    {
        return $this->calendar_id;
    }
    
    /**
     * get shared
     *
     * @access public
     * @return array
     */
    public function getShared()
    {
        return $this->shared ? $this->shared : array();
    }
    
    /**
     * get users
     *
     * @access public
     * @return array
     */
    public function getUsers()
    {
        return $this->shared_users ? $this->shared_users : array();
    }
    
    /**
     * get roles
     *
     * @access public
     * @return array
     */
    public function getRoles()
    {
        return $this->shared_roles ? $this->shared_roles : array();
    }
    
    /**
     * Check if calendar is already shared with specific user or role
     *
     * @access public
     * @param int obj_id
     * @return bool
     */
    public function isShared($a_obj_id)
    {
        return isset($this->shared[$a_obj_id]);
    }
    
    /**
     * Check if calendar is editable for user
     * @param type $a_user_id
     */
    public function isEditableForUser($a_user_id)
    {
        foreach ((array) $this->shared as $info) {
            if (!$info['writable']) {
                continue;
            }
            
            switch ($info['obj_type']) {
                case self::TYPE_USR:
                    if ($info['obj_id'] == $a_user_id) {
                        return true;
                    }
                    break;
                    
                case self::TYPE_ROLE:
                    if ($GLOBALS['DIC']['rbacreview']->isAssigned($a_user_id, $info['obj_id'])) {
                        return true;
                    }
                    break;
            }
        }
        return false;
    }
    
    /**
     * share calendar
     *
     * @access public
     * @param int obj_id
     * @param int type
     * @return bool
     */
    public function share($a_obj_id, $a_type, $a_writable = false)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if ($this->isShared($a_obj_id)) {
            return false;
        }
        $query = "INSERT INTO cal_shared (cal_id,obj_id,obj_type,create_date,writable) " .
            "VALUES ( " .
            $this->db->quote($this->getCalendarId(), 'integer') . ", " .
            $this->db->quote($a_obj_id, 'integer') . ", " .
            $this->db->quote($a_type, 'integer') . ", " .
            $ilDB->now() . ", " .
            $this->db->quote((int) $a_writable, 'integer') . ' ' .
            ")";
        
        $res = $ilDB->manipulate($query);
        
        $this->read();
        return true;
    }
    
    /**
     * stop sharing
     *
     * @access public
     * @param int obj_id
     * @return bool
     */
    public function stopSharing($a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if (!$this->isShared($a_obj_id)) {
            return false;
        }
        $query = "DELETE FROM cal_shared WHERE cal_id = " . $this->db->quote($this->getCalendarId(), 'integer') . " " .
            "AND obj_id = " . $this->db->quote($a_obj_id, 'integer') . " ";
        $res = $ilDB->manipulate($query);
        
        include_once('./Services/Calendar/classes/class.ilCalendarSharedStatus.php');
        ilCalendarSharedStatus::deleteStatus($a_obj_id, $this->getCalendarId());
        
        
        $this->read();
        return true;
    }
    
    /**
     * read shared calendars
     *
     * @access protected
     * @return
     */
    protected function read()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $this->shared = $this->shared_users = $this->shared_roles = array();
        
        $query = "SELECT * FROM cal_shared WHERE cal_id = " . $this->db->quote($this->getCalendarId(), 'integer');
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            switch ($row->obj_type) {
                case self::TYPE_USR:
                    $this->shared_users[$row->obj_id]['obj_id'] = $row->obj_id;
                    $this->shared_users[$row->obj_id]['obj_type'] = $row->obj_type;
                    $this->shared_users[$row->obj_id]['create_date'] = $row->create_date;
                    $this->shared_users[$row->obj_id]['writable'] = $row->writable;
                    break;
                
                
                case self::TYPE_ROLE:
                    $this->shared_roles[$row->obj_id]['obj_id'] = $row->obj_id;
                    $this->shared_roles[$row->obj_id]['obj_type'] = $row->obj_type;
                    $this->shared_roles[$row->obj_id]['create_date'] = $row->create_date;
                    $this->shared_role[$row->obj_id]['writable'] = $row->writable;
                    break;
                    
            }
            
            $this->shared[$row->obj_id]['obj_id'] = $row->obj_id;
            $this->shared[$row->obj_id]['obj_type'] = $row->obj_type;
            $this->shared[$row->obj_id]['create_date'] = $row->create_date;
            $this->shared[$row->obj_id]['writable'] = $row->writable;
        }
        return true;
    }
}
