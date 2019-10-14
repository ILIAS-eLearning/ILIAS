<?php

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Component\Feecback\Form;

use ilFormPropertyGUI;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerOptionFeedbackMode;
use ilRadioGroupInputGUI;
use ilRadioOption;

/**
 * Class FeedbackFieldAnswerOption
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class FeedbackFieldAnswerOption
{
    /**
     * var int
     */
    protected $answer_option_feedback_mode = AnswerOptionFeedbackMode::OPT_ANSWER_OPTION_FEEDBACK_MODE_ALL;


    /**
     * FeedbackFieldAnswerOption constructor.
     *
     * @param int    $answer_option_feedback_mode
     * @param int    $container_obj_id
     * @param string $container_obj_type
     * @param string $post_var
     */
    public function __construct(int $answer_option_feedback_mode, $post_var)
    {
        $this->answer_option_feedback_mode = $answer_option_feedback_mode;
        $this->post_var = $post_var;
    }


    public function getField() : ilFormPropertyGUI
    {
        global $DIC;
        $feedback_setting = new ilRadioGroupInputGUI($DIC->language()->txt('asq_label_feedback_setting'),  $this->post_var);
        $feedback_setting->addOption(new ilRadioOption($DIC->language()->txt('asq_option_feedback_all'), AnswerOptionFeedbackMode::OPT_ANSWER_OPTION_FEEDBACK_MODE_ALL));
        $feedback_setting->addOption(new ilRadioOption($DIC->language()->txt('asq_option_feedback_checked'), AnswerOptionFeedbackMode::OPT_ANSWER_OPTION_FEEDBACK_MODE_CHECKED));
        $feedback_setting->addOption(new ilRadioOption($DIC->language()->txt('asq_option_feedback_correct'), AnswerOptionFeedbackMode::OPT_ANSWER_OPTION_FEEDBACK_MODE_CORRECT));
        $feedback_setting->setRequired(true);

        $feedback_setting->setValue($this->answer_option_feedback_mode);

        return $feedback_setting;
    }


    /**
     * @param string $post_var
     *
     * @return int
     */
    public static function getValueFromPost(string $post_var) : int
    {
        return filter_input(INPUT_POST, $post_var, FILTER_VALIDATE_INT);
    }
}