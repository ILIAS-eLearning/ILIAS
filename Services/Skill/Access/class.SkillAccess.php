<?php

/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Skill\Access;

/**
 * Skill access
 * @author Alexander Killing <killing@leifos.de>
 */
class SkillAccess
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

    public function hasManageProfilesPermission(int $a_usr_id = 0) : bool
    {
        if ($a_usr_id == 0) {
            $a_usr_id = $this->usr_id;
        }
        return $this->access->checkAccessOfUser($a_usr_id, "manage_profiles", $this->obj_skill_tree_ref_id);
    }

    public function hasReadProfilesPermission(int $a_usr_id = 0) : bool
    {
        if ($a_usr_id == 0) {
            $a_usr_id = $this->usr_id;
        }
        return $this->access->checkAccessOfUser($a_usr_id, "read_profiles", $this->obj_skill_tree_ref_id);
    }
}