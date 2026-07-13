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
            if ($userdata->getMark()?->getPassed()) {
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
            $eval->getStatistics()->getEvaluationDataOfMedianUser()?->getMark()?->getShortName() ?? '',
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
            $attempt_id = $participant_data?->getScoredPass();
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
            $attempt_data->getWorkingTime(),
            $participant_data->getTimeOnTask(),
            $attempt_data->getStartTime(),
            $attempt_data->getLastAccessTime(),
            $participant_data->getPassCount(),
            $participant_data->getScoredPass(),
            $eval->getStatistics()->rank($participant_data->getReached()),
            $attempt_data->getStatusOfAttempt()
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
            function (Participant $v) use ($settings, $test_obj, $participants): Participant {
                if ($v->getActiveId() === null) {
                    return $v;
                }

                $scored_attempt = $this->getAttemptOverviewFor(
                    $settings,
                    $test_obj,
                    $v->getActiveId(),
                    null
                );

                if ($scored_attempt !== null
                    && $scored_attempt->getStatusOfAttempt() === StatusOfAttempt::RUNNING
                    && $scored_attempt->getStartedDate() !== null) {
                    $v = $v->withRunningAttemptStart($scored_attempt->getStartedDate());
                }

                return $v->withAttemptOverviewInformation(
                    $scored_attempt
                );
            },
            $participants
        );
    }

    public function getAttemptResultsFor(
        ResultPresentationSettings $settings,
        \ilObjTest $test_obj,
        int $active_id,
        int $attempt_id,
        bool $is_user_output
    ): AttemptSolutions {
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
    ): AttemptSolutions {
        $question_results = [];

        $results = $test_obj->getTestResult(
            $active_id,
            $attempt_id,
            false, //$ordered_sequence
            $settings->getShowHiddenQuestions(),
            $settings->getShowOptionalQuestions()
        );

        // params of getSolutionOutput
        $show_question_only = $settings->getQuestionTextOnly();

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

            $question_gui = $test_obj->createQuestionGUI('', $qid);
            $shuffle_trafo = $this->shuffler->getAnswerShuffleFor($qid, $active_id, $attempt_id);
            $question = $question_gui->getObject();
            $question->setShuffler($shuffle_trafo);
            $question_gui->setObject($question);

            $show_feedback = $settings->getShowFeedback();
            $usr_solution = $question_gui->getSolutionOutput(
                $active_id,
                $attempt_id,
                true,
                false,
                $show_question_only,
                $show_feedback,
                false,
                false,
                true,
                true
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

            $best_solution = $question_gui->getSolutionOutput(
                $active_id,
                $attempt_id,
                false,
                false,
                $show_question_only,
                false,
                true,
                false,
                true,
                false
            );

            if ($show_question_only) {
                $usr_solution = $this->ui_renderer->render($this->ui_factory->legacy()->content('<div class="ilc_question_Standard">' . $usr_solution . '</div>'));
                $best_solution = $this->ui_renderer->render($this->ui_factory->legacy()->content('<div class="ilc_question_Standard">' . $best_solution . '</div>'));
            }

            $feedback = $question_gui->getGenericFeedbackOutput($active_id, $attempt_id);

            $recapitulation = $is_user_output && $settings->getShowRecapitulation()
                ? $question->getSuggestedSolutionOutput()
                : null;

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
                $recapitulation,
                $idx
            );
        }

        return new AttemptSolutions(
            $active_id,
            $attempt_id,
            $question_results
        );
    }

    private function retrieveResultData(\ilObjTest $test_obj): \ilTestEvaluationData
    {
        if (!isset($this->test_data[$test_obj->getId()])) {
            $this->test_data[$test_obj->getId()] = $test_obj->getCompleteEvaluationData();
        }

        return $this->test_data[$test_obj->getId()];
    }
}
