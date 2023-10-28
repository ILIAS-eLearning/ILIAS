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
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Survey\Sequence;

use ILIAS\Survey\InternalRepoService;
use ILIAS\Survey\InternalDomainService;

class SequenceManager
{
    protected \ilObjSurvey $survey;
    protected \ilLogger $log;
    protected int $survey_id;
    protected InternalDomainService $domain;
    protected SequenceDBRepository $question_repo;

    public function __construct(
        InternalRepoService $repo,
        InternalDomainService $domain,
        int $survey_id,
        \ilObjSurvey $survey
    ) {
        $this->question_repo = $repo->sequence();
        $this->domain = $domain;
        $this->survey_id = $survey_id;        // not object id
        $this->log = $domain->log();
        $this->survey = $survey;
    }

    public function appendQuestion(
        int $survey_question_id,
        bool $duplicate = true,
        bool $force_duplicate = false
    ): int {
        $this->log->debug("append question, id: " . $survey_question_id . ", duplicate: " . $duplicate . ", force: " . $force_duplicate);

        // create duplicate if pool question (or forced for question blocks copy)
        if ($duplicate) {
            // this does nothing if this is not a pool question and $a_force_duplicate is false
            $survey_question_id = $this->survey->duplicateQuestionForSurvey($survey_question_id, $force_duplicate);
        }

        // check if question is not already in the survey, see #22018
        if ($this->survey->isQuestionInSurvey($survey_question_id)) {
            return $survey_question_id;
        }

        // append to survey
        $next_id = $this->question_repo->insert($this->survey_id, $survey_question_id);

        $this->log->debug("insert svy_svy_qst, id: " . $next_id . ", qfi: " . $survey_question_id);

        return $survey_question_id;
    }

}
