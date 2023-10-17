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

namespace ILIAS\TestQuestionPool\Skills;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class TestQuestionPoolSkillDeletionManager
{
    protected TestQuestionPoolSkillDBRepository $qpl_skill_repo;

    public function __construct(
        TestQuestionPoolSkillDBRepository $qpl_skill_repo = null
    ) {
        global $DIC;

        $this->qpl_skill_repo = ($qpl_skill_repo)
            ?: $DIC->skills()->internalTestQuestionPool()->repo()->getTestQuestionPoolSkillRepo();
    }

    public function removeTestQuestionPoolSkillsForSkill(int $skill_node_id, bool $is_reference = false): void
    {
        $this->qpl_skill_repo->removeForSkill($skill_node_id, $is_reference);
    }
}
