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

/**
 * @package Modules/Test
 * Results for one user and pass
 */
class ilTestResultsFactory
{
    protected ilObjTest $test_obj;
    protected int $active_id;
    protected int $pass_id;
    protected bool $is_user_output = true;

    /**
     * @param ilQuestionResult[] $question_results
     */
    public function __construct(
        protected ilTestShuffler $shuffler,
        protected ILIAS\UI\Factory $ui_factory,
        protected ILIAS\UI\Renderer $ui_renderer,
        protected ILIAS\Refinery\Factory $refinery,
        protected ILIAS\Data\Factory $data_factory,
        protected ILIAS\HTTP\Services $http,
        protected ilLanguage $lng
    ) {
    }
    public function for(
        ilObjTest $test_obj,
        int $active_id,
        int $pass_id,
        bool $is_user_output = true
    ): self {
        $clone = clone $this;
        $clone->test_obj = $test_obj;
        $clone->active_id = $active_id;
        $clone->pass_id = $pass_id;
        $clone->is_user_output = $is_user_output;
        return $clone;
    }

    protected function getResults(): ilTestResult
    {
        $settings = $this->getResultsSettings();
        $question_results = [];

        $results = $this->test_obj->getTestResult(
            $this->active_id,
            $this->pass_id,
            false, //$ordered_sequence
            $settings->getShowHiddenQuestions(),
            $settings->getShowOptionalQuestions()
        );

        foreach ($results as $idx => $qresult) {
            if (! is_numeric($idx)) {
                continue;
            }

            $qid = $qresult['qid'];
            $type = $qresult['type'];
            $title = $qresult['title'];
            $question_score = $qresult['max'];
            $usr_score = $qresult['reached'];
            $workedthrough = (bool)$qresult['workedthrough'];
            $answered = (bool)$qresult['answered'];

            // params of getSolutionOutput
            $graphical_output = false;
            $result_output = false;
            $show_question_only = false;
            $show_feedback = false;
            $show_correct_solution = false;
            $show_manual_scoring = false;
            $show_question_text = false;

            $question_gui = $this->test_obj->createQuestionGUI("", $qid);
            $shuffle_trafo = $this->shuffler->getAnswerShuffleFor($qid, $this->active_id, $this->pass_id);
            $question_gui->object->setShuffler($shuffle_trafo);

            $graphical_output = true;
            $usr_solution = $question_gui->getSolutionOutput(
                $this->active_id,
                $this->pass_id,
                $graphical_output,
                $result_output,
                $show_question_only,
                $show_feedback
            );

            $graphical_output = false;
            $show_correct_solution = true;
            $best_solution = $question_gui->getSolutionOutput(
                $this->active_id,
                $this->pass_id,
                $graphical_output,
                $result_output,
                $show_question_only,
                $show_feedback,
                $show_correct_solution
            );

            $feedback = $question_gui->getGenericFeedbackOutput($this->active_id, $this->pass_id);

            $question_results[] = new ilQuestionResult(
                $qid,
                $type,
                $title,
                $question_score,
                $usr_score,
                $usr_solution,
                $best_solution,
                $feedback,
                $workedthrough,
                $answered
            );
        }

        return new ilTestResult(
            $this->active_id,
            $this->pass_id,
            $question_results
        );
    }

    protected function getResultsSettings(): ilTestResultsSettings
    {
        $settings = $this->test_obj->getScoreSettings();
        $settings_summary = $settings->getResultSummarySettings();
        $settings_result = $settings->getResultDetailsSettings();


        $show_best_solution = $this->is_user_output ?
            $settings_result->getShowSolutionListOwnAnswers() :
            (bool)ilSession::get('tst_results_show_best_solutions');

        $environment = (new ilTestResultsSettings())
            ->withShowHiddenQuestions(false)
            ->withShowOptionalQuestions(true)
            ->withShowBestSolution($show_best_solution)
            ->withShowFeedback($settings_result->getShowSolutionFeedback());

        return $environment;
    }

    public function getTable(string $title = ''): ilTestResultsOverviewTable
    {
        return  new ilTestResultsOverviewTable(
            $this->ui_factory,
            $this->ui_renderer,
            $this->refinery,
            $this->http,
            $this->data_factory,
            $this->lng,
            $title,
            $this->getResults(),
            $this->getResultsSettings()
        );
    }
}
