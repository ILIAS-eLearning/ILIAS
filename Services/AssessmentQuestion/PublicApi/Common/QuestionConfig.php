<?php
declare(strict_types=1);

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Common;

use ilAdvancedSelectionListGUI;
use ILIAS\UI\Component\Button\Primary;
use ILIAS\UI\Component\Button\Standard;

/**
 * Class QuestionConfig
 *
 * @package ILIAS\Services\AssessmentQuestion\PublicApi
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>$
 */
class QuestionConfig
{

    /**
     * @var null|string
     *
     * The ilAsqQuestionProcessingGUI will redirect to this action for getting the next question
     * If don't set this action no Next Button will not be displayed
     * Forward your command after choosing the question to the ilAsqQuestionProcessingGUI
     */
    protected $show_next_question_action = null;
    /**
     * @var null|string
     *
     * The ilAsqQuestionProcessingGUI will redirect to this action for getting the previous question.
     * If don't set this action no Previous Button will not be displayed
     */
    protected $show_previous_question_action = null;
    /**
     * @var null|string
     *
     * The ilAsqQuestionProcessingGUI will show this subline direct under the question title. E.g. Question 4 of 4 (2 Points)
     * If don't set the subline nothing will be displayed
     */
    protected $subline = null;
    /**
     * @var null|ilAdvancedSelectionListGUI
     */
    protected $question_page_action_menu = null;
    /**
     * @var array
     */
    protected $java_script_on_load_paths = [];
    /**
     * @var bool
     */
    protected $show_total_points_of_question = false;
    /**
     * @var bool
     */
    protected $hints_activated = false;
    /**
     * @var bool
     */
    protected $feedback_show_score = false;
    /**
     * @var bool
     */
    protected $feedback_for_answer_option = false;
    /**
     * @var bool
     */
    protected $feedback_show_correct_solution = false;
    /**
     * @var bool
     */
    protected $feedback_on_submit = false;
    /**
     * @var bool
     */
    protected $feedback_on_demand = false;


    /**
     * @return string|null
     */
    public function getShowNextQuestionAction() : ?string
    {
        return $this->show_next_question_action;
    }


    /**
     * @param string|null $show_next_question_action
     */
    public function setShowNextQuestionAction(?string $show_next_question_action) : void
    {
        $this->show_next_question_action = $show_next_question_action;
    }


    /**
     * @return string|null
     */
    public function getShowPreviousQuestionAction() : ?string
    {
        return $this->show_previous_question_action;
    }


    /**
     * @return string|null
     */
    public function getSubline() : ?string
    {
        return $this->subline;
    }


    /**
     * @param string|null $subline
     */
    public function setSubline(?string $subline) : void
    {
        $this->subline = $subline;
    }


    /**
     * @return ilAdvancedSelectionListGUI|null
     */
    public function getQuestionPageActionMenu() : ?ilAdvancedSelectionListGUI
    {
        return $this->question_page_action_menu;
    }


    /**
     * @param ilAdvancedSelectionListGUI|null $question_page_action_menu
     */
    public function setQuestionPageActionMenu(?ilAdvancedSelectionListGUI $question_page_action_menu) : void
    {
        $this->question_page_action_menu = $question_page_action_menu;
    }


    /**
     * @return array
     */
    public function getJavaScriptOnLoadPaths() : array
    {
        return $this->java_script_on_load_paths;
    }


    /**
     * @param array $java_script_on_load_paths
     */
    public function setJavaScriptOnLoadPaths(array $java_script_on_load_paths) : void
    {
        $this->java_script_on_load_paths = $java_script_on_load_paths;
    }

    /**
     * @return bool
     */
    public function isShowTotalPointsOfQuestion() : bool
    {
        return $this->show_total_points_of_question;
    }


    /**
     * @param bool $show_total_points_of_question
     */
    public function setShowTotalPointsOfQuestion(bool $show_total_points_of_question) : void
    {
        $this->show_total_points_of_question = $show_total_points_of_question;
    }


    /**
     * @param string|null $show_previous_question_action
     */
    public function setShowPreviousQuestionAction(?string $show_previous_question_action) : void
    {
        $this->show_previous_question_action = $show_previous_question_action;
    }


    /**
     * @return bool
     */
    public function isHintsActivated() : bool
    {
        return $this->hints_activated;
    }


    /**
     * @param bool $hints_activated
     */
    public function setHintsActivated(bool $hints_activated) : void
    {
        $this->hints_activated = $hints_activated;
    }


    /**
     * @return bool
     */
    public function isFeedbackShowScore() : bool
    {
        return $this->feedback_show_score;
    }


    /**
     * @param bool $feedback_show_score
     */
    public function setFeedbackShowScore(bool $feedback_show_score) : void
    {
        $this->feedback_show_score = $feedback_show_score;
    }


    /**
     * @return bool
     */
    public function isFeedbackForAnswerOption() : bool
    {
        return $this->feedback_for_answer_option;
    }


    /**
     * @param bool $feedback_for_answer_option
     */
    public function setFeedbackForAnswerOption(bool $feedback_for_answer_option) : void
    {
        $this->feedback_for_answer_option = $feedback_for_answer_option;
    }


    /**
     * @return bool
     */
    public function isFeedbackShowCorrectSolution() : bool
    {
        return $this->feedback_show_correct_solution;
    }


    /**
     * @param bool $feedback_show_correct_solution
     */
    public function setFeedbackShowCorrectSolution(bool $feedback_show_correct_solution) : void
    {
        $this->feedback_show_correct_solution = $feedback_show_correct_solution;
    }


    /**
     * @return bool
     */
    public function isFeedbackOnSubmit() : bool
    {
        return $this->feedback_on_submit;
    }


    /**
     * @param bool $feedback_on_submit
     */
    public function setFeedbackOnSubmit(bool $feedback_on_submit) : void
    {
        $this->feedback_on_submit = $feedback_on_submit;
    }


    /**
     * @return bool
     */
    public function isFeedbackOnDemand() : bool
    {
        return $this->feedback_on_demand;
    }


    /**
     * @param bool $feedback_on_demand
     */
    public function setFeedbackOnDemand(bool $feedback_on_demand) : void
    {
        $this->feedback_on_demand = $feedback_on_demand;
    }
}