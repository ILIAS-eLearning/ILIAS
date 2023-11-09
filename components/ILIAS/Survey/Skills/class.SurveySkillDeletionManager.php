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

namespace ILIAS\Survey\Skills;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class SurveySkillDeletionManager
{
    protected SurveySkillDBRepository $survey_skill_repo;

    public function __construct(
        SurveySkillDBRepository $survey_skill_repo = null
    ) {
        global $DIC;

        $this->survey_skill_repo = ($survey_skill_repo)
            ?: $DIC->skills()->internalSurvey()->repo()->getSurveySkillRepo();
    }

    public function removeSurveySkillsForSkill(int $skill_node_id, bool $is_reference = false): void
    {
        $this->survey_skill_repo->removeForSkill($skill_node_id, $is_reference);
    }
}
