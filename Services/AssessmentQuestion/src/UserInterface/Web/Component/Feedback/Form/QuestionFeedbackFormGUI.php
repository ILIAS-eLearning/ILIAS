<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Form;

use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerOptions;
use ILIAS\Services\AssessmentQuestion\DomainModel\Feedback;
use ilFormSectionHeaderGUI;
use ilRadioGroupInputGUI;
use ilRadioOption;
use ilTextAreaInputGUI;
use ILIAS\AssessmentQuestion\ilAsqHtmlPurifier;

/**
 * Class QuestionFeedbackFormGUI
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 *
 * @package ILIAS\AssessmentQuestion\UserInterface\Web\Form
 */
class QuestionFeedbackFormGUI extends \ilPropertyFormGUI
{
    const VAR_ANSWER_FEEDBACK_CORRECT = 'answer_feedback_correct';
    const VAR_ANSWER_FEEDBACK_WRONG = 'answer_feedback_wrong';
    const VAR_ANSWER_OPTION_FEEDBACK_MODE = 'answer_option_feedback_mode';
    const VAR_FEEDBACK_FOR_ANSWER = "feedback_for_answer";

    /**
     * @var QuestionDto
     */
    protected $question_dto;
    /**
     * @var Feedback
     */
    protected $feedback;

    /**
     * @param QuestionDto $question_dto
     * @param Feedback $feedback
     * @param AnswerOptions $answer_options
     */
    public function __construct(
        QuestionDto $question_dto,
        ?Feedback $feedback,
        ?AnswerOptions $answer_options
    )
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        parent::__construct();


        $this->question_dto = $question_dto;
        $this->feedback = $feedback;
        if(!is_object($this->feedback)) {
            $this->feedback = new Feedback();
        }

        $this->setTitle($DIC->language()->txt('asq_feedback_form_title'));

        $this->initForm();
    }

    protected function initForm()
    {
        global $DIC;
        /* @var \ILIAS\DI\Container $DIC */

        $feedback_correct = new ilTextAreaInputGUI($DIC->language()->txt('asq_input_feedback_correct'),  self::VAR_ANSWER_FEEDBACK_CORRECT);
        $feedback_correct->setValue($this->feedback->getAnswerCorrectFeedback());
        $this->addItem($feedback_correct);
        
        $feedback_wrong = new ilTextAreaInputGUI($DIC->language()->txt('asq_input_feedback_wrong'), self::VAR_ANSWER_FEEDBACK_WRONG);
        $feedback_wrong->setValue($this->feedback->getAnswerWrongFeedback());
        $this->addItem($feedback_wrong);

        $header = new ilFormSectionHeaderGUI();
        $header->setTitle($DIC->language()->txt('asq_header_feedback_answers'));
        $this->addItem($header);
        
        $feedback_setting = new ilRadioGroupInputGUI($DIC->language()->txt('asq_label_feedback_setting'),  self::VAR_ANSWER_OPTION_FEEDBACK_MODE);
        $feedback_setting->addOption(new ilRadioOption($DIC->language()->txt('asq_option_feedback_all'), Feedback::OPT_ANSWER_OPTION_FEEDBACK_MODE_ALL));
        $feedback_setting->addOption(new ilRadioOption($DIC->language()->txt('asq_option_feedback_checked'), Feedback::OPT_ANSWER_OPTION_FEEDBACK_MODE_CHECKED));
        $feedback_setting->addOption(new ilRadioOption($DIC->language()->txt('asq_option_feedback_correct'), Feedback::OPT_ANSWER_OPTION_FEEDBACK_MODE_CORRECT));
        $feedback_setting->setRequired(true);
        $feedback_setting->setValue($this->feedback->getAnswerOptionFeedbackMode());
         
        $this->addItem($feedback_setting);

        for ($i = 1; $i <= count($this->question_dto->getAnswerOptions()->getOptions()); $i++) {
            $field = new ilTextAreaInputGUI($i, self::VAR_FEEDBACK_FOR_ANSWER . $i);
            $this->addItem($field);
        }
    }


    /**
     * @param AnswerOptions $answer_options
     *
     * @return Feedback
     */
    public static function getFeedbackFromPost() {

        $feedback_correct = ilAsqHtmlPurifier::getInstance()->purify($_POST[self::VAR_ANSWER_FEEDBACK_CORRECT]);
        $feedback_wrong = ilAsqHtmlPurifier::getInstance()->purify($_POST[self::VAR_ANSWER_FEEDBACK_WRONG]);
        $answer_option_feedback_mode = intval($_POST[self::VAR_ANSWER_OPTION_FEEDBACK_MODE]);

        return Feedback::create($feedback_correct, $feedback_wrong, $answer_option_feedback_mode);
    }
}
