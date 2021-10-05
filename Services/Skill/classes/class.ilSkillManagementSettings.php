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

    public function activate(bool $a_active) : void
    {
        $this->set("enable_skmg", (int) $a_active);
    }

    public function isActivated() : bool
    {
        return $this->get("enable_skmg");
    }

    public function setHideProfileBeforeSelfEval(bool $a_val) : void
    {
        $this->set("hide_profile_self_eval", (int) $a_val);
    }

    public function getHideProfileBeforeSelfEval() : bool
    {
        return $this->get("hide_profile_self_eval");
    }

    public function setLocalAssignmentOfProfiles(bool $a_val) : void
    {
        $this->set("local_assignment_profiles", (int) $a_val);
    }

    public function getLocalAssignmentOfProfiles() : bool
    {
        return $this->get("local_assignment_profiles");
    }

    public function setAllowLocalProfiles(bool $a_val) : void
    {
        $this->set("allow_local_profiles", (int) $a_val);
    }

    public function getAllowLocalProfiles() : bool
    {
        return $this->get("allow_local_profiles");
    }
}
