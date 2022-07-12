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
 * Interface ilSkillUserLevelRepository
 */
interface ilSkillUserLevelRepository
{
    public function deleteUserLevelsOfSkill(int $skill_id) : void;

    /**
     * Reset skill level status. This is currently only used for self evaluations with a "no competence" level.
     * It has to be discussed, how this should be provided for non-self-evaluations.
     * @param bool   $update           update or insert
     * @param int    $trigger_obj_id   triggering object id
     * @param string $status_date     date status
     * @param int    $a_user_id        user id
     * @param int    $a_skill_id       skill id
     * @param int    $a_tref_id        skill tref id
     * @param int    $a_trigger_ref_id triggering repository object ref id
     * @param bool   $a_self_eval      currently needs to be set to true
     * @throws ilSkillException
     */
    public function resetUserSkillLevelStatus(
        bool $update,
        int $trigger_obj_id,
        string $status_date,
        int $a_user_id,
        int $a_skill_id,
        int $a_tref_id = 0,
        int $a_trigger_ref_id = 0,
        bool $a_self_eval = false
    ) : void;

    /**
     * Has recent self evaluation. Check if self evaluation for user/object has been done on the same day
     * already
     * @param int $trigger_obj_id   triggering object id
     * @param int $a_user_id        user id
     * @param int $a_skill_id       skill id
     * @param int $a_tref_id        skill tref id
     * @param int $a_trigger_ref_id triggering repository object ref id
     * @return string
     */
    public function hasRecentSelfEvaluation(
        int $trigger_obj_id,
        int $a_user_id,
        int $a_skill_id,
        int $a_tref_id = 0,
        int $a_trigger_ref_id = 0
    ) : string;

    public function getNewAchievementsPerUser(
        string $a_timestamp,
        string $a_timestamp_to = null,
        int $a_user_id = 0,
        int $a_self_eval = 0
    ) : array;

    /**
     * Write skill level status
     * @param int         $skill_id                skill id
     * @param int         $trigger_ref_id          triggering repository object ref id
     * @param int         $trigger_obj_id          triggering object id
     * @param null|string $trigger_title           triggering object title
     * @param null|string $trigger_type            triggering object type
     * @param bool        $update                  update or insert
     * @param string      $status_date             date status
     * @param int         $a_level_id              skill level id
     * @param int         $a_user_id               user id
     * @param int         $a_tref_id               skill tref id
     * @param bool        $a_self_eval             self evaluation
     * @param string      $a_unique_identifier     a  unique identifier (should be used with trigger_ref_id > 0)
     * @param float       $a_next_level_fulfilment next level percentage fulfilment value (value must be >=0 and <1)
     */
    public function writeUserSkillLevelStatus(
        int $skill_id,
        int $trigger_ref_id,
        int $trigger_obj_id,
        ?string $trigger_title,
        ?string $trigger_type,
        bool $update,
        string $status_date,
        int $a_level_id,
        int $a_user_id,
        int $a_tref_id = 0,
        bool $a_self_eval = false,
        string $a_unique_identifier = "",
        float $a_next_level_fulfilment = 0.0,
        string $trigger_user_id = ""
    ) : void;

    /**
     * Remove a user skill completely
     * @param int    $a_user_id           user id
     * @param int    $a_trigger_obj_id    triggering repository object obj id
     * @param bool   $a_self_eval         currently needs to be set to true
     * @param string $a_unique_identifier unique identifier string
     * @return bool true, if entries have been deleted, otherwise false
     */
    public function removeAllUserSkillLevelStatusOfObject(
        int $a_user_id,
        int $a_trigger_obj_id,
        bool $a_self_eval = false,
        string $a_unique_identifier = ""
    ) : bool;

    public function removeAllUserData(int $a_user_id) : void;

    public function getMaxLevelPerType(
        int $skill_id,
        array $levels,
        int $a_tref_id,
        string $a_type,
        int $a_user_id = 0,
        int $a_self_eval = 0
    ) : int;

    public function getNextLevelFulfilmentPerType(
        int $skill_id,
        int $a_tref_id,
        string $a_type,
        int $a_user_id = 0,
        int $a_self_eval = 0
    ) : float;

    public function getAllLevelEntriesOfUser(
        int $skill_id,
        int $a_tref_id,
        int $a_user_id = 0,
        int $a_self_eval = 0
    ) : array;

    public function getAllHistoricLevelEntriesOfUser(
        int $skill_id,
        int $a_tref_id,
        int $a_user_id = 0,
        int $a_eval_by = 0
    ) : array;

    public function getMaxLevelPerObject(
        int $skill_id,
        array $levels,
        int $a_tref_id,
        int $a_object_id,
        int $a_user_id = 0,
        int $a_self_eval = 0
    ) : int;

    public function getNextLevelFulfilmentPerObject(
        int $skill_id,
        int $a_tref_id,
        int $a_object_id,
        int $a_user_id = 0,
        int $a_self_eval = 0
    ) : float;

    public function getMaxLevel(
        int $skill_id,
        array $levels,
        int $a_tref_id,
        int $a_user_id = 0,
        int $a_self_eval = 0
    ) : int;

    public function getNextLevelFulfilment(
        int $skill_id,
        int $a_tref_id,
        int $a_user_id = 0,
        int $a_self_eval = 0
    ) : float;

    public function hasSelfEvaluated(int $a_user_id, int $a_skill_id, int $a_tref_id) : bool;

    public function getLastLevelPerObject(
        int $skill_id,
        int $a_tref_id,
        int $a_object_id,
        int $a_user_id = 0,
        int $a_self_eval = 0
    ) : int;

    public function getLastUpdatePerObject(
        int $skill_id,
        int $a_tref_id,
        int $a_object_id,
        int $a_user_id = 0,
        int $a_self_eval = 0
    ) : ?string;
}
