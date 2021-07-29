<?php

/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Skill\Access;

/**
 * Skill tree access
 * @author Alexander Killing <killing@leifos.de>
 */
class SkillTreeAccess
{
    /**
     * @var \ilRbacSystem
     */
    protected $access;

    /**
     * @var int
     */
    protected $obj_skill_tree_ref_id;

    /**
     * @var int
     */
    protected $usr_id;

    /**
     * Constructor
     */
    public function __construct(\ilRbacSystem $access, int $obj_skill_tree_ref_id, int $usr_id)
    {
        $this->access = $access;
        $this->obj_skill_tree_ref_id = $obj_skill_tree_ref_id;
        $this->usr_id = $usr_id;
    }

    /**
     * @param int $a_usr_id
     * @return bool
     */
    public function hasReadTreePermission(int $a_usr_id = 0) : bool
    {
        if ($a_usr_id == 0) {
            $a_usr_id = $this->usr_id;
        }
        return $this->access->checkAccessOfUser($a_usr_id, "visible,read", $this->obj_skill_tree_ref_id);
    }

    /**
     * @param int $a_usr_id
     * @return bool
     */
    public function hasEditTreeSettingsPermission(int $a_usr_id = 0) : bool
    {
        if ($a_usr_id == 0) {
            $a_usr_id = $this->usr_id;
        }
        return $this->access->checkAccessOfUser($a_usr_id, "write", $this->obj_skill_tree_ref_id);
    }

    /**
     * @param int $a_usr_id
     * @return bool
     */
    public function hasEditTreePermissionsPermission(int $a_usr_id = 0) : bool
    {
        if ($a_usr_id == 0) {
            $a_usr_id = $this->usr_id;
        }
        return $this->access->checkAccessOfUser($a_usr_id, "edit_permission", $this->obj_skill_tree_ref_id);
    }

    /**
     * @param int $a_usr_id
     * @return bool
     */
    public function hasReadCompetencesPermission(int $a_usr_id = 0) : bool
    {
        if ($a_usr_id == 0) {
            $a_usr_id = $this->usr_id;
        }
        return $this->access->checkAccessOfUser($a_usr_id, "read_comp", $this->obj_skill_tree_ref_id);
    }

    /**
     * @param int $a_usr_id
     * @return bool
     */
    public function hasManageCompetencesPermission(int $a_usr_id = 0) : bool
    {
        if ($a_usr_id == 0) {
            $a_usr_id = $this->usr_id;
        }
        return $this->access->checkAccessOfUser($a_usr_id, "manage_comp", $this->obj_skill_tree_ref_id);
    }

    /**
     * @param int $a_usr_id
     * @return bool
     */
    public function hasManageCompetenceTemplatesPermission(int $a_usr_id = 0) : bool
    {
        if ($a_usr_id == 0) {
            $a_usr_id = $this->usr_id;
        }
        return $this->access->checkAccessOfUser($a_usr_id, "manage_comp_temp", $this->obj_skill_tree_ref_id);
    }

    /**
     * @param int $a_usr_id
     * @return bool
     */
    public function hasReadProfilesPermission(int $a_usr_id = 0) : bool
    {
        if ($a_usr_id == 0) {
            $a_usr_id = $this->usr_id;
        }
        return $this->access->checkAccessOfUser($a_usr_id, "read_profiles", $this->obj_skill_tree_ref_id);
    }

    /**
     * @param int $a_usr_id
     * @return bool
     */
    public function hasManageProfilesPermission(int $a_usr_id = 0) : bool
    {
        if ($a_usr_id == 0) {
            $a_usr_id = $this->usr_id;
        }
        return $this->access->checkAccessOfUser($a_usr_id, "manage_profiles", $this->obj_skill_tree_ref_id);
    }
}