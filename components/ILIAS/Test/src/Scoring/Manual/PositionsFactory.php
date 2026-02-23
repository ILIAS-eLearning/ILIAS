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

namespace ILIAS\Test\Scoring\Manual;

use ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository;

class PositionsFactory
{
    public function __construct(
        private readonly \ilObjTest $test_obj,
        private readonly GeneralQuestionPropertiesRepository $question_repo
    ) {
    }

    public function get(): Positions
    {
        $user_questions = [];
        $user_attempts = [];
        $question_properties = [];
        foreach (array_keys($this->test_obj->getTestParticipants()) as $usr_active_id) {
            $attempt = \ilObjTest::_getResultPass($usr_active_id);
            $user_attempts[$usr_active_id] = $attempt;
            $user_questions[$usr_active_id] = $this->test_obj->isRandomTest()
                ? array_map(
                    fn($q) => $q['question_fi'],
                    $this->test_obj->getQuestionsOfPass($usr_active_id, $attempt)
                )
                : $this->test_obj->getQuestions();
        }

        $question_ids = array_unique(array_merge(...array_values($user_questions)));
        foreach ($question_ids as $qid) {
            $question_properties[$qid] = $this->question_repo->getForQuestionId($qid);
        }

        $qtypes = $this->test_obj->getGlobalSettings()->getDisabledQuestionTypes();

        $question_properties = array_filter(
            $question_properties,
            fn($qprops, $id) => !in_array($qprops->getTypeId(), $qtypes),
            ARRAY_FILTER_USE_BOTH
        );

        $user_questions = array_map(
            fn($question_ids) => array_filter(
                $question_ids,
                fn($qid) => array_key_exists($qid, $question_properties)
            ),
            $user_questions
        );

        return new Positions(
            $user_questions,
            $user_attempts,
            $question_properties
        );
    }
}
