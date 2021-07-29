<?php

/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Skill\Access;

/**
 * Skill management access
 * @author Thomas Famula <famula@leifos.de>
 */
class SkillManagementAccess
{
    /**
     * @var \ilRbacSystem
     */
    protected $access;

    /**
     * @var int
     */
    protected $skmg_ref_id;

    /**
     * @var int
     */
    protected $usr_id;

    /**
     * Constructor
     */
    public function __construct(\ilRbacSystem $access, int $skmg_ref_id, int $usr_id)
    {
        $this->access = $access;
        $this->skmg_ref_id = $skmg_ref_id;
        $this->usr_id = $usr_id;
    }

    /**
     * @param int $a_usr_id
     * @return bool
     */
    public function hasReadManagementPermission(int $a_usr_id = 0) : bool
    {
        if ($a_usr_id == 0) {
            $a_usr_id = $this->usr_id;
        }
        return $this->access->checkAccessOfUser($a_usr_id, "visible,read", $this->skmg_ref_id);
    }

    /**
     * @param int $a_usr_id
     * @return bool
     */
    public function hasEditManagementSettingsPermission(int $a_usr_id = 0) : bool
    {
        if ($a_usr_id == 0) {
            $a_usr_id = $this->usr_id;
        }
        return $this->access->checkAccessOfUser($a_usr_id, "write", $this->skmg_ref_id);
    }

    /**
     * @param int $a_usr_id
     * @return bool
     */
    public function hasEditManagementPermissionsPermission(int $a_usr_id = 0) : bool
    {
        if ($a_usr_id == 0) {
            $a_usr_id = $this->usr_id;
        }
        return $this->access->checkAccessOfUser($a_usr_id, "edit_permission", $this->skmg_ref_id);
    }

    /**
     * @param int $a_usr_id
     * @return bool
     */
    public function hasCreateTreePermission(int $a_usr_id = 0) : bool
    {
        if ($a_usr_id == 0) {
            $a_usr_id = $this->usr_id;
        }
        return $this->access->checkAccessOfUser($a_usr_id, "create_skee", $this->skmg_ref_id);
    }
}