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

namespace ILIAS\Skill\Profile;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class SkillProfileManager
{
    protected SkillProfileDBRepository $profile_repo;
    protected SkillProfileLevelsDBRepository $profile_levels_repo;
    protected SkillProfileUserDBRepository $profile_user_repo;
    protected SkillProfileRoleDBRepository $profile_role_repo;
    protected \ilRbacReview $rbac_review;

    public function __construct(
        ?\ILIAS\Skill\Profile\SkillProfileDBRepository $profile_repo = null,
        ?\ILIAS\Skill\Profile\SkillProfileLevelsDBRepository $profile_levels_repo = null,
        ?\ILIAS\Skill\Profile\SkillProfileUserDBRepository $profile_user_repo = null,
        ?\ILIAS\Skill\Profile\SkillProfileRoleDBRepository $profile_role_repo = null
    ) {
        global $DIC;

        $this->profile_repo = ($profile_repo) ?: $DIC->skills()->internal()->repo()->getProfileRepo();
        $this->profile_levels_repo = ($profile_levels_repo) ?: $DIC->skills()->internal()->repo()->getProfileLevelsRepo();
        $this->profile_user_repo = ($profile_user_repo) ?: $DIC->skills()->internal()->repo()->getProfileUserRepo();
        $this->profile_role_repo = ($profile_role_repo) ?: $DIC->skills()->internal()->repo()->getProfileRoleRepo();
        $this->rbac_review = $DIC->rbac()->review();
    }

    /**
     * @throws \ilSkillProfileNotFoundException
     */
    public function getById(int $profile_id) : SkillProfile
    {
        return $this->profile_repo->getById($profile_id);
    }

    public function createProfile(SkillProfile $profile) : SkillProfile
    {
        // profile
        $new_profile = $this->profile_repo->createProfile($profile);

        // profile levels
        $this->profile_levels_repo->createProfileLevels($new_profile->getId(), $new_profile->getSkillLevels());

        return $new_profile;
    }

    public function updateProfile(SkillProfile $profile) : SkillProfile
    {
        // profile
        $updated_profile = $this->profile_repo->updateProfile($profile);

        // profile levels
        $this->profile_levels_repo->updateProfileLevels($profile->getId(), $profile->getSkillLevels());

        return $updated_profile;
    }

    public function delete(int $profile_id) : void
    {
        $this->deleteProfile($profile_id);
        $this->deleteProfileLevels($profile_id);
        $this->deleteProfileUsers($profile_id);
        $this->deleteProfileRoles($profile_id);
    }

    protected function deleteProfile(int $profile_id) : void
    {
        $this->profile_repo->deleteProfile($profile_id);
    }

    protected function deleteProfileLevels(int $profile_id) : void
    {
        $this->profile_levels_repo->deleteProfileLevels($profile_id);
    }

    protected function deleteProfileUsers(int $profile_id) : void
    {
        $this->profile_user_repo->deleteProfileUsers($profile_id);
    }

    protected function deleteProfileRoles(int $profile_id) : void
    {
        $this->profile_role_repo->deleteProfileRoles($profile_id);
    }

    public function deleteProfilesFromObject(int $ref_id) : void
    {
        $this->profile_repo->deleteProfilesFromObject($ref_id);
    }

    public function updateSkillOrder(int $profile_id, array $order) : void
    {
        asort($order);

        $this->profile_levels_repo->updateSkillOrder($profile_id, $order);
    }

    public function fixSkillOrderNumbering(int $profile_id) : void
    {
        $this->profile_levels_repo->fixSkillOrderNumbering($profile_id);
    }

    public function getMaxLevelOrderNr(int $profile_id) : int
    {
        $max = $this->profile_levels_repo->getMaxLevelOrderNr($profile_id);
        return $max;
    }

    public function getProfilesForAllSkillTrees() : array
    {
        $profiles = $this->profile_repo->getProfilesForAllSkillTrees();
        return $profiles;
    }

    public function getProfilesForSkillTree(int $skill_tree_id) : array
    {
        $profiles = $this->profile_repo->getProfilesForSkillTree($skill_tree_id);
        return $profiles;
    }

    public function getAllGlobalProfiles() : array
    {
        $profiles = $this->profile_repo->getAllGlobalProfiles();
        return $profiles;
    }

    public function getLocalProfilesForObject(int $ref_id) : array
    {
        $profiles = $this->profile_repo->getLocalProfilesForObject($ref_id);
        return $profiles;
    }

    public function lookupTitle(int $profile_id) : string
    {
        $title = $this->profile_repo->lookup($profile_id, "title");
        return $title;
    }

    public function lookupRefId(int $profile_id) : int
    {
        $ref_id = $this->profile_repo->lookup($profile_id, "ref_id");
        return $ref_id;
    }

    /**
     * Update the old ref id with the new ref id after import
     */
    public function updateRefIdAfterImport(int $profile_id, int $new_ref_id) : void
    {
        $this->profile_repo->updateRefIdAfterImport($profile_id, $new_ref_id);
    }

    public function getTreeId(int $profile_id) : int
    {
        $tree_id = $this->profile_repo->getTreeId($profile_id);
        return $tree_id;
    }

    ////
    //// Skill user assignment
    ////

    /**
     * Get all assignments (users and roles)
     */
    public function getAssignments(int $profile_id) : array
    {
        $assignments = [];

        $users = $this->getAssignedUsers($profile_id);
        $roles = $this->getAssignedRoles($profile_id);
        $assignments = array_merge($users, $roles);

        return $assignments;
    }

    public function getAssignedUsers(int $profile_id) : array
    {
        $users = $this->profile_user_repo->getAssignedUsers($profile_id);
        return $users;
    }

    public function getAssignedUsersForRole(int $role_id) : array
    {
        return $this->rbac_review->assignedUsers($role_id);
    }

    public function getAssignedUserIdsIncludingRoleAssignments(int $profile_id) : array
    {
        $all = [];
        $users = $this->getAssignedUsers($profile_id);
        foreach ($users as $user) {
            $all[] = $user["id"];
        }

        $roles = $this->getAssignedRoles($profile_id);
        foreach ($roles as $role) {
            $role_users = $this->rbac_review->assignedUsers($role["id"]);
            foreach ($role_users as $user_id) {
                if (!in_array($user_id, $all)) {
                    $all[] = $user_id;
                }
            }
        }

        return $all;
    }

    public function addUserToProfile(int $profile_id, int $user_id) : void
    {
        $this->profile_user_repo->addUserToProfile($profile_id, $user_id);
    }

    public function removeUserFromProfile(int $profile_id, int $user_id) : void
    {
        $this->profile_user_repo->removeUserFromProfile($profile_id, $user_id);
    }

    public function removeUserFromAllProfiles(int $user_id) : void
    {
        $this->profile_user_repo->removeUserFromAllProfiles($user_id);
    }

    /**
     * @return array{id: int, title: string, description: string, image_id: string}[]
     */
    public function getProfilesOfUser(int $user_id) : array
    {
        $all_profiles = [];

        // competence profiles coming from user assignments
        $user_profiles = $this->profile_user_repo->getProfilesOfUser($user_id);

        // competence profiles coming from role assignments
        $role_profiles = [];
        $user_roles = $this->rbac_review->assignedRoles($user_id);
        foreach ($user_roles as $role) {
            $profiles = $this->getGlobalProfilesOfRole($role);
            foreach ($profiles as $profile) {
                $role_profiles[] = $profile;
            }
        }

        // merge competence profiles and remove multiple occurrences
        $all_profiles = array_merge($user_profiles, $role_profiles);
        $temp_profiles = [];
        foreach ($all_profiles as $v) {
            if (!isset($temp_profiles[$v["id"]])) {
                $temp_profiles[$v["id"]] = $v;
            }
        }
        $all_profiles = array_values($temp_profiles);
        return $all_profiles;
    }

    public function countUsers(int $profile_id) : int
    {
        $count = $this->profile_user_repo->countUsers($profile_id);
        return $count;
    }

    public function getAssignedRoles(int $profile_id) : array
    {
        $roles = $this->profile_role_repo->getAssignedRoles($profile_id);
        return $roles;
    }

    public function addRoleToProfile(int $profile_id, int $role_id) : void
    {
        $this->profile_role_repo->addRoleToProfile($profile_id, $role_id);
    }

    public function removeRoleFromProfile(int $profile_id, int $role_id) : void
    {
        $this->profile_role_repo->removeRoleFromProfile($profile_id, $role_id);
    }

    public function removeRoleFromAllProfiles(int $role_id) : void
    {
        $this->profile_role_repo->removeRoleFromAllProfiles($role_id);
    }

    /**
     * Get global and local profiles of a role
     * @return array{id: int, title: string, description: string, image_id: string}[]
     */
    public function getAllProfilesOfRole(int $role_id) : array
    {
        $profiles = $this->profile_role_repo->getAllProfilesOfRole($role_id);
        return $profiles;
    }

    /**
     * @return array{id: int, title: string, description: string, image_id: string}[]
     */
    public function getGlobalProfilesOfRole(int $role_id) : array
    {
        $profiles = $this->profile_role_repo->getGlobalProfilesOfRole($role_id);
        return $profiles;
    }

    public function getLocalProfilesOfRole(int $role_id, int $ref_id) : array
    {
        $profiles = $this->profile_role_repo->getLocalProfilesOfRole($role_id, $ref_id);
        return $profiles;
    }

    public function countRoles(int $profile_id) : int
    {
        $count = $this->profile_role_repo->countRoles($profile_id);
        return $count;
    }
}
