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

namespace ILIAS\Skill\Service;

/**
 * Skill profile service
 * @author famula@leifos.de
 */
class SkillProfileService
{
    protected \ilSkillProfileManager $profile_manager;

    public function __construct(SkillInternalService $internal_service)
    {
        $this->profile_manager = $internal_service->manager()->getProfileManager();
        $this->profile_completion_manager = $internal_service->manager()->getProfileCompletionManager();
    }

    /**
     * @throws \ilSkillProfileNotFoundException
     */
    public function getById(int $profile_id) : \ilSkillProfile
    {
        return $this->profile_manager->getById($profile_id);
    }

    public function delete(int $profile_id) : void
    {
        $this->profile_manager->delete($profile_id);
        $this->profile_completion_manager->deleteEntriesForProfile($profile_id);
    }

    public function lookupTitle(int $profile_id) : string
    {
        $title = $this->profile_manager->lookupTitle($profile_id);
        return $title;
    }

    public function lookupRefId(int $profile_id) : int
    {
        $ref_id = $this->profile_manager->lookupRefId($profile_id);
        return $ref_id;
    }

    public function getProfilesOfUser(int $user_id) : array
    {
        return $this->profile_manager->getProfilesOfUser($user_id);
    }

    public function getAllGlobalProfiles() : array
    {
        $profiles = $this->profile_manager->getAllGlobalProfiles();
        return $profiles;
    }

    public function addRoleToProfile(int $profile_id, int $role_id) : void
    {
        $this->profile_manager->addRoleToProfile($profile_id, $role_id);
    }

    /**
     * Update the old ref id with the new ref id after import
     */
    public function updateRefIdAfterImport(int $profile_id, int $new_ref_id) : void
    {
        $this->profile_manager->updateRefIdAfterImport($profile_id, $new_ref_id);
    }

    /**
     * Write profile completion entries (fulfilled or non-fulfilled) of user for all profiles
     */
    public function writeCompletionEntryForAllProfiles(int $user_id) : void
    {
        $this->profile_completion_manager->writeCompletionEntryForAllProfiles($user_id);
    }
}
