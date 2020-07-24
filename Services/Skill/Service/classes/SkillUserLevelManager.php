<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Skill\Service;

/**
 * Skill user level manager
 * @author famula@leifos.de
 */
class SkillUserLevelManager
{
    /**
     * @var \ilBasicSkillLevelRepository
     */
    protected $level_repo;

    /**
     * @var \ilBasicSkillUserLevelRepository
     */
    protected $user_level_repo;

    /**
     * @var \ilSkillObjectAdapterInterface
     */
    protected $obj_adapter;

    /**
     * Constructor
     * @param \ilBasicSkillLevelRepository|null     $a_level_repo
     * @param \ilBasicSkillUserLevelRepository|null $a_user_level_repo
     * @param \ilSkillObjectAdapterInterface|null   $a_obj_adapter
     */
    public function __construct(
        \ilBasicSkillLevelRepository $a_level_repo = null,
        \ilBasicSkillUserLevelRepository $a_user_level_repo = null,
        \ilSkillObjectAdapterInterface $a_obj_adapter = null
    ) {
        global $DIC;

        $this->level_repo = ($a_level_repo)
            ? $a_level_repo
            : $DIC->skills()->internal()->repo()->getLevelRepo();
        $this->user_level_repo = ($a_user_level_repo)
            ? $a_user_level_repo
            : $DIC->skills()->internal()->repo()->getUserLevelRepo();
        $this->obj_adapter = ($a_obj_adapter)
            ? $a_obj_adapter
            : new \ilSkillObjectAdapter();
    }

    /**
     * @param int    $user_id
     * @param int    $a_level_id
     * @param int    $a_trigger_ref_id
     * @param int    $a_tref_id
     * @param bool   $a_self_eval
     * @param string $a_unique_identifier
     * @param float  $a_next_level_fulfilment
     */
    public function writeSkillLevel(
        int $user_id,
        int $a_level_id,
        int $a_trigger_ref_id,
        int $a_tref_id,
        bool $a_self_eval,
        string $a_unique_identifier,
        float $a_next_level_fulfilment
    ) {
        $skill_id = $this->level_repo->lookupLevelSkillId($a_level_id);
        $trigger_ref_id = $a_trigger_ref_id;
        $trigger_obj_id = $this->obj_adapter->getObjIdForRefId($trigger_ref_id);
        $trigger_title = $this->obj_adapter->getTitleForObjId($trigger_obj_id);
        $trigger_type = $this->obj_adapter->getTypeForObjId($trigger_obj_id);

        //next level percentage fulfilment value must be >=0 and <1
        if (!($a_next_level_fulfilment >= 0) || !($a_next_level_fulfilment < 1)) {
            throw new \UnexpectedValueException(
                "Next level fulfilment must be equal to or greater than 0 and less than 1, '" .
                $a_next_level_fulfilment . "' given."
            );
        }

        $update = false;

        // self evaluations will update, if the last self evaluation is on the same day
        if ($a_self_eval && $this->user_level_repo->hasRecentSelfEvaluation($trigger_obj_id, $user_id, $skill_id,
                $a_tref_id, $trigger_ref_id)) {
            $status_date = $this->user_level_repo->hasRecentSelfEvaluation($trigger_obj_id, $user_id, $skill_id,
                $a_tref_id, $trigger_ref_id);
            if ($status_date != "") {
                $update = true;
            }
        }

        $this->user_level_repo->writeUserSkillLevelStatus($skill_id, $trigger_ref_id, $trigger_obj_id, $trigger_title,
            $trigger_type, $update, $status_date, $a_level_id, $user_id, $a_tref_id, $a_self_eval, $a_unique_identifier,
            $a_next_level_fulfilment);
    }
}