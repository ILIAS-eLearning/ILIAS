<?php

/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Skill\Access;

/**
 * Skill tree access
 * @author Thomas Famula <famula@leifos.de>
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
    protected $ref_id;

    /**
     * @var string
     */
    protected $obj_type;

    /**
     * @var int
     */
    protected $usr_id;

    /**
     * Constructor
     */
    public function __construct(\ilRbacSystem $access, int $ref_id, int $usr_id)
    {
        $this->access = $access;
        $this->ref_id = $ref_id;
        $this->obj_type = \ilObject::_lookupType($this->ref_id, true);
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
        return $this->access->checkAccessOfUser($a_usr_id, "visible,read", $this->ref_id);
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
        return $this->access->checkAccessOfUser($a_usr_id, "write", $this->ref_id);
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
        return $this->access->checkAccessOfUser($a_usr_id, "edit_permission", $this->ref_id);
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
        return $this->access->checkAccessOfUser($a_usr_id, "read_comp", $this->ref_id);
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
        return $this->access->checkAccessOfUser($a_usr_id, "manage_comp", $this->ref_id);
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
        return $this->access->checkAccessOfUser($a_usr_id, "manage_comp_temp", $this->ref_id);
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
        if ($this->obj_type == "crs" || $this->obj_type == "grp") {
            return $this->access->checkAccessOfUser($a_usr_id, "read", $this->ref_id);
        }
        return $this->access->checkAccessOfUser($a_usr_id, "read_profiles", $this->ref_id);
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
        if ($this->obj_type == "crs" || $this->obj_type == "grp") {
            return $this->access->checkAccessOfUser($a_usr_id, "write", $this->ref_id);
        }
        return $this->access->checkAccessOfUser($a_usr_id, "manage_profiles", $this->ref_id);
    }
}