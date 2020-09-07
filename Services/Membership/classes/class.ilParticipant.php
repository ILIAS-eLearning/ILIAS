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


include_once './Services/Membership/classes/class.ilParticipants.php';

/**
* Base class for course and group participant
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ServicesMembership
*/
abstract class ilParticipant
{
    const MEMBERSHIP_ADMIN = 1;
    const MEMBERSHIP_TUTOR = 2;
    const MEMBERSHIP_MEMBER = 3;
    
    
    private $obj_id = 0;
    private $usr_id = 0;
    protected $type = '';
    private $ref_id = 0;
    
    private $component = '';

    private $participants = false;
    private $admins = false;
    private $tutors = false;
    private $members = false;
    
    private $numMembers = null;

    private $member_roles = [];

    private $participants_status = array();

    /**
     * Singleton Constructor
     *
     * @access protected
     * @param int obj_id of container
     */
    protected function __construct($a_component_name, $a_obj_id, $a_usr_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $lng = $DIC['lng'];
        
        $this->obj_id = $a_obj_id;
        $this->usr_id = $a_usr_id;
        $this->type = ilObject::_lookupType($a_obj_id);
        $ref_ids = ilObject::_getAllReferences($this->obj_id);
        $this->ref_id = current($ref_ids);
        
        $this->component = $a_component_name;
        
        $this->readParticipant();
        $this->readParticipantStatus();
    }
    
    /**
     * Update member roles
     * @global ilDB $ilDB
     * @param type $a_obj_id
     * @param type $a_role_id
     * @param type $a_status
     */
    public static function updateMemberRoles($a_obj_id, $a_usr_id, $a_role_id, $a_status)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $a_membership_role_type = self::getMembershipRoleType($a_role_id);
        
        switch ($a_membership_role_type) {
            case self::MEMBERSHIP_ADMIN:
                $update_fields = array('admin' => array('integer', $a_status ? 1 : 0));
                $update_string = ('admin = ' . $ilDB->quote($a_status ? 1 : 0, 'integer'));
                break;
            
            case self::MEMBERSHIP_TUTOR:
                $update_fields = array('tutor' => array('integer', $a_status ? 1 : 0));
                $update_string = ('tutor = ' . $ilDB->quote($a_status ? 1 : 0, 'integer'));
                break;

            case self::MEMBERSHIP_MEMBER:
            default:
                $current_status = self::lookupStatusByMembershipRoleType($a_obj_id, $a_usr_id, $a_membership_role_type);

                if ($a_status) {
                    $new_status = $current_status + 1;
                }
                if (!$a_status) {
                    $new_status = $current_status - 1;
                    if ($new_status < 0) {
                        $new_status = 0;
                    }
                }
                
                $update_fields = array('member' => array('integer', $new_status));
                $update_string = ('member = ' . $ilDB->quote($new_status, 'integer'));
                break;
        }
        
        $query = 'SELECT count(*) num FROM obj_members  ' .
                'WHERE obj_id = ' . $ilDB->quote($a_obj_id, 'integer') . ' ' .
                'AND usr_id = ' . $ilDB->quote($a_usr_id, 'integer');
        $res = $ilDB->query($query);
        
        $found = false;
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            if ($row->num) {
                $found = true;
            }
        }
        if (!$found) {
            $ilDB->replace(
                'obj_members',
                array(
                        'obj_id' => array('integer',$a_obj_id),
                        'usr_id' => array('integer',$a_usr_id)
                    ),
                $update_fields
            );
        } else {
            $query = 'UPDATE obj_members SET ' .
                    $update_string . ' ' .
                    'WHERE obj_id = ' . $ilDB->quote($a_obj_id, 'integer') . ' ' .
                    'AND usr_id = ' . $ilDB->quote($a_usr_id, 'integer');
            
            $ilDB->manipulate($query);
        }
        
        $query = 'DELETE from obj_members ' .
            'WHERE obj_id = ' . $ilDB->quote($a_obj_id, 'integer') . ' ' .
            'AND usr_id = ' . $ilDB->quote($a_usr_id, 'integer') . ' ' .
            'AND admin = ' . $ilDB->quote(0, 'integer') . ' ' .
            'AND tutor = ' . $ilDB->quote(0, 'integer') . ' ' .
            'AND member = ' . $ilDB->quote(0, 'integer');
        $ilDB->manipulate($query);
    }

    /**
     *
     * @param type $a_role_id
     */
    public static function getMembershipRoleType($a_role_id)
    {
        $title = ilObject::_lookupTitle($a_role_id);
        switch (substr($title, 0, 8)) {
            case 'il_crs_a':
            case 'il_grp_a':
                return self::MEMBERSHIP_ADMIN;
                
            case 'il_crs_t':
                return self::MEMBERSHIP_TUTOR;
                
            case 'il_crs_m':
            default:
                return self::MEMBERSHIP_MEMBER;
                
        }
    }
    
    /**
     * lookup assignment status
     * @global ilDB $ilDB
     * @param type $a_obj_id
     * @param type $a_usr_id
     * @param type $a_membership_role_type
     * @return int
     */
    public static function lookupStatusByMembershipRoleType($a_obj_id, $a_usr_id, $a_membership_role_type)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'SELECT * FROM obj_members ' .
                'WHERE obj_id = ' . $ilDB->quote($a_obj_id, 'integer') . ' ' .
                'AND usr_id = ' . $ilDB->quote($a_usr_id) . ' ';
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            switch ($a_membership_role_type) {
                case self::MEMBERSHIP_ADMIN:
                    return $row->admin;
                    
                case self::MEMBERSHIP_TUTOR:
                    return $row->tutor;
                    
                case self::MEMBERSHIP_MEMBER:
                    return $row->member;
            }
        }
        return 0;
    }
    
    
    /**
     * Get component name
     * Used for event handling
     * @return type
     */
    protected function getComponent()
    {
        return $this->component;
    }

    /**
     * get user id
     * @return int
     */
    public function getUserId()
    {
        return $this->usr_id;
    }

    public function isBlocked()
    {
        return (bool) $this->participants_status[$this->getUserId()]['blocked'];
    }
    
    // cognos-blu-patch: begin
    /**
     * Check if user is contact for current object
     * @return bool
     */
    public function isContact()
    {
        return (bool) $this->participants_status[$this->getUserId()]['contact'];
    }
    

    public function isAssigned()
    {
        return (bool) $this->participants;
    }

    public function isMember()
    {
        return (bool) $this->members;
    }

    public function isAdmin()
    {
        return $this->admins;
    }

    public function isTutor()
    {
        return (bool) $this->tutors;
    }

    public function isParticipant()
    {
        return (bool) $this->participants;
    }
    
    public function getNumberOfMembers()
    {
        global $DIC;

        $rbacreview = $DIC['rbacreview'];

        if ($this->numMembers === null) {
            $this->numMembers = $rbacreview->getNumberOfAssignedUsers($this->member_roles);
        }
        return $this->numMembers;
    }
    

    /**
     * Read participant
     * @return void
     */
    protected function readParticipant()
    {
        global $DIC;

        $rbacreview = $DIC['rbacreview'];
        $ilObjDataCache = $DIC['ilObjDataCache'];

        $this->roles = $rbacreview->getRolesOfRoleFolder($this->ref_id, false);

        $users = array();
        $this->participants = array();
        $this->members = $this->admins = $this->tutors = array();
        $this->member_roles = [];

        foreach ($this->roles as $role_id) {
            $title = $ilObjDataCache->lookupTitle($role_id);
            switch (substr($title, 0, 8)) {
                case 'il_crs_m':
                    $this->member_roles[] = $role_id;
                    $this->role_data[IL_CRS_MEMBER] = $role_id;
                    if ($rbacreview->isAssigned($this->getUserId(), $role_id)) {
                        $this->participants = true;
                        $this->members = true;
                    }
                    break;

                case 'il_crs_a':
                    $this->role_data[IL_CRS_ADMIN] = $role_id;
                    if ($rbacreview->isAssigned($this->getUserId(), $role_id)) {
                        $this->participants = true;
                        $this->admins = true;
                    }
                    break;

                case 'il_crs_t':
                    $this->role_data[IL_CRS_TUTOR] = $role_id;
                    if ($rbacreview->isAssigned($this->getUserId(), $role_id)) {
                        $this->participants = true;
                        $this->tutors = true;
                    }
                    break;

                case 'il_grp_a':
                    $this->role_data[IL_GRP_ADMIN] = $role_id;
                    if ($rbacreview->isAssigned($this->getUserId(), $role_id)) {
                        $this->participants = true;
                        $this->admins = true;
                    }
                    break;

                case 'il_grp_m':
                    $this->member_roles[] = $role_id;
                    $this->role_data[IL_GRP_MEMBER] = $role_id;
                    if ($rbacreview->isAssigned($this->getUserId(), $role_id)) {
                        $this->participants = true;
                        $this->members = true;
                    }
                    break;

                default:
                    
                    $this->member_roles[] = $role_id;
                    if ($rbacreview->isAssigned($this->getUserId(), $role_id)) {
                        $this->participants = true;
                        $this->members = true;
                    }
                    break;
            }
        }
    }

    /**
     * Read participant status
     * @global ilDB $ilDB
     */
    protected function readParticipantStatus()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM obj_members " .
            "WHERE obj_id = " . $ilDB->quote($this->obj_id, 'integer') . " " .
            'AND usr_id = ' . $ilDB->quote($this->getUserId(), 'integer');

        $res = $ilDB->query($query);
        $this->participants_status = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->participants_status[$this->getUserId()]['blocked'] = $row->blocked;
            $this->participants_status[$this->getUserId()]['notification'] = $row->notification;
            $this->participants_status[$this->getUserId()]['passed'] = $row->passed;
            // cognos-blu-patch: begin
            $this->participants_status[$this->getUserId()]['contact'] = $row->contact;
            // cognos-blu-patch: end
        }
    }
    
    /**
     * Add user to course/group
     *
     * @access public
     * @param int user id
     * @param int role IL_CRS_ADMIN || IL_CRS_TUTOR || IL_CRS_MEMBER
     *
     * global ilRbacReview $rbacreview
     *
     */
    public function add($a_usr_id, $a_role)
    {
        global $DIC;

        $rbacadmin = $DIC['rbacadmin'];
        $ilLog = $DIC->logger()->mmbr();
        $ilAppEventHandler = $DIC['ilAppEventHandler'];
        $rbacreview = $DIC['rbacreview'];
        

        if ($rbacreview->isAssignedToAtLeastOneGivenRole($a_usr_id, $this->roles)) {
            return false;
        }
        
        switch ($a_role) {
            case IL_CRS_ADMIN:
                $this->admins = true;
                break;

            case IL_CRS_TUTOR:
                $this->tutors = true;
                break;

            case IL_CRS_MEMBER:
                $this->members = true;
                break;
                
            case IL_GRP_ADMIN:
                $this->admins = true;
                break;
                
            case IL_GRP_MEMBER:
                $this->members = true;
                break;
        }

        $rbacadmin->assignUser($this->role_data[$a_role], $a_usr_id);
        $this->addDesktopItem($a_usr_id);
        
        // Delete subscription request
        $this->deleteSubscriber($a_usr_id);
        
        include_once './Services/Membership/classes/class.ilWaitingList.php';
        ilWaitingList::deleteUserEntry($a_usr_id, $this->obj_id);

        $ilLog->debug(': Raise new event: ' . $this->getComponent() . ' addParticipant');
        $ilAppEventHandler->raise(
            $this->getComponent(),
            "addParticipant",
            array(
                    'obj_id' => $this->obj_id,
                    'usr_id' => $a_usr_id,
                    'role_id' => $a_role)
        );
        return true;
    }
    
    /**
     * Drop user from all roles
     *
     * @access public
     * @param int usr_id
     *
     */
    public function delete($a_usr_id)
    {
        global $DIC;

        $rbacadmin = $DIC['rbacadmin'];
        $ilDB = $DIC['ilDB'];
        $ilAppEventHandler = $DIC['ilAppEventHandler'];
        
        $this->dropDesktopItem($a_usr_id);
        foreach ($this->roles as $role_id) {
            $rbacadmin->deassignUser($role_id, $a_usr_id);
        }
        
        $query = "DELETE FROM obj_members " .
            "WHERE usr_id = " . $ilDB->quote($a_usr_id, 'integer') . " " .
            "AND obj_id = " . $ilDB->quote($this->obj_id, 'integer');
        $res = $ilDB->manipulate($query);
        
        $ilAppEventHandler->raise(
            $this->getComponent(),
            "deleteParticipant",
            array(
                    'obj_id' => $this->obj_id,
                    'usr_id' => $a_usr_id)
        );
        return true;
    }
    
    /**
     * Delete subsciber
     *
     * @access public
     */
    public function deleteSubscriber($a_usr_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "DELETE FROM il_subscribers " .
            "WHERE usr_id = " . $ilDB->quote($a_usr_id, 'integer') . " " .
            "AND obj_id = " . $ilDB->quote($this->obj_id, 'integer') . " ";
        $res = $ilDB->manipulate($query);

        return true;
    }
    
    /**
     * Add desktop item
     *
     * @access public
     * @param int usr_id
     *
     */
    public function addDesktopItem($a_usr_id)
    {
        if (!ilObjUser::_isDesktopItem($a_usr_id, $this->ref_id, $this->type)) {
            ilObjUser::_addDesktopItem($a_usr_id, $this->ref_id, $this->type);
        }
        return true;
    }
    
    /**
     * Drop desktop item
     *
     * @access public
     * @param int usr_id
     *
     */
    public function dropDesktopItem($a_usr_id)
    {
        if (ilObjUser::_isDesktopItem($a_usr_id, $this->ref_id, $this->type)) {
            ilObjUser::_dropDesktopItem($a_usr_id, $this->ref_id, $this->type);
        }

        return true;
    }
    
    // cognos-blu-patch: begin
    /**
     *
     * @global ilDB $ilDB
     * @param type $a_usr_id
     * @param type $a_contact
     * @return boolean
     */
    public function updateContact($a_usr_id, $a_contact)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $ilDB->manipulate(
            'UPDATE obj_members SET ' .
                'contact = ' . $ilDB->quote($a_contact, 'integer') . ' ' .
                'WHERE obj_id = ' . $ilDB->quote($this->obj_id, 'integer') . ' ' .
                'AND usr_id = ' . $ilDB->quote($a_usr_id, 'integer')
        );
        
        $this->participants_status[$a_usr_id]['contact'] = $a_contact;
        return true;
    }
    // cognos-blu-patch: end
    
    /**
     * Update notification status
     *
     * @access public
     * @param int usr_id
     * @param bool passed
     *
     */
    public function updateNotification($a_usr_id, $a_notification)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $this->participants_status[$a_usr_id]['notification'] = (int) $a_notification;

        $query = "SELECT * FROM obj_members " .
            "WHERE obj_id = " . $ilDB->quote($this->obj_id, 'integer') . " " .
            "AND usr_id = " . $ilDB->quote($a_usr_id, 'integer');
        $res = $ilDB->query($query);
        if ($res->numRows()) {
            $query = "UPDATE obj_members SET " .
                "notification = " . $ilDB->quote((int) $a_notification, 'integer') . " " .
                "WHERE obj_id = " . $ilDB->quote($this->obj_id, 'integer') . " " .
                "AND usr_id = " . $ilDB->quote($a_usr_id, 'integer');
        } else {
            $query = "INSERT INTO obj_members (notification,obj_id,usr_id,passed,blocked) " .
                "VALUES ( " .
                $ilDB->quote((int) $a_notification, 'integer') . ", " .
                $ilDB->quote($this->obj_id, 'integer') . ", " .
                $ilDB->quote($a_usr_id, 'integer') . ", " .
                $ilDB->quote(0, 'integer') . ", " .
                $ilDB->quote(0, 'integer') .
                ")";
        }
        $res = $ilDB->manipulate($query);
        return true;
    }
    
    /**
     * Check if user for deletion are last admins
     *
     * @access public
     * @param array array of user ids for deletion
     *
     */
    public function checkLastAdmin($a_usr_ids)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $admin_role_id =
            $this->type == 'crs' ?
            $this->role_data[IL_CRS_ADMIN] :
            $this->role_data[IL_GRP_ADMIN];
        
        
        $query = "
		SELECT			COUNT(rolesusers.usr_id) cnt
		
		FROM			object_data rdata
		
		LEFT JOIN		rbac_ua  rolesusers		
		ON				rolesusers.rol_id = rdata.obj_id
		
		WHERE			rdata.obj_id = %s
		";
        
        $query .= ' AND ' . $ilDB->in('rolesusers.usr_id', $a_usr_ids, true, 'integer');
        $res = $ilDB->queryF($query, array('integer'), array($admin_role_id));

        $data = $ilDB->fetchAssoc($res);
                    
        return (int) $data['cnt'] > 0;
    }
}
