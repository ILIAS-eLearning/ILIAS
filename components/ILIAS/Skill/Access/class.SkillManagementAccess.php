<?php

declare(strict_types=1);

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
 * Skill management access
 * @author Thomas Famula <famula@leifos.de>
 */
class SkillManagementAccess
{
    protected \ilRbacSystem $access;
    protected int $skmg_ref_id = 0;
    protected int $usr_id = 0;

    public function __construct(\ilRbacSystem $access, int $skmg_ref_id, int $usr_id)
    {
        $this->access = $access;
        $this->skmg_ref_id = $skmg_ref_id;
        $this->usr_id = $usr_id;
    }

    public function hasReadManagementPermission(int $a_usr_id = 0): bool
    {
        if ($a_usr_id == 0) {
            $a_usr_id = $this->usr_id;
        }
        return $this->access->checkAccessOfUser($a_usr_id, "visible,read", $this->skmg_ref_id);
    }

    public function hasEditManagementSettingsPermission(int $a_usr_id = 0): bool
    {
        if ($a_usr_id == 0) {
            $a_usr_id = $this->usr_id;
        }
        return $this->access->checkAccessOfUser($a_usr_id, "write", $this->skmg_ref_id);
    }

    public function hasEditManagementPermissionsPermission(int $a_usr_id = 0): bool
    {
        if ($a_usr_id == 0) {
            $a_usr_id = $this->usr_id;
        }
        return $this->access->checkAccessOfUser($a_usr_id, "edit_permission", $this->skmg_ref_id);
    }

    public function hasCreateTreePermission(int $a_usr_id = 0): bool
    {
        if ($a_usr_id == 0) {
            $a_usr_id = $this->usr_id;
        }
        return $this->access->checkAccessOfUser($a_usr_id, "create_skee", $this->skmg_ref_id);
    }
}
