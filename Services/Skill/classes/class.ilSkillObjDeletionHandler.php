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
 * Handles deletion of (user) objects
 *
 * @author killing@leifos.de
 * @ingroup ServicesSkill
 */
class ilSkillObjDeletionHandler
{
    protected int $obj_id = 0;
    protected string $obj_type = "";
    protected ilSkillProfileManager $profile_manager;
    protected ilSkillProfileCompletionManager $profile_completion_manager;

    public function __construct(int $obj_id, string $obj_type)
    {
        global $DIC;

        $this->obj_type = $obj_type;
        $this->obj_id = $obj_id;
        $this->profile_manager = $DIC->skills()->internal()->manager()->getProfileManager();
        $this->profile_completion_manager = $DIC->skills()->internal()->manager()->getProfileCompletionManager();
    }

    public function processDeletion() : void
    {
        if ($this->obj_type == "usr" && ilObject::_lookupType($this->obj_id) == "usr") {
            ilPersonalSkill::removeSkills($this->obj_id);
            ilPersonalSkill::removeMaterials($this->obj_id);
            $this->profile_manager->removeUserFromAllProfiles($this->obj_id);
            $this->profile_completion_manager->deleteEntriesForUser($this->obj_id);
            ilBasicSkill::removeAllUserData($this->obj_id);
        }
        if ($this->obj_type == "role" && ilObject::_lookupType($this->obj_id) == "role") {
            $this->profile_manager->removeRoleFromAllProfiles($this->obj_id);
        }
        if ($this->obj_type == "crs" && ilObject::_lookupType($this->obj_id) == "crs") {
            foreach (ilContainerReference::_getAllReferences($this->obj_id) as $ref_id) {
                if ($ref_id != 0) {
                    $this->profile_manager->deleteProfilesFromObject($ref_id);
                }
            }
        }
        if ($this->obj_type == "grp" && ilObject::_lookupType($this->obj_id) == "grp") {
            foreach (ilContainerReference::_getAllReferences($this->obj_id) as $ref_id) {
                if ($ref_id != 0) {
                    $this->profile_manager->deleteProfilesFromObject($ref_id);
                }
            }
        }
    }
}
