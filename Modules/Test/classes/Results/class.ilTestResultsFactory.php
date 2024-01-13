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

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;

/**
 * @package Modules/Test
 * Results (currently, for one user and pass)
 */
class ilTestResultsFactory
{
    /**
     * @param ilQuestionResult[] $question_results
     */
    public function __construct(
        protected ilTestShuffler $shuffler,
        protected UIFactory $ui_factory,
        protected UIRenderer $ui_renderer
    ) {
    }
    public function getPassResultsFor(
        ilObjTest $test_obj,
        int $active_id,
        int $pass_id,
        bool $is_user_output = true
    ): ilTestPassResult {
        $settings = $this->getPassResultsSettings($test_obj, $is_user_output);
        return $this->buildPassResults(
            $settings,
            $test_obj,
            $active_id,
            $pass_id,
            $is_user_output
        );
    }

    protected function buildPassResults(
        ilTestPassResultsSettings $settings,
        ilObjTest $test_obj,
        int $active_id,
        int $pass_id,
        bool $is_user_output
    ): ilTestPassResult {
        $question_results = [];

        $results = $test_obj->getTestResult(
            $active_id,
            $pass_id,
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
            $workedthrough = (bool)$qresult['workedthrough'];
            $answered = (bool)$qresult['answered'];

            $question_gui = $test_obj->createQuestionGUI("", $qid);
            $shuffle_trafo = $this->shuffler->getAnswerShuffleFor($qid, $active_id, $pass_id);
            $question_gui->object->setShuffler($shuffle_trafo);

            $graphical_output = true;
            $show_correct_solution = false;
            $show_inline_feedback = $settings->getShowFeedback();
            $usr_solution = $question_gui->getSolutionOutput(
                $active_id,
                $pass_id,
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
                    $pass_id,
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
                $pass_id,
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

            $feedback = $question_gui->getGenericFeedbackOutput($active_id, $pass_id);

            $recapitulation = null;
            if ($is_user_output && $settings->getShowRecapitulation()) {
                $recapitulation = $question_gui->object->getSuggestedSolutionOutput();
            }

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
                $answered,
                $recapitulation
            );
        }

        return new ilTestPassResult(
            $settings,
            $active_id,
            $pass_id,
            $question_results
        );
    }

    protected function getPassResultsSettings(
        ilObjTest $test_obj,
        bool $is_user_output
    ): ilTestPassResultsSettings {
        $settings = $test_obj->getScoreSettings();
        $settings_summary = $settings->getResultSummarySettings();
        $settings_result = $settings->getResultDetailsSettings();

        $show_hidden_questions = false;
        $show_optional_questions = true;
        $show_best_solution = $is_user_output ?
            $settings_result->getShowSolutionListComparison() :
            (bool)ilSession::get('tst_results_show_best_solutions');
        $show_feedback = $settings_result->getShowSolutionFeedback();
        $show_question_text_only = $settings_result->getShowSolutionAnswersOnly();
        $show_content_for_recapitulation = $settings_result->getShowSolutionSuggested();

        return new ilTestPassResultsSettings(
            $show_hidden_questions,
            $show_optional_questions,
            $show_best_solution,
            $show_feedback,
            $show_question_text_only,
            $show_content_for_recapitulation
        );
    }
}
