<?php declare(strict_types=1);
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilForumModerators
 * @author  Nadia Matuschek <nmatuschek@databay.de>
 * @ingroup ModulesForum
 */
class ilForumModerators
{
    private int $ref_id;
    private \ILIAS\Services\RBAC\RBACServices $rbac;

    public function __construct(int $a_ref_id)
    {
        global $DIC;

        $this->rbac = $DIC->rbac();
        $this->ref_id = $a_ref_id;
    }

    public function setRefId(int $ref_id) : void
    {
        $this->ref_id = $ref_id;
    }

    public function getRefId() : int
    {
        return $this->ref_id;
    }

    public function addModeratorRole(int $a_usr_id) : bool
    {
        $a_rol_id = null;
        $role_list = $this->rbac->review()->getRoleListByObject($this->getRefId());
        foreach ($role_list as $role) {
            if (strpos($role['title'], 'il_frm_moderator') !== false) {
                $a_rol_id = (int) $role['obj_id'];
                break;
            }
        }

        if ($a_rol_id !== null) {
            $this->rbac->admin()->assignUser($a_rol_id, $a_usr_id);
            return true;
        }

        return false;
    }

    public function detachModeratorRole(int $a_usr_id) : bool
    {
        $a_rol_id = null;
        $role_list = $this->rbac->review()->getRoleListByObject($this->getRefId());
        foreach ($role_list as $role) {
            if (strpos($role['title'], 'il_frm_moderator') !== false) {
                $a_rol_id = (int) $role['obj_id'];
                break;
            }
        }

        if ($a_rol_id !== null) {
            $this->rbac->admin()->deassignUser($a_rol_id, $a_usr_id);
            return true;
        }

        return false;
    }

    /**
     * @return int[]
     */
    public function getCurrentModerators() : array
    {
        $assigned_users = [];
        $roles = $this->rbac->review()->getRoleListByObject($this->getRefId());
        foreach ($roles as $role) {
            if (strpos($role['title'], 'il_frm_moderator') !== false) {
                $assigned_users = array_map('intval', $this->rbac->review()->assignedUsers((int) $role['rol_id']));
                break;
            }
        }

        return $assigned_users;
    }

    /**
     * @return int[]
     */
    public function getUsers() : array
    {
        $assigned_users = [];
        $roles = $this->rbac->review()->getRoleListByObject($this->getRefId());
        foreach ($roles as $role) {
            if (strpos($role['title'], 'il_frm_moderator') !== false) {
                $assigned_users = array_map('intval', $this->rbac->review()->assignedUsers((int) $role['rol_id']));
                break;
            }
        }

        return $assigned_users;
    }
}
