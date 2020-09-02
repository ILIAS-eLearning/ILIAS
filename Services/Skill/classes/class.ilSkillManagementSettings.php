<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Skill management settings
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup
 */
class ilSkillManagementSettings extends ilSetting
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct("skmg");
    }
    
    /**
     * Activate skill management
     *
     * @param
     * @return
     */
    public function activate($a_active)
    {
        $this->set("enable_skmg", (int) $a_active);
    }
    
    
    /**
     * Is activated
     */
    public function isActivated()
    {
        return $this->get("enable_skmg");
    }
    
    /**
     * Set hide profile values before self evaluations
     *
     * @param bool $a_val hide profile
     */
    public function setHideProfileBeforeSelfEval($a_val)
    {
        $this->set("hide_profile_self_eval", (int) $a_val);
    }
    
    /**
     * Get hide profile values before self evaluations
     *
     * @return bool hide profile
     */
    public function getHideProfileBeforeSelfEval()
    {
        return $this->get("hide_profile_self_eval");
    }

    /**
     * Set value if local assignment of global profiles is allowed
     *
     * @param bool $a_val
     */
    public function setLocalAssignmentOfProfiles(bool $a_val)
    {
        $this->set("local_assignment_profiles", (int) $a_val);
    }

    /**
     * Get value if local assignment of global profiles is allowed
     *
     * @return bool
     */
    public function getLocalAssignmentOfProfiles() : bool
    {
        return $this->get("local_assignment_profiles");
    }

    /**
     * Set value if creation of local profiles is allowed
     *
     * @param bool $a_val
     */
    public function setAllowLocalProfiles(bool $a_val)
    {
        $this->set("allow_local_profiles", (int) $a_val);
    }

    /**
     * Get value if creation of local profiles is allowed
     *
     * @return bool
     */
    public function getAllowLocalProfiles() : bool
    {
        return $this->get("allow_local_profiles");
    }
}
