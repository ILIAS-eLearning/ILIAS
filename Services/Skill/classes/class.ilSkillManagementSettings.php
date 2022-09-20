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

/**
 * Skill management settings
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup
 */
class ilSkillManagementSettings extends ilSetting
{
    public function __construct()
    {
        parent::__construct("skmg");
    }

    public function activate(bool $a_active): void
    {
        $value = $a_active ? "1" : "0";
        $this->set("enable_skmg", $value);
    }

    public function isActivated(): bool
    {
        return (bool) $this->get("enable_skmg", "0");
    }

    public function setHideProfileBeforeSelfEval(bool $a_val): void
    {
        $value = $a_val ? "1" : "0";
        $this->set("hide_profile_self_eval", $value);
    }

    public function getHideProfileBeforeSelfEval(): bool
    {
        return (bool) $this->get("hide_profile_self_eval", "0");
    }

    public function setLocalAssignmentOfProfiles(bool $a_val): void
    {
        $value = $a_val ? "1" : "0";
        $this->set("local_assignment_profiles", $value);
    }

    public function getLocalAssignmentOfProfiles(): bool
    {
        return (bool) $this->get("local_assignment_profiles", "0");
    }

    public function setAllowLocalProfiles(bool $a_val): void
    {
        $value = $a_val ? "1" : "0";
        $this->set("allow_local_profiles", $value);
    }

    public function getAllowLocalProfiles(): bool
    {
        return (bool) $this->get("allow_local_profiles", "0");
    }
}
