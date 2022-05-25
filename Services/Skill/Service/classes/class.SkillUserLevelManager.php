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
 * Skill user level manager
 * @author famula@leifos.de
 */
class SkillUserLevelManager
{
    protected \ilSkillLevelRepository $level_repo;
    protected \ilSkillUserLevelRepository $user_level_repo;
    protected \ilSkillObjectAdapterInterface $obj_adapter;

    public function __construct(
        ?\ilSkillLevelRepository $a_level_repo = null,
        ?\ilSkillUserLevelRepository $a_user_level_repo = null,
        ?\ilSkillObjectAdapterInterface $a_obj_adapter = null
    ) {
        global $DIC;

        $this->level_repo = ($a_level_repo)
            ?: $DIC->skills()->internal()->repo()->getLevelRepo();
        $this->user_level_repo = ($a_user_level_repo)
            ?: $DIC->skills()->internal()->repo()->getUserLevelRepo();
        $this->obj_adapter = ($a_obj_adapter)
            ?: new \ilSkillObjectAdapter();
    }

    public function writeSkillLevel(
        int $user_id,
        int $a_level_id,
        int $a_trigger_ref_id,
        int $a_tref_id,
        bool $a_self_eval,
        string $a_unique_identifier,
        float $a_next_level_fulfilment
    ) : void {
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

        $status_date = "";
        $update = false;

        // self evaluations will update, if the last self evaluation is on the same day
        if ($a_self_eval && $this->user_level_repo->hasRecentSelfEvaluation(
            $trigger_obj_id,
            $user_id,
            $skill_id,
            $a_tref_id,
            $trigger_ref_id
        )) {
            $status_date = $this->user_level_repo->hasRecentSelfEvaluation(
                $trigger_obj_id,
                $user_id,
                $skill_id,
                $a_tref_id,
                $trigger_ref_id
            );
            if ($status_date != "") {
                $update = true;
            }
        }

        $this->user_level_repo->writeUserSkillLevelStatus(
            $skill_id,
            $trigger_ref_id,
            $trigger_obj_id,
            $trigger_title,
            $trigger_type,
            $update,
            $status_date,
            $a_level_id,
            $user_id,
            $a_tref_id,
            $a_self_eval,
            $a_unique_identifier,
            $a_next_level_fulfilment
        );
    }
}
