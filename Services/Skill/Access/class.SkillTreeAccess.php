<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 ********************************************************************
 */

namespace ILIAS\Skill\Access;

/**
 * Skill tree access
 * @author Thomas Famula <famula@leifos.de>
 */
class SkillTreeAccess
{
    protected \ilRbacSystem $access;
    protected int $ref_id = 0;
    protected string $obj_type = "";
    protected int $usr_id = 0;

    public function __construct(\ilRbacSystem $access, int $ref_id, int $usr_id)
    {
        $this->access = $access;
        $this->ref_id = $ref_id;
        $this->obj_type = \ilObject::_lookupType($this->ref_id, true);
        $this->usr_id = $usr_id;
    }

    public function hasVisibleTreePermission(int $a_usr_id = 0) : bool
    {
        if ($a_usr_id == 0) {
            $a_usr_id = $this->usr_id;
        }
        return $this->access->checkAccessOfUser($a_usr_id, "visible", $this->ref_id);
    }

    public function hasReadTreePermission(int $a_usr_id = 0) : bool
    {
        if ($a_usr_id == 0) {
            $a_usr_id = $this->usr_id;
        }
        return $this->access->checkAccessOfUser($a_usr_id, "read", $this->ref_id);
    }

    public function hasEditTreeSettingsPermission(int $a_usr_id = 0) : bool
    {
        if ($a_usr_id == 0) {
            $a_usr_id = $this->usr_id;
        }
        return $this->access->checkAccessOfUser($a_usr_id, "write", $this->ref_id);
    }

    public function hasEditTreePermissionsPermission(int $a_usr_id = 0) : bool
    {
        if ($a_usr_id == 0) {
            $a_usr_id = $this->usr_id;
        }
        return $this->access->checkAccessOfUser($a_usr_id, "edit_permission", $this->ref_id);
    }

    public function hasReadCompetencesPermission(int $a_usr_id = 0) : bool
    {
        if ($a_usr_id == 0) {
            $a_usr_id = $this->usr_id;
        }
        return $this->access->checkAccessOfUser($a_usr_id, "read_comp", $this->ref_id);
    }

    public function hasManageCompetencesPermission(int $a_usr_id = 0) : bool
    {
        if ($a_usr_id == 0) {
            $a_usr_id = $this->usr_id;
        }
        return $this->access->checkAccessOfUser($a_usr_id, "manage_comp", $this->ref_id);
    }

    public function hasManageCompetenceTemplatesPermission(int $a_usr_id = 0) : bool
    {
        if ($a_usr_id == 0) {
            $a_usr_id = $this->usr_id;
        }
        return $this->access->checkAccessOfUser($a_usr_id, "manage_comp_temp", $this->ref_id);
    }

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
