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

use ILIAS\Skill\Profile;
use ILIAS\Skill\Personal;
use ILIAS\Skill\Usage;

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
    protected Profile\SkillProfileManager $profile_manager;
    protected Profile\SkillProfileCompletionManager $profile_completion_manager;
    protected Personal\PersonalSkillManager $personal_manager;
    protected Personal\AssignedMaterialManager $assigned_material_manager;
    protected Usage\SkillUsageManager $usage_manager;

    public function __construct(int $obj_id, string $obj_type)
    {
        global $DIC;

        $this->obj_type = $obj_type;
        $this->obj_id = $obj_id;
        $this->profile_manager = $DIC->skills()->internal()->manager()->getProfileManager();
        $this->profile_completion_manager = $DIC->skills()->internal()->manager()->getProfileCompletionManager();
        $this->personal_manager = $DIC->skills()->internal()->manager()->getPersonalSkillManager();
        $this->assigned_material_manager = $DIC->skills()->internal()->manager()->getAssignedMaterialManager();
        $this->usage_manager = $DIC->skills()->internal()->manager()->getUsageManager();
    }

    public function processDeletion(): void
    {
        if ($this->obj_type == "usr" && ilObject::_lookupType($this->obj_id) == "usr") {
            $this->personal_manager->removePersonalSkillsForUser($this->obj_id);
            $this->assigned_material_manager->removeAssignedMaterialsForUser($this->obj_id);
            $this->profile_manager->removeUserFromAllProfiles($this->obj_id);
            $this->profile_completion_manager->deleteEntriesForUser($this->obj_id);
            ilBasicSkill::removeAllUserData($this->obj_id);
            return;
        } elseif ($this->obj_type == "role" && ilObject::_lookupType($this->obj_id) == "role") {
            $this->profile_manager->removeRoleFromAllProfiles($this->obj_id);
            return;
        } elseif ($this->obj_type == "crs" && ilObject::_lookupType($this->obj_id) == "crs") {
            foreach (ilContainerReference::_getAllReferences($this->obj_id) as $ref_id) {
                if ($ref_id != 0) {
                    $this->profile_manager->deleteProfilesFromObject($ref_id);
                }
            }
        } elseif ($this->obj_type == "grp" && ilObject::_lookupType($this->obj_id) == "grp") {
            foreach (ilContainerReference::_getAllReferences($this->obj_id) as $ref_id) {
                if ($ref_id != 0) {
                    $this->profile_manager->deleteProfilesFromObject($ref_id);
                }
            }
        }
        $this->usage_manager->removeUsagesFromObject($this->obj_id);
    }
}
