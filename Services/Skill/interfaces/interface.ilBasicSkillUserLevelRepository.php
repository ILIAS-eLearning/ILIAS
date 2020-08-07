<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilBasicSkillUserLevelRepository
 */
interface ilBasicSkillUserLevelRepository
{

    /**
     * Delete user levels of a skill
     * @param int $skill_id
     */
    public function deleteUserLevelsOfSkill(int $skill_id);

    /**
     * Reset skill level status. This is currently only used for self evaluations with a "no competence" level.
     * It has to be discussed, how this should be provided for non-self-evaluations.
     * @param bool  $update           update or insert
     * @param int   $trigger_obj_id   triggering object id
     * @param mixed $status_date      date status
     * @param int   $a_user_id        user id
     * @param int   $a_skill_id       skill id
     * @param int   $a_tref_id        skill tref id
     * @param int   $a_trigger_ref_id triggering repository object ref id
     * @param bool  $a_self_eval      currently needs to be set to true
     * @throws ilSkillException
     */
    public function resetUserSkillLevelStatus(
        bool $update,
        int $trigger_obj_id,
        $status_date,
        int $a_user_id,
        int $a_skill_id,
        int $a_tref_id = 0,
        int $a_trigger_ref_id = 0,
        bool $a_self_eval = false
    );

    /**
     * Has recent self evaluation. Check if self evaluation for user/object has been done on the same day
     * already
     * @param int $trigger_obj_id   triggering object id
     * @param int $a_user_id        user id
     * @param int $a_skill_id       skill id
     * @param int $a_tref_id        skill tref id
     * @param int $a_trigger_ref_id triggering repository object ref id
     * @return mixed
     */
    public function hasRecentSelfEvaluation(
        int $trigger_obj_id,
        int $a_user_id,
        int $a_skill_id,
        int $a_tref_id = 0,
        int $a_trigger_ref_id = 0
    );

    /**
     * Get new achievements
     * @param string $a_timestamp
     * @param string $a_timestamp_to
     * @param int    $a_user_id
     * @param int    $a_self_eval
     * @return array
     */
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
     * @param mixed       $status_date             date status
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
        $status_date,
        int $a_level_id,
        int $a_user_id,
        int $a_tref_id = 0,
        bool $a_self_eval = false,
        string $a_unique_identifier = "",
        float $a_next_level_fulfilment = 0.0
    );

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

    /**
     * Remove all data of a user
     * @param int $a_user_id
     */
    public function removeAllUserData(int $a_user_id);

    /**
     * Get max levels per type
     * @param int    $skill_id
     * @param array  $levels
     * @param int    $a_tref_id
     * @param string $a_type
     * @param int    $a_user_id
     * @param int    $a_self_eval
     * @return int
     */
    public function getMaxLevelPerType(
        int $skill_id,
        array $levels,
        int $a_tref_id,
        string $a_type,
        int $a_user_id = 0,
        int $a_self_eval = 0
    ) : int;

    /**
     * Get all level entries
     * @param int $skill_id
     * @param int $a_tref_id
     * @param int $a_user_id
     * @param int $a_self_eval
     * @return array
     */
    public function getAllLevelEntriesOfUser(
        int $skill_id,
        int $a_tref_id,
        int $a_user_id = 0,
        int $a_self_eval = 0
    ) : array;

    /**
     * Get all historic level entries
     * @param int $skill_id
     * @param int $a_tref_id
     * @param int $a_user_id
     * @param int $a_eval_by
     * @return array
     */
    public function getAllHistoricLevelEntriesOfUser(
        int $skill_id,
        int $a_tref_id,
        int $a_user_id = 0,
        int $a_eval_by = 0
    ) : array;

    /**
     * Get max levels per object
     * @param int   $skill_id
     * @param array $levels
     * @param int   $a_tref_id
     * @param int   $a_object_id
     * @param int   $a_user_id
     * @param int   $a_self_eval
     * @return int
     */
    public function getMaxLevelPerObject(
        int $skill_id,
        array $levels,
        int $a_tref_id,
        int $a_object_id,
        int $a_user_id = 0,
        int $a_self_eval = 0
    ) : int;

    /**
     * Get max levels
     * @param int   $skill_id
     * @param array $levels
     * @param int   $a_tref_id
     * @param int   $a_user_id
     * @param int   $a_self_eval
     * @return int
     */
    public function getMaxLevel(
        int $skill_id,
        array $levels,
        int $a_tref_id,
        int $a_user_id = 0,
        int $a_self_eval = 0
    ) : int;

    /**
     * Has user self evaluated a skill?
     * @param int $a_user_id
     * @param int $a_skill_id
     * @param int $a_tref_id
     * @return bool
     */
    public function hasSelfEvaluated(int $a_user_id, int $a_skill_id, int $a_tref_id) : bool;

    /**
     * Get last level set per object
     * @param int $skill_id
     * @param int $a_tref_id
     * @param int $a_object_id
     * @param int $a_user_id
     * @param int $a_self_eval
     * @return null|int
     */
    public function getLastLevelPerObject(
        int $skill_id,
        int $a_tref_id,
        int $a_object_id,
        int $a_user_id = 0,
        int $a_self_eval = 0
    ) : ?int;

    /**
     * Get last update per object
     * @param int $skill_id
     * @param int $a_tref_id
     * @param int $a_object_id
     * @param int $a_user_id
     * @param int $a_self_eval
     * @return null|string
     */
    public function getLastUpdatePerObject(
        int $skill_id,
        int $a_tref_id,
        int $a_object_id,
        int $a_user_id = 0,
        int $a_self_eval = 0
    ) : ?string;
}
