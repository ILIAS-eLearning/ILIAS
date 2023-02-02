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

namespace ILIAS\Skill\Profile;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class SkillProfileManager implements \ilSkillUsageInfo
{
    protected SkillProfileDBRepository $profile_repo;
    protected SkillProfileLevelsDBRepository $profile_levels_repo;
    protected SkillProfileUserDBRepository $profile_user_repo;
    protected SkillProfileRoleDBRepository $profile_role_repo;
    protected \ilRbacReview $rbac_review;
    protected \ilLanguage $lng;

    public function __construct(
        \ILIAS\Skill\Profile\SkillProfileDBRepository $profile_repo = null,
        \ILIAS\Skill\Profile\SkillProfileLevelsDBRepository $profile_levels_repo = null,
        \ILIAS\Skill\Profile\SkillProfileUserDBRepository $profile_user_repo = null,
        \ILIAS\Skill\Profile\SkillProfileRoleDBRepository $profile_role_repo = null
    ) {
        global $DIC;

        $this->profile_repo = ($profile_repo) ?: $DIC->skills()->internal()->repo()->getProfileRepo();
        $this->profile_levels_repo = ($profile_levels_repo) ?: $DIC->skills()->internal()->repo()->getProfileLevelsRepo();
        $this->profile_user_repo = ($profile_user_repo) ?: $DIC->skills()->internal()->repo()->getProfileUserRepo();
        $this->profile_role_repo = ($profile_role_repo) ?: $DIC->skills()->internal()->repo()->getProfileRoleRepo();
        $this->rbac_review = $DIC->rbac()->review();
        $this->lng = $DIC->language();
    }

    /**
     * @throws \ilSkillProfileNotFoundException
     */
    public function getProfile(int $profile_id): SkillProfile
    {
        return $this->profile_repo->get($profile_id);
    }

    public function createProfile(SkillProfile $profile): SkillProfile
    {
        // profile
        $new_profile = $this->profile_repo->createProfile($profile);

        return $new_profile;
    }

    public function updateProfile(SkillProfile $profile): SkillProfile
    {
        // profile
        $updated_profile = $this->profile_repo->updateProfile($profile);

        return $updated_profile;
    }

    public function delete(int $profile_id): void
    {
        $this->deleteProfile($profile_id);
        $this->deleteProfileLevels($profile_id);
        $this->deleteProfileUsers($profile_id);
        $this->deleteProfileRoles($profile_id);
    }

    protected function deleteProfile(int $profile_id): void
    {
        $this->profile_repo->deleteProfile($profile_id);
    }

    protected function deleteProfileLevels(int $profile_id): void
    {
        $this->profile_levels_repo->deleteAll($profile_id);
    }

    protected function deleteProfileUsers(int $profile_id): void
    {
        $this->profile_user_repo->deleteProfileUsers($profile_id);
    }

    protected function deleteProfileRoles(int $profile_id): void
    {
        $this->profile_role_repo->deleteProfileRoles($profile_id);
    }

    public function deleteProfilesFromObject(int $ref_id): void
    {
        $this->profile_repo->deleteProfilesFromObject($ref_id);
    }

    /**
     * @return SkillProfileLevel[]
     */
    public function getSkillLevels(int $profile_id): array
    {
        $levels = $this->profile_levels_repo->get($profile_id);
        usort($levels, static function (SkillProfileLevel $level_a, SkillProfileLevel $level_b): int {
            return $level_a->getOrderNr() <=> $level_b->getOrderNr();
        });

        return $levels;
    }

    public function addSkillLevel(SkillProfileLevel $skill_level_obj): void
    {
        $this->profile_levels_repo->create($skill_level_obj);
    }

    public function removeSkillLevel(SkillProfileLevel $skill_level_obj): void
    {
        $this->profile_levels_repo->delete($skill_level_obj);
    }

    public function updateSkillOrder(int $profile_id, array $order): void
    {
        asort($order);

        $this->profile_levels_repo->updateSkillOrder($profile_id, $order);
    }

    public function fixSkillOrderNumbering(int $profile_id): void
    {
        $this->profile_levels_repo->fixSkillOrderNumbering($profile_id);
    }

    public function getMaxLevelOrderNr(int $profile_id): int
    {
        $max = $this->profile_levels_repo->getMaxOrderNr($profile_id);
        return $max;
    }

    /**
     * @return SkillProfile[]
     */
    public function getProfilesForAllSkillTrees(): array
    {
        $profiles = $this->profile_repo->getProfilesForAllSkillTrees();
        return $profiles;
    }

    /**
     * @return SkillProfile[]
     */
    public function getProfilesForSkillTree(int $skill_tree_id): array
    {
        $profiles = $this->profile_repo->getProfilesForSkillTree($skill_tree_id);
        return $profiles;
    }

    /**
     * @return SkillProfile[]
     */
    public function getAllGlobalProfiles(): array
    {
        $profiles = $this->profile_repo->getAllGlobalProfiles();
        return $profiles;
    }

    /**
     * @return SkillProfile[]
     */
    public function getLocalProfilesForObject(int $ref_id): array
    {
        $profiles = $this->profile_repo->getLocalProfilesForObject($ref_id);
        return $profiles;
    }

    public function lookupTitle(int $profile_id): string
    {
        $title = $this->profile_repo->lookup($profile_id, "title");
        return $title;
    }

    public function lookupRefId(int $profile_id): int
    {
        $ref_id = $this->profile_repo->lookup($profile_id, "ref_id");
        return (int) $ref_id;
    }

    /**
     * Update the old ref id with the new ref id after import
     */
    public function updateRefIdAfterImport(int $profile_id, int $new_ref_id): void
    {
        $this->profile_repo->updateRefIdAfterImport($profile_id, $new_ref_id);
    }

    public function getTreeId(int $profile_id): int
    {
        $tree_id = $this->profile_repo->getTreeId($profile_id);
        return $tree_id;
    }

    ////
    //// Skill user assignment
    ////

    /**
     * Get all assignments (users and roles)
     * @return SkillProfileAssignmentInterface[]
     */
    public function getAssignments(int $profile_id): array
    {
        $assignments = [];

        $users = $this->getUserAssignments($profile_id);
        $roles = $this->getRoleAssignments($profile_id);
        $assignments = array_merge($users, $roles);

        return $assignments;
    }

    /**
     * @return SkillProfileUserAssignment[]
     */
    public function getUserAssignments(int $profile_id): array
    {
        $lng = $this->lng;

        $users = $this->profile_user_repo->get($profile_id);
        /** @var SkillProfileUserAssignment[] $users_as_obj */
        $users_as_obj = [];
        foreach ($users as $u) {
            $u["user_id"] = (int) $u["user_id"];
            $u["profile_id"] = (int) $u["profile_id"];
            $name = \ilUserUtil::getNamePresentation($u["user_id"]);

            $user_restructured = [
                "name" => $name,
                "id" => $u["user_id"]
            ];

            $users_as_obj[] = $this->profile_user_repo->getFromRecord($user_restructured);
        }

        return $users_as_obj;
    }

    /**
     * @return int[]
     */
    public function getAssignedUsersForRole(int $role_id): array
    {
        return $this->rbac_review->assignedUsers($role_id);
    }

    /**
     * @return int[]
     */
    public function getAssignedUserIdsIncludingRoleAssignments(int $profile_id): array
    {
        $all = [];
        $users = $this->getUserAssignments($profile_id);
        foreach ($users as $user) {
            $all[] = $user->getId();
        }

        $roles = $this->getRoleAssignments($profile_id);
        foreach ($roles as $role) {
            $role_users = $this->rbac_review->assignedUsers($role->getId());
            foreach ($role_users as $user_id) {
                if (!in_array($user_id, $all)) {
                    $all[] = $user_id;
                }
            }
        }

        return $all;
    }

    public function addUserToProfile(int $profile_id, int $user_id): void
    {
        $this->profile_user_repo->addUserToProfile($profile_id, $user_id);
    }

    public function removeUserFromProfile(int $profile_id, int $user_id): void
    {
        $this->profile_user_repo->removeUserFromProfile($profile_id, $user_id);
    }

    public function removeUserFromAllProfiles(int $user_id): void
    {
        $this->profile_user_repo->removeUserFromAllProfiles($user_id);
    }

    /**
     * @return SkillProfile[]
     */
    public function getProfilesOfUser(int $user_id): array
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

        /** @var SkillProfile[] $all_profiles */
        $all_profiles = array_merge($user_profiles, $role_profiles);
        /** @var SkillProfile[] $temp_profiles */
        $temp_profiles = [];
        foreach ($all_profiles as $v) {
            if (!isset($temp_profiles[$v->getId()])) {
                $temp_profiles[$v->getId()] = $v;
            }
        }
        $all_profiles = array_values($temp_profiles);
        return $all_profiles;
    }

    public function countUsers(int $profile_id): int
    {
        $count = $this->profile_user_repo->countUsers($profile_id);
        return $count;
    }

    /**
     * @return SkillProfileRoleAssignment[]
     */
    public function getRoleAssignments(int $profile_id, bool $with_objects_in_trash = false): array
    {
        $lng = $this->lng;
        $review = $this->rbac_review;

        $roles = $this->profile_role_repo->get($profile_id);
        /** @var SkillProfileRoleAssignment[] $roles_as_obj_without_trash */
        $roles_as_obj_without_trash = [];
        /** @var SkillProfileRoleAssignment[] $roles_as_obj_with_trash */
        $roles_as_obj_with_trash = [];
        foreach ($roles as $r) {
            $r["role_id"] = (int) $r["role_id"];
            $r["profile_id"] = (int) $r["profile_id"];
            $name = \ilObjRole::_getTranslation(\ilObjRole::_lookupTitle($r["role_id"]));
            // get object of role
            $obj_id = \ilObject::_lookupObjectId($review->getObjectReferenceOfRole($r["role_id"]));
            $obj_title = \ilObject::_lookupTitle($obj_id);
            $obj_type = \ilObject::_lookupType($obj_id);

            $role_restructured = [
                "name" => $name,
                "id" => $r["role_id"],
                "object_title" => $obj_title,
                "object_type" => $obj_type,
                "object_id" => $obj_id
            ];

            if (!$with_objects_in_trash && \ilObject::_hasUntrashedReference($obj_id)) {
                $roles_as_obj_without_trash[] = $this->profile_role_repo->getFromRecord($role_restructured);
            }
            $roles_as_obj_with_trash[] = $this->profile_role_repo->getFromRecord($role_restructured);
        }

        if ($with_objects_in_trash) {
            return $roles_as_obj_with_trash;
        }
        return $roles_as_obj_without_trash;
    }

    public function addRoleToProfile(int $profile_id, int $role_id): void
    {
        $this->profile_role_repo->addRoleToProfile($profile_id, $role_id);
    }

    public function removeRoleFromProfile(int $profile_id, int $role_id): void
    {
        $this->profile_role_repo->removeRoleFromProfile($profile_id, $role_id);
    }

    public function removeRoleFromAllProfiles(int $role_id): void
    {
        $this->profile_role_repo->removeRoleFromAllProfiles($role_id);
    }

    /**
     * Get global and local profiles of a role
     * @return SkillProfile[]
     */
    public function getAllProfilesOfRole(int $role_id): array
    {
        $profiles = $this->profile_role_repo->getAllProfilesOfRole($role_id);
        return $profiles;
    }

    /**
     * @return SkillProfile[]
     */
    public function getGlobalProfilesOfRole(int $role_id): array
    {
        $profiles = $this->profile_role_repo->getGlobalProfilesOfRole($role_id);
        return $profiles;
    }

    /**
     * @return SkillProfile[]
     */
    public function getLocalProfilesOfRole(int $role_id, int $ref_id): array
    {
        $profiles = $this->profile_role_repo->getLocalProfilesOfRole($role_id, $ref_id);
        return $profiles;
    }

    public function countRoles(int $profile_id): int
    {
        $count = $this->profile_role_repo->countRoles($profile_id);
        return $count;
    }

    /**
     * @param array{skill_id: int, tref_id: int}[] $a_cskill_ids
     *
     * @return array<string, array<string, array{key: string}[]>>
     */
    public static function getUsageInfo(array $a_cskill_ids): array
    {
        return \ilSkillUsage::getUsageInfoGeneric(
            $a_cskill_ids,
            \ilSkillUsage::PROFILE,
            "skl_profile_level",
            "profile_id",
            "base_skill_id"
        );
    }
}
