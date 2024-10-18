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

namespace ILIAS\Test\Results\Data;

use ILIAS\Test\Results\Presentation\Settings as ResultPresentationSettings;
use ILIAS\Test\Participants\Participant;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;

class Factory
{
    /*
     * @var array<int test_obj_id, \ilTestEvaluationData> $test_data
     */
    private array $test_data = [];
    public function __construct(
        protected \ilTestShuffler $shuffler,
        protected UIFactory $ui_factory,
        protected UIRenderer $ui_renderer
    ) {
    }

    /**
     * @return array<\ilTestEvaluationPassData>
     */
    public function getAttemptIdsArrayFor(
        \ilObjTest $test_obj,
        int $active_id
    ): array {
        $eval = $this->retrieveResultData($test_obj);
        return array_keys($eval->getParticipant($active_id)->getPasses());
    }

    public function getOverviewDataForTest(
        \ilObjTest $test_obj
    ): ?TestOverview {
        $eval = $this->retrieveResultData($test_obj);
        $found_participants = $eval->getParticipants();
        if ($found_participants === []) {
            return null;
        }

        $total_passed = 0;
        $total_passed_reached = 0.0;
        $total_passed_max = 0.0;
        $total_passed_time = 0;
        foreach ($found_participants as $userdata) {
            if ($userdata->getMark()->getPassed()) {
                $total_passed++;
                $total_passed_reached += $userdata->getReached();
                $total_passed_max += $userdata->getMaxpoints();
                $total_passed_time += $userdata->getTimeOnTask();
            }
        }

        return new TestOverview(
            $test_obj->getId(),
            count($found_participants),
            $eval->getTotalFinishedParticipants(),
            $total_passed,
            $test_obj->evalTotalStartedAverageTime($eval->getParticipantIds()),
            $total_passed_time,
            $eval->getStatistics()->rankMedian(),
            $eval->getStatistics()->getEvaluationDataOfMedianUser()?->getMark()->getShortName() ?? '',
            $eval->getStatistics()->median(),
            $total_passed === 0 ? 0 : $total_passed_reached / $total_passed
        );
    }

    public function getAttemptOverviewFor(
        ResultPresentationSettings $settings,
        \ilObjTest $test_obj,
        int $active_id,
        ?int $attempt_id
    ): ?AttemptOverview {
        $eval = $this->retrieveResultData($test_obj);
        $found_participants = $eval->getParticipants();
        $participant_data = $eval->getParticipant($active_id);
        if ($attempt_id === null) {
            $attempt_id = $participant_data->getScoredPass();
        }
        if ($found_participants === []
            || $attempt_id === null) {
            return null;
        }

        $attempt_data = $participant_data?->getPass($attempt_id);
        if ($attempt_data === null) {
            return null;
        }

        return new AttemptOverview(
            $active_id,
            $attempt_id,
            $settings,
            $attempt_data->getExamId(),
            $attempt_data->getReachedPoints(),
            $attempt_data->getMaxPoints(),
            $attempt_data->getMark(),
            $attempt_data->getAnsweredQuestionCount(),
            $attempt_data->getQuestionCount(),
            $attempt_data->getRequestedHintsCount(),
            $attempt_data->getWorkingTime(),
            $participant_data->getFirstVisit(),
            $participant_data->getLastVisit(),
            $participant_data->getPassCount(),
            $participant_data->getScoredPass(),
            $eval->getStatistics()->rank($participant_data->getReached())
        );
    }

    /**
     *
     * @param array<ILIAS\Test\Participants\Participant> $participants
     * @return array<ILIAS\Test\Participants\Participant>
     */
    public function addAttemptOverviewInformationToParticipants(
        ResultPresentationSettings $settings,
        \ilObjTest $test_obj,
        array $participants
    ): array {
        return array_map(
            fn(Participant $v): Participant => $v->getActiveId() === null
                ? $v
                : $v->withAttemptOverviewInformation(
                    $this->getAttemptOverviewFor(
                        $settings,
                        $test_obj,
                        $v->getActiveId(),
                        null
                    )
                ),
            $participants
        );
    }

    public function getAttemptResultsFor(
        ResultPresentationSettings $settings,
        \ilObjTest $test_obj,
        int $active_id,
        int $attempt_id,
        bool $is_user_output
    ): AttemptResult {
        return $this->buildAttemptResults(
            $settings,
            $test_obj,
            $active_id,
            $attempt_id,
            $is_user_output
        );
    }

    private function buildAttemptResults(
        ResultPresentationSettings $settings,
        \ilObjTest $test_obj,
        int $active_id,
        int $attempt_id,
        bool $is_user_output
    ): AttemptResult {
        $question_results = [];

        $results = $test_obj->getTestResult(
            $active_id,
            $attempt_id,
            false, //$ordered_sequence
            $settings->getShowHiddenQuestions(),
            $settings->getShowOptionalQuestions()
        );

        // params of getSolutionOutput
        $graphical_output = false;
        $result_output = false;
        $show_question_only = $settings->getQuestionTextOnly();
        $show_feedback = false; //general
        $show_correct_solution = false;
        $show_manual_scoring = false;
        $show_question_text = true;
        $show_inline_feedback = true;

        foreach ($results as $idx => $qresult) {
            if (!is_numeric($idx)) {
                continue;
            }

            $qid = $qresult['qid'];
            $type = $qresult['type'];
            $title = $qresult['title'];
            $question_score = $qresult['max'];
            $usr_score = $qresult['reached'];
            $workedthrough = (bool) $qresult['workedthrough'];
            $answered = (bool) $qresult['answered'];
            $requested_hints = (int) $qresult['requested_hints'];


            $question_gui = $test_obj->createQuestionGUI('', $qid);
            $shuffle_trafo = $this->shuffler->getAnswerShuffleFor($qid, $active_id, $attempt_id);
            $question = $question_gui->getObject();
            $question->setShuffler($shuffle_trafo);
            $question_gui->setObject($question);

            $graphical_output = true;
            $show_correct_solution = false;
            $show_inline_feedback = $settings->getShowFeedback();
            $usr_solution = $question_gui->getSolutionOutput(
                $active_id,
                $attempt_id,
                $graphical_output,
                $result_output,
                $show_question_only,
                $show_feedback,
                $show_correct_solution,
                $show_manual_scoring,
                $show_question_text,
                $show_inline_feedback
            );

            if ($test_obj->getAutosave() &&
                $type === 'assTextQuestion'
            ) {
                $usr_solution .= $question_gui->getAutoSavedSolutionOutput(
                    $active_id,
                    $attempt_id,
                    false,
                    false,
                    false,
                    false,
                    false,
                    false,
                    false,
                    true
                );
            }

            $graphical_output = false;
            $show_correct_solution = true;
            $show_inline_feedback = false;
            $best_solution = $question_gui->getSolutionOutput(
                $active_id,
                $attempt_id,
                $graphical_output,
                $result_output,
                $show_question_only,
                $show_feedback,
                $show_correct_solution,
                $show_manual_scoring,
                $show_question_text,
                $show_inline_feedback
            );

            if ($show_question_only) {
                $usr_solution = $this->ui_renderer->render($this->ui_factory->legacy('<div class="ilc_question_Standard">' . $usr_solution . '</div>'));
                $best_solution = $this->ui_renderer->render($this->ui_factory->legacy('<div class="ilc_question_Standard">' . $best_solution . '</div>'));
            }

            $feedback = $question_gui->getGenericFeedbackOutput($active_id, $attempt_id);

            $recapitulation = null;
            if ($is_user_output && $settings->getShowRecapitulation()) {
                $recapitulation = $question_gui->getObject()->getSuggestedSolutionOutput();
            }

            $question_results[] = new QuestionResult(
                $qid,
                $type,
                $title,
                $question_score,
                $usr_score,
                $usr_solution,
                $best_solution,
                $feedback,
                $workedthrough,
                $answered,
                $requested_hints,
                $recapitulation
            );
        }

        return new AttemptResult(
            $active_id,
            $attempt_id,
            $question_results
        );
    }

    private function retrieveResultData(\ilObjTest $test_obj): \ilTestEvaluationData
    {
        if (!isset($this->test_data[$test_obj->getId()])) {
            $test_obj->setAccessFilteredParticipantList(
                $test_obj->buildStatisticsAccessFilteredParticipantList()
            );

            $this->test_data[$test_obj->getId()] = $test_obj->getCompleteEvaluationData();
        }

        return $this->test_data[$test_obj->getId()];
    }
}
