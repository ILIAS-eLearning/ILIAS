<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Handles deletion of (user) objects
 *
 * @author killing@leifos.de
 * @ingroup ServicesSkill
 */
class ilSkillObjDeletionHandler
{
    /**
     * @var int
     */
    protected $obj_id;

    /**
     * @var string
     */
    protected $obj_type;

    /**
     * Constructor
     */
    public function __construct($obj_id, $obj_type)
    {
        $this->obj_type = $obj_type;
        $this->obj_id = $obj_id;
    }

    /**
     * Process deletion
     */
    public function processDeletion()
    {
        if ($this->obj_type == "usr" && ilObject::_lookupType($this->obj_id) == "usr") {
            ilPersonalSkill::removeSkills($this->obj_id);
            ilPersonalSkill::removeMaterials($this->obj_id);
            ilSkillProfile::removeUserFromAllProfiles($this->obj_id);
            ilBasicSkill::removeAllUserData($this->obj_id);
        }
    }
}
