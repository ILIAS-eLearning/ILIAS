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
 * Manages skill profile completion
 *
 * (business logic)
 *
 * @author Thomas Famula <famula@leifos.de>
 */
class SkillProfileCompletionManager
{
    protected SkillProfileManager $profile_manager;
    protected SkillProfileCompletionDBRepository $profile_completion_repo;
    protected \ilTree $tree_service;
    protected \ilObjectDefinition $obj_definition;

    public function __construct(
        SkillProfileManager $profile_manager,
        ?SkillProfileCompletionDBRepository $profile_completion_repo = null
    ) {
        global $DIC;

        $this->profile_manager = $profile_manager;
        $this->profile_completion_repo = ($profile_completion_repo)
            ?: $DIC->skills()->internal()->repo()->getProfileCompletionRepo();
        $this->tree_service = $DIC->repositoryTree();
        $this->obj_definition = $DIC["objDefinition"];
    }

    /**
     * @param SkillProfileLevel[] $skills
     * @return array<int, array<int, int>>
     */
    public function getActualMaxLevels(
        int $user_id,
        array $skills,
        string $gap_mode = "",
        string $gap_mode_type = "",
        int $gap_mode_obj_id = 0
    ): array {
        // get actual levels for gap analysis
        $actual_levels = [];
        foreach ($skills as $sk) {
            $bs = new \ilBasicSkill($sk->getBaseSkillId());
            if ($gap_mode == "max_per_type") {
                $max = $bs->getMaxLevelPerType($sk->getTrefId(), $gap_mode_type, $user_id);
            } elseif ($gap_mode == "max_per_object") {
                if ($this->obj_definition->isContainer(\ilObject::_lookupType($gap_mode_obj_id))) {
                    $sub_objects = $this->tree_service->getSubTree(
                        $this->tree_service->getNodeData((int) current(\ilObject::_getAllReferences($gap_mode_obj_id))),
                        false,
                        \ilObjectLP::getSupportedObjectTypes()
                    );
                    $max = 0;
                    foreach ($sub_objects as $ref_id) {
                        $obj_id = \ilContainerReference::_lookupObjectId($ref_id);
                        $max_tmp = $bs->getMaxLevelPerObject($sk->getTrefId(), $obj_id, $user_id);
                        if ($max_tmp > $max) {
                            $max = $max_tmp;
                        }
                    }
                } else {
                    $max = $bs->getMaxLevelPerObject($sk->getTrefId(), $gap_mode_obj_id, $user_id);
                }
            } else {
                $max = $bs->getMaxLevel($sk->getTrefId(), $user_id);
            }
            $actual_levels[$sk->getBaseSkillId()][$sk->getTrefId()] = $max;
        }

        return $actual_levels;
    }

    public function getActualLastLevels(
        int $user_id,
        array $skills,
        string $gap_mode = "",
        string $gap_mode_type = "",
        int $gap_mode_obj_id = 0
    ): array {
        // todo for coming feature
        return [];
    }

    /**
     * @param SkillProfileLevel[] $skills
     * @return array<int, array<int, float>>
     */
    public function getActualNextLevelFulfilments(
        int $user_id,
        array $skills,
        string $gap_mode = "",
        string $gap_mode_type = "",
        int $gap_mode_obj_id = 0
    ): array {
        // get actual next level fulfilments for gap analysis
        $fuls = [];
        foreach ($skills as $sk) {
            $bs = new \ilBasicSkill($sk->getBaseSkillId());
            if ($gap_mode == "max_per_type") {
                $perc = $bs->getNextLevelFulfilmentPerType($sk->getTrefId(), $gap_mode_type, $user_id);
            } elseif ($gap_mode == "max_per_object") {
                $perc = $bs->getNextLevelFulfilmentPerObject($sk->getTrefId(), $gap_mode_obj_id, $user_id);
            } else {
                $perc = $bs->getNextLevelFulfilment($sk->getTrefId(), $user_id);
            }
            $fuls[$sk->getBaseSkillId()][$sk->getTrefId()] = $perc;
        }

        return $fuls;
    }

    /**
     * Get progress in percent for a profile
     */
    public function getProfileProgress(int $user_id, int $profile_id): int
    {
        $profile_levels = $this->profile_manager->getSkillLevels($profile_id);
        $actual_levels = $this->getActualMaxLevels($user_id, $profile_levels);

        $profile_count = 0;
        $achieved_count = 0;
        foreach ($profile_levels as $level) {
            if ($actual_levels[$level->getBaseSkillId()][$level->getTrefId()] >= $level->getLevelId()) {
                $achieved_count++;
            }
            $profile_count++;
        }
        if ($profile_count == 0) {
            return 0;
        }
        $progress = $achieved_count / $profile_count * 100;

        return (int) $progress;
    }

    /**
     * Check if a profile is fulfilled (progress = 100%)
     */
    public function isProfileFulfilled(int $user_id, int $profile_id): bool
    {
        if ($this->getProfileProgress($user_id, $profile_id) == 100) {
            return true;
        }
        return false;
    }

    /**
     * Get all profiles of user which are fulfilled or non-fulfilled
     * @return array<int, bool>
     */
    public function getAllProfileCompletionsForUser(int $user_id): array
    {
        $user_profiles = $this->profile_manager->getProfilesOfUser($user_id);
        $profile_comps = [];
        foreach ($user_profiles as $p) {
            if ($this->isProfileFulfilled($user_id, $p->getId())) {
                $profile_comps[$p->getId()] = true;
            } else {
                $profile_comps[$p->getId()] = false;
            }
        }

        return $profile_comps;
    }

    /**
     * Get profile completion entries for given user-profile-combination
     * @return SkillProfileCompletion[]
     */
    public function getEntries(int $user_id, int $profile_id): array
    {
        return $this->profile_completion_repo->getEntries($user_id, $profile_id);
    }

    /**
     * Get all fulfilled profile completion entries for a user
     * @return SkillProfileCompletion[]
     */
    public function getFulfilledEntriesForUser(int $user_id): array
    {
        return $this->profile_completion_repo->getFulfilledEntriesForUser($user_id);
    }

    /**
     * Get all profile completion entries for a user
     * @return SkillProfileCompletion[]
     */
    public function getAllEntriesForUser(int $user_id): array
    {
        return $this->profile_completion_repo->getAllEntriesForUser($user_id);
    }

    /**
     * Get all completion entries for a single profile
     * @return SkillProfileCompletion[]
     */
    public function getAllEntriesForProfile(int $profile_id): array
    {
        return $this->profile_completion_repo->getAllEntriesForProfile($profile_id);
    }

    /**
     * Write profile completion entries (fulfilled or non-fulfilled) of user for all profiles
     */
    public function writeCompletionEntryForAllProfiles(int $user_id): void
    {
        $completions = $this->getAllProfileCompletionsForUser($user_id);
        foreach ($completions as $profile_id => $fulfilled) {
            if ($fulfilled) {
                $this->profile_completion_repo->addFulfilmentEntry($user_id, $profile_id);
            } else {
                $this->profile_completion_repo->addNonFulfilmentEntry($user_id, $profile_id);
            }
        }
    }

    /**
     * Write profile completion entry (fulfilled or non-fulfilled) of user for given profile
     */
    public function writeCompletionEntryForSingleProfile(int $user_id, int $profile_id): void
    {
        if ($this->isProfileFulfilled($user_id, $profile_id)) {
            $this->profile_completion_repo->addFulfilmentEntry($user_id, $profile_id);
        } else {
            $this->profile_completion_repo->addNonFulfilmentEntry($user_id, $profile_id);
        }
    }

    /**
     * Delete all profile completion entries for a profile
     */
    public function deleteEntriesForProfile(int $profile_id): void
    {
        $this->profile_completion_repo->deleteEntriesForProfile($profile_id);
    }

    /**
     * Delete all profile completion entries for a user
     */
    public function deleteEntriesForUser(int $user_id): void
    {
        $this->profile_completion_repo->deleteEntriesForUser($user_id);
    }
}
