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
    ): self {
        $clone = clone $this;
        $clone->test_obj = $test_obj;
        $clone->active_id = $active_id;
        $clone->pass_id = $pass_id;
        return $clone;
    }

    protected function getResults(): ilTestResult
    {
        $settings = $this->getResultsSettings();
        $question_results = [];
        /*
        public function &getTestResult(
            $active_id,
            $pass = null,
            bool $ordered_sequence = false,
            bool $considerHiddenQuestions = true,
            bool $considerOptionalQuestions = true
        */
        $results = $this->test_obj->getTestResult(
            $this->active_id,
            $this->pass_id,
            false,
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
            /**
            public function getSolutionOutput(
                $active_id,
                $pass = null,
              1 $graphicalOutput = false,
              2 $result_output = false,
              3 $show_question_only = true,
              4 $show_feedback = false,
              5 $show_correct_solution = false,
              6 $show_manual_scoring = false,
              7 $show_question_text = true
            */

            $question_gui = $this->test_obj->createQuestionGUI("", $qid);
            $shuffle_trafo = $this->shuffler->getAnswerShuffleFor($qid, $this->active_id, $this->pass_id);
            $question_gui->object->setShuffler($shuffle_trafo);

            $usr_solution = $question_gui->getSolutionOutput($this->active_id, $this->pass_id, true, false, false, false);
            $best_solution = $question_gui->getSolutionOutput($this->active_id, $this->pass_id, false, false, false, false, true);
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

        $environment = (new ilTestResultsSettings())
            ->withShowHiddenQuestions(false)
            ->withShowOptionalQuestions(
                true
                //!$this->getObjectiveOrientedContainer()->isObjectiveOrientedPresentationRequired()
            )
            ->withShowBestSolution((bool)ilSession::get('tst_results_show_best_solutions'))
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
