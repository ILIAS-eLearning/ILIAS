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
    protected int $user_id = 0;

    public function __construct(int $a_user_id)
    {
        $this->setUserId($a_user_id);
    }

    public function setUserId(int $a_val) : void
    {
        $this->user_id = $a_val;
    }

    public function getUserId() : int
    {
        return $this->user_id;
    }

    /**
     * @param array{base_skill_id: int, tref_id: int, level_id: int} $a_skills
     * @return array<int, array<int, int>>
     */
    public function getActualMaxLevels(
        array $a_skills = null,
        string $a_gap_mode = "",
        string $a_gap_mode_type = "",
        int $a_gap_mode_obj_id = 0
    ) : array {
        // get actual levels for gap analysis
        $actual_levels = [];
        foreach ($a_skills as $sk) {
            $bs = new ilBasicSkill($sk["base_skill_id"]);
            if ($a_gap_mode == "max_per_type") {
                $max = $bs->getMaxLevelPerType($sk["tref_id"], $a_gap_mode_type, $this->getUserId());
            } elseif ($a_gap_mode == "max_per_object") {
                $max = $bs->getMaxLevelPerObject($sk["tref_id"], $a_gap_mode_obj_id, $this->getUserId());
            } else {
                $max = $bs->getMaxLevel($sk["tref_id"], $this->getUserId());
            }
            $actual_levels[$sk["base_skill_id"]][$sk["tref_id"]] = $max;
        }

        return $actual_levels;
    }

    public function getActualLastLevels(
        ?array $a_skills = null,
        string $a_gap_mode = "",
        string $a_gap_mode_type = "",
        int $a_gap_mode_obj_id = 0
    ) : array {
        // todo for coming feature
        return [];
    }

    /**
     * Get progress in percent for a profile
     */
    public function getProfileProgress(int $a_profile_id) : int
    {
        $profile = new ilSkillProfile($a_profile_id);
        $profile_levels = $profile->getSkillLevels();
        $skills = [];
        foreach ($profile_levels as $l) {
            $skills[] = array(
                "base_skill_id" => $l["base_skill_id"],
                "tref_id" => $l["tref_id"],
                "level_id" => $l["level_id"]
            );
        }
        $actual_levels = $this->getActualMaxLevels($skills);

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
    public function isProfileFulfilled(int $a_profile_id) : bool
    {
        if ($this->getProfileProgress($a_profile_id) == 100) {
            return true;
        }
        return false;
    }

    /**
     * Get all profiles of user which are fulfilled or non-fulfilled
     * @return array<int, bool>
     */
    public function getAllProfileCompletionsForUser() : array
    {
        $user_profiles = ilSkillProfile::getProfilesOfUser($this->getUserId());
        $profile_comps = [];
        foreach ($user_profiles as $p) {
            if ($this->isProfileFulfilled($p["id"])) {
                $profile_comps[$p["id"]] = true;
            } else {
                $profile_comps[$p["id"]] = false;
            }
        }

        return $profile_comps;
    }

    /**
     * Write profile completion entries (fulfilled or non-fulfilled) of user for all profiles
     */
    public function writeCompletionEntryForAllProfiles() : void
    {
        $completions = $this->getAllProfileCompletionsForUser();
        foreach ($completions as $profile_id => $fulfilled) {
            $prof_comp_repo = new ilSkillProfileCompletionRepository($profile_id, $this->getUserId());
            if ($fulfilled) {
                $prof_comp_repo->addFulfilmentEntry();
            } else {
                $prof_comp_repo->addNonFulfilmentEntry();
            }
        }
    }

    /**
     * Write profile completion entry (fulfilled or non-fulfilled) of user for given profile
     */
    public function writeCompletionEntryForSingleProfile(int $a_profile_id) : void
    {
        $prof_comp_repo = new ilSkillProfileCompletionRepository($a_profile_id, $this->getUserId());

        if ($this->isProfileFulfilled($a_profile_id)) {
            $prof_comp_repo->addFulfilmentEntry();
        } else {
            $prof_comp_repo->addNonFulfilmentEntry();
        }
    }
}
