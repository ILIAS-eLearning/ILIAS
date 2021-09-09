<?php declare(strict_types=1);
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilForumModerators
*
* @author Nadia Matuschek <nmatuschek@databay.de>
*
* @ingroup ModulesForum
*/
class ilForumModerators
{
    private $ref_id = 0;
    private $rbac;
    
    public function __construct($a_ref_id)
    {
        global $DIC;
        
        $this->rbac = $DIC->rbac();
        $this->ref_id = $a_ref_id;
    }

    /**
     * @param int $ref_id
     */
    public function setRefId($ref_id)
    {
        $this->ref_id = $ref_id;
    }

    /**
     * @return int
     */
    public function getRefId()
    {
        return $this->ref_id;
    }
    
    /**
     * @param $a_usr_id
     * @return bool
     */
    public function addModeratorRole($a_usr_id)
    {
        $a_rol_id = null;
        $role_list = $this->rbac->review()->getRoleListByObject($this->getRefId());
        foreach ($role_list as $role) {
            if (strpos($role['title'], 'il_frm_moderator') !== false) {
                $a_rol_id = $role['obj_id'];
                break;
            }
        }
        
        if ((int) $a_rol_id) {
            $user = $this->rbac->admin()->assignUser($a_rol_id, $a_usr_id);
            return true;
        }
    
        return false;
    }
    
    /**
     * @param $a_usr_id
     * @return bool
     */
    public function detachModeratorRole($a_usr_id)
    {
        $a_rol_id = null;
        $role_list = $this->rbac->review()->getRoleListByObject($this->getRefId());
        foreach ($role_list as $role) {
            if (strpos($role['title'], 'il_frm_moderator') !== false) {
                $a_rol_id = $role['obj_id'];
                break;
            }
        }
        
        if ((int) $a_rol_id) {
            $user = $this->rbac->admin()->deassignUser($a_rol_id, $a_usr_id);
            return true;
        }
    
        return false;
    }
    
    /**
     * @return array
     */
    public function getCurrentModerators() : array
    {
        $assigned_users = [];
        $roles = $this->rbac->review()->getRoleListByObject($this->getRefId());
        foreach ($roles as $role) {
            if (strpos($role['title'], 'il_frm_moderator') !== false) {
                $assigned_users = $this->rbac->review()->assignedUsers($role['rol_id']);
                break;
            }
        }
        return $assigned_users;
    }
    
    /**
     * @return array
     */
    public function getUsers() : array
    {
        $assigned_users = [];
        $roles = $this->rbac->review()->getRoleListByObject($this->getRefId());
        foreach ($roles as $role) {
            if (strpos($role['title'], 'il_frm_moderator') !== false) {
                $assigned_users = $this->rbac->review()->assignedUsers($role['rol_id']);
                break;
            }
        }
        return $assigned_users;
    }
}
