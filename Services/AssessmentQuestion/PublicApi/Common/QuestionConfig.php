<?php
declare(strict_types=1);

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Common;

use ILIAS\UI\Component\Button\Primary;

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
     * @var Primary|Null
     */
    protected $btn_next = NULL;
    /**
     * @var Primary|Null
     */
    protected $btn_prev = NULL;


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


    /**
     * @return Primary|Null
     */
    public function getBtnNext() : ?Primary
    {
        return $this->btn_next;
    }


    /**
     * @param Primary|Null $btn_next
     */
    public function setBtnNext(?Primary $btn_next) : void
    {
        $this->btn_next = $btn_next;
    }


    /**
     * @return Primary|Null
     */
    public function getBtnPrev() : ?Primary
    {
        return $this->btn_prev;
    }


    /**
     * @param Primary|Null $btn_prev
     */
    public function setBtnPrev(?Primary $btn_prev) : void
    {
        $this->btn_prev = $btn_prev;
    }




}