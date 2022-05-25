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
 * Manages skill profile completion
 *
 * (business logic)
 *
 * @author Thomas Famula <famula@leifos.de>
 */
class ilSkillProfileCompletionManager
{
    protected ilSkillProfileManager $profile_manager;
    protected ilSkillProfileCompletionRepository $profile_completion_repo;

    public function __construct(
        ilSkillProfileManager $profile_manager,
        ?ilSkillProfileCompletionRepository $profile_completion_repo = null
    ) {
        global $DIC;

        $this->profile_manager = $profile_manager;
        $this->profile_completion_repo = ($profile_completion_repo)
            ?: $DIC->skills()->internal()->repo()->getProfileCompletionRepo();
    }

    /**
     * @param array{base_skill_id: int, tref_id: int, level_id: int} $skills
     * @return array<int, array<int, int>>
     */
    public function getActualMaxLevels(
        int $user_id,
        array $skills,
        string $gap_mode = "",
        string $gap_mode_type = "",
        int $gap_mode_obj_id = 0
    ) : array {
        // get actual levels for gap analysis
        $actual_levels = [];
        foreach ($skills as $sk) {
            $bs = new ilBasicSkill($sk["base_skill_id"]);
            if ($gap_mode == "max_per_type") {
                $max = $bs->getMaxLevelPerType($sk["tref_id"], $gap_mode_type, $user_id);
            } elseif ($gap_mode == "max_per_object") {
                $max = $bs->getMaxLevelPerObject($sk["tref_id"], $gap_mode_obj_id, $user_id);
            } else {
                $max = $bs->getMaxLevel($sk["tref_id"], $user_id);
            }
            $actual_levels[$sk["base_skill_id"]][$sk["tref_id"]] = $max;
        }

        return $actual_levels;
    }

    public function getActualLastLevels(
        int $user_id,
        array $skills,
        string $gap_mode = "",
        string $gap_mode_type = "",
        int $gap_mode_obj_id = 0
    ) : array {
        // todo for coming feature
        return [];
    }

    /**
     * @param array{base_skill_id: int, tref_id: int, level_id: int} $skills
     * @return array<int, array<int, float>>
     */
    public function getActualNextLevelFulfilments(
        int $user_id,
        array $skills,
        string $gap_mode = "",
        string $gap_mode_type = "",
        int $gap_mode_obj_id = 0
    ) : array {
        // get actual next level fulfilments for gap analysis
        $fuls = [];
        foreach ($skills as $sk) {
            $bs = new ilBasicSkill($sk["base_skill_id"]);
            if ($gap_mode == "max_per_type") {
                $perc = $bs->getNextLevelFulfilmentPerType($sk["tref_id"], $gap_mode_type, $user_id);
            } elseif ($gap_mode == "max_per_object") {
                $perc = $bs->getNextLevelFulfilmentPerObject($sk["tref_id"], $gap_mode_obj_id, $user_id);
            } else {
                $perc = $bs->getNextLevelFulfilment($sk["tref_id"], $user_id);
            }
            $fuls[$sk["base_skill_id"]][$sk["tref_id"]] = $perc;
        }

        return $fuls;
    }

    /**
     * Get progress in percent for a profile
     */
    public function getProfileProgress(int $user_id, int $profile_id) : int
    {
        $profile = $this->profile_manager->getById($profile_id);
        $profile_levels = $profile->getSkillLevels();
        $skills = [];
        foreach ($profile_levels as $l) {
            $skills[] = array(
                "base_skill_id" => $l["base_skill_id"],
                "tref_id" => $l["tref_id"],
                "level_id" => $l["level_id"]
            );
        }
        $actual_levels = $this->getActualMaxLevels($user_id, $skills);

        $profile_count = 0;
        $achieved_count = 0;
        foreach ($profile_levels as $profile) {
            if ($actual_levels[$profile["base_skill_id"]][$profile["tref_id"]] >= $profile["level_id"]) {
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
    public function isProfileFulfilled(int $user_id, int $profile_id) : bool
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
    public function getAllProfileCompletionsForUser(int $user_id) : array
    {
        $user_profiles = $this->profile_manager->getProfilesOfUser($user_id);
        $profile_comps = [];
        foreach ($user_profiles as $p) {
            if ($this->isProfileFulfilled($user_id, $p["id"])) {
                $profile_comps[$p["id"]] = true;
            } else {
                $profile_comps[$p["id"]] = false;
            }
        }

        return $profile_comps;
    }

    /**
     * Get profile completion entries for given user-profile-combination
     */
    public function getEntries(int $user_id, int $profile_id) : array
    {
        return $this->profile_completion_repo->getEntries($user_id, $profile_id);
    }

    /**
     * Get all profile completion entries for a user
     * @return array{profile_id: int, user_id: int, date: string, fulfilled: int}[]
     */
    public function getFulfilledEntriesForUser(int $user_id) : array
    {
        return $this->profile_completion_repo->getFulfilledEntriesForUser($user_id);
    }

    /**
     * Get all profile completion entries for a user
     */
    public function getAllEntriesForUser(int $user_id) : array
    {
        return $this->profile_completion_repo->getAllEntriesForUser($user_id);
    }

    /**
     * Get all completion entries for a single profile
     */
    public function getAllEntriesForProfile(int $profile_id) : array
    {
        return $this->profile_completion_repo->getAllEntriesForProfile($profile_id);
    }

    /**
     * Write profile completion entries (fulfilled or non-fulfilled) of user for all profiles
     */
    public function writeCompletionEntryForAllProfiles(int $user_id) : void
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
    public function writeCompletionEntryForSingleProfile(int $user_id, int $profile_id) : void
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
    public function deleteEntriesForProfile(int $profile_id) : void
    {
        $this->profile_completion_repo->deleteEntriesForProfile($profile_id);
    }

    /**
     * Delete all profile completion entries for a user
     */
    public function deleteEntriesForUser(int $user_id) : void
    {
        $this->profile_completion_repo->deleteEntriesForUser($user_id);
    }
}
