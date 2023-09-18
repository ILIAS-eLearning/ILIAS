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

namespace ILIAS\Skill\Personal;

use ILIAS\Skill\Service;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class SelfEvaluationManager
{
    /**
     * @param int $user_id user id
     * @param int $top_skill the "selectable" top skill
     * @param int $tref_id template reference id
     * @param int $basic_skill the basic skill the level belongs to
     * @param int $level level id
     */
    public function saveSelfEvaluation(
        int $user_id,
        int $top_skill,
        int $tref_id,
        int $basic_skill,
        int $level
    ): void {
        if ($level > 0) {
            \ilBasicSkill::writeUserSkillLevelStatus(
                $level,
                $user_id,
                0,
                $tref_id,
                \ilBasicSkill::ACHIEVED,
                false,
                true
            );
        } else {
            \ilBasicSkill::resetUserSkillLevelStatus($user_id, $basic_skill, $tref_id, 0, true);
        }
    }

    /**
     * @param int $user_id user id
     * @param int $top_skill the "selectable" top skill
     * @param int $tref_id template reference id
     * @param int $basic_skill the basic skill the level belongs to
     * @return int level id
     */
    public function getSelfEvaluation(
        int $user_id,
        int $top_skill,
        int $tref_id,
        int $basic_skill
    ): int {
        $bs = new \ilBasicSkill($basic_skill);
        return $bs->getLastLevelPerObject($tref_id, 0, $user_id, 1);
    }

    /**
     * @param int $user_id user id
     * @param int $top_skill the "selectable" top skill
     * @param int $tref_id template reference id
     * @param int $basic_skill the basic skill the level belongs to
     * @return string status date
     */
    public function getSelfEvaluationDate(
        int $user_id,
        int $top_skill,
        int $tref_id,
        int $basic_skill
    ): string {
        $bs = new \ilBasicSkill($basic_skill);
        return $bs->getLastUpdatePerObject($tref_id, 0, $user_id, 1);
    }
}
