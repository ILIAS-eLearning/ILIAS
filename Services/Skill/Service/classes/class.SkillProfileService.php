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

namespace ILIAS\Skill\Service;

use ILIAS\Skill\Profile;

/**
 * Skill profile service
 * @author famula@leifos.de
 */
class SkillProfileService
{
    protected Profile\SkillProfileManager $profile_manager;
    protected Profile\SkillProfileCompletionManager $profile_completion_manager;

    public function __construct(SkillInternalService $internal_service)
    {
        $this->profile_manager = $internal_service->manager()->getProfileManager();
        $this->profile_completion_manager = $internal_service->manager()->getProfileCompletionManager();
    }

    /**
     * @throws \ilSkillProfileNotFoundException
     */
    public function getProfile(int $profile_id): Profile\SkillProfile
    {
        return $this->profile_manager->getProfile($profile_id);
    }

    public function deleteProfile(int $profile_id): void
    {
        $this->profile_manager->delete($profile_id);
        $this->profile_completion_manager->deleteEntriesForProfile($profile_id);
    }

    public function lookupProfileTitle(int $profile_id): string
    {
        $title = $this->profile_manager->lookupTitle($profile_id);
        return $title;
    }

    public function lookupProfileRefId(int $profile_id): int
    {
        $ref_id = $this->profile_manager->lookupRefId($profile_id);
        return $ref_id;
    }

    /**
     * @return Profile\SkillProfileLevel[]
     */
    public function getSkillLevels(int $profile_id): array
    {
        return $this->profile_manager->getSkillLevels($profile_id);
    }

    /**
     * @return Profile\SkillProfile[]
     */
    public function getProfilesOfUser(int $user_id): array
    {
        return $this->profile_manager->getProfilesOfUser($user_id);
    }

    /**
     * @return Profile\SkillProfile[]
     */
    public function getAllGlobalProfiles(): array
    {
        $profiles = $this->profile_manager->getAllGlobalProfiles();
        return $profiles;
    }

    public function addRoleToProfile(int $profile_id, int $role_id): void
    {
        $this->profile_manager->addRoleToProfile($profile_id, $role_id);
    }

    /**
     * Update the old ref id with the new ref id after import
     */
    public function updateProfileRefIdAfterImport(int $profile_id, int $new_ref_id): void
    {
        $this->profile_manager->updateRefIdAfterImport($profile_id, $new_ref_id);
    }

    /**
     * Write profile completion entries (fulfilled or non-fulfilled) of user for all profiles
     */
    public function writeCompletionEntryForAllProfiles(int $user_id): void
    {
        $this->profile_completion_manager->writeCompletionEntryForAllProfiles($user_id);
    }
}
