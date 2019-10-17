<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Form;

use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerOption;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerOptionFeedback;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerOptionFeedbackMode;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerOptions;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\ImageAndTextDisplayDefinition;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Feecback\Form\FeedbackFieldAnswerCorrectRte;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Feecback\Form\FeedbackFieldAnswerOption;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Feecback\Form\FeedbackFieldAnswerOptionsContentRte;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Feecback\Form\FeedbackFieldAnswerWrongRte;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Feecback\Form\FeedbackFieldContentRte;
use ILIAS\AssessmentQuestion\UserInterface\Web\Page\Page;
use ILIAS\Services\AssessmentQuestion\DomainModel\Feedback\AnswerCorrectFeedback;
use ILIAS\Services\AssessmentQuestion\DomainModel\Feedback\AnswerWrongFeedback;
use ILIAS\Services\AssessmentQuestion\DomainModel\Feedback\Feedback;
use Exception;
use ilFormSectionHeaderGUI;

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
     * @var Page
     */
    protected $page;

    /**
     * @var QuestionDto
     */
    protected $question_dto;
    /**
     * @var Feedback
     */
    protected $feedback;
    /**
     * @var AnswerOptionFeedback[]
     */
    protected $answer_option_feedbacks;

    /**
     * QuestionFeedbackFormGUI constructor.
     *
     * @param Page                      $page
     * @param QuestionDto               $questionDto
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


        $this->answer_options = $answer_options;

        $this->setTitle($DIC->language()->txt('asq_feedback_form_title'));

        $this->initForm();
    }

    protected function initForm()
    {
        global $DIC;
        /* @var \ILIAS\DI\Container $DIC */

        $feedback = "";
        if(is_object($this->feedback->getAnswerCorrectFeedback())) {
            $feedback = $this->feedback->getAnswerCorrectFeedback()->getAnswerFeedback();
        }
        $field = new FeedbackFieldContentRte($feedback,$this->question_dto->getContainerObjId(), $this->question_dto->getLegacyData()->getContainerObjType(),  $DIC->language()->txt('asq_input_feedback_correct'), self::VAR_ANSWER_FEEDBACK_CORRECT);
        $this->addItem($field->getField());

        $feedback = "";
        if(is_object($this->feedback->getAnswerWrongFeedback())) {
            $feedback = $this->feedback->getAnswerWrongFeedback()->getAnswerFeedback();
        }
        $field = new FeedbackFieldContentRte($feedback,$this->question_dto->getContainerObjId(), $this->question_dto->getLegacyData()->getContainerObjType(),  $DIC->language()->txt('asq_input_feedback_wrong'), self::VAR_ANSWER_FEEDBACK_WRONG);
        $this->addItem($field->getField());


        $header = new ilFormSectionHeaderGUI();
        $header->setTitle($DIC->language()->txt('asq_header_feedback_answers'));
        $this->addItem($header);

        $feedback_mode = 0;
        if(is_object($this->feedback->getAnswerOptionFeedbackMode())) {
           $feedback_mode = $this->feedback->getAnswerOptionFeedbackMode()->getMode();
         }
        $field = new FeedbackFieldAnswerOption($feedback_mode,self::VAR_ANSWER_OPTION_FEEDBACK_MODE);
        $this->addItem($field->getField());


        $field_group = new FeedbackFieldAnswerOptionsContentRte($this->question_dto->getAnswerOptions(),$this->question_dto->getContainerObjId(), $this->question_dto->getLegacyData()->getContainerObjType(),   self::VAR_FEEDBACK_FOR_ANSWER);
        foreach ($field_group->getFields() as $field) {
            $this->addItem($field->getField());
        }
    }


    /**
     * @param AnswerOptions $answer_options
     *
     * @return Feedback
     */
    public static function getFeedbackFromPost() {

        $feedback_correct = FeedbackFieldContentRte::getValueFromPost(self::VAR_ANSWER_FEEDBACK_CORRECT);
        $feedback_wrong = FeedbackFieldContentRte::getValueFromPost(self::VAR_ANSWER_FEEDBACK_WRONG);
        $answer_option_feedback_mode = FeedbackFieldAnswerOption::getValueFromPost(self::VAR_ANSWER_OPTION_FEEDBACK_MODE);

        return new Feedback(new AnswerCorrectFeedback($feedback_correct), new AnswerWrongFeedback($feedback_wrong), new AnswerOptionFeedbackMode($answer_option_feedback_mode));
    }

    public static function getAnswerOptionFeedbacksFromPost(AnswerOptions $answer_options) {
       return FeedbackFieldAnswerOptionsContentRte::getValueFromPostAnswerOptions($answer_options, self::VAR_FEEDBACK_FOR_ANSWER);
    }

}
