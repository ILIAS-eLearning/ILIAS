<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Form;

use Exception;
use ilAsqAnswerOptionFeedbackPageGUI;
use ilAsqQuestionAuthoringGUI;
use ilAsqQuestionFeedbackEditorGUI;
use ilFormSectionHeaderGUI;
use ILIAS\AssessmentQuestion\DomainModel\Answer\AnswerFeedbackDefinition;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerOptionFeedbackModeDefinition;
use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ILIAS\AssessmentQuestion\UserInterface\Web\Page\Page;
use ILIAS\Services\AssessmentQuestion\DomainModel\Feedback\Feedback;
use ILIAS\UI\Implementation\Component\Link\Standard;

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
    /**
     * @var Page
     */
    protected $page;

    /**
     * @var QuestionDto
     */
    protected $question_dto;

    /**
     * QuestionFeedbackFormGUI constructor.
     *
     * @param Page                      $page
     * @param QuestionDto               $questionDto
     */
    public function __construct(
        QuestionDto $question_dto
    )
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        parent::__construct();


        $this->question_dto = $question_dto;

        $this->setTitle($DIC->language()->txt('asq_feedback_form_title'));

        $this->initForm();
    }


   /*
    protected function initRteTextareaForm()
    {


        $feedbackCorrectInput = $this->buildFeedbackContentInputFormProperty(
            $DIC->language()->txt('asq_input_feedback_correct'), self::VAR_FEEDBACK_CORRECT
        );

        $feedbackCorrectInput->setValue($this->questionDto->getFeedbackCorrect()->getContent());

        $this->addItem($feedbackCorrectInput);

        $feedbackWrongInput = $this->buildFeedbackContentInputFormProperty(
            $DIC->language()->txt('asq_input_feedback_wrong'), self::VAR_FEEDBACK_WRONG
        );

        $feedbackWrongInput->setValue($this->questionDto->getFeedbackWrong()->getContent());

        $this->addItem($feedbackWrongInput);
    }*/

    /**
     * @param string $label
     * @param string $postVar
     * @return \ilTextAreaInputGUI
     */
    /*
    protected function buildFeedbackContentInputFormProperty($label, $postVar)
    {
        $property = new \ilTextAreaInputGUI($label, $postVar);
        $property->setRequired(false);
        $property->setRows(10);

        if( !$this->preventRteUsage ) // TinyMCE
        {
            $property->setUseRte(true);
            $property->addPlugin("latex");
            $property->addButton("latex");
            $property->addButton("pastelatex");
            $property->setRteTags(\ilObjAdvancedEditing::_getUsedHTMLTags("assessment"));
        }
        else
        {
            $property->setRteTags(\ilAssSelfAssessmentQuestionFormatter::getSelfAssessmentTags());
            $property->setUseTagsForRteOnly(false);
        }

        $property->setRTESupport($this->questionDto->getQuestionIntId(), 'asq', 'assessment');

        return $property;
    }*/

    protected function initForm()
    {
        global $DIC;
        /* @var \ILIAS\DI\Container $DIC */

        //$page_object_common_feedback_configuration = CommonPageObjectFeedbackConfiguration::create($this->page);
        /*foreach(CommonPageObjectFeedback::generateFields($page_object_common_feedback_configuration) as $field) {
            $this->addItem($field);
        }*/

        foreach (AnswerFeedbackDefinition::getFields($this->question_dto->getId()) as $field) {
            $this->addItem($field);
        }

        $header = new ilFormSectionHeaderGUI();
        $header->setTitle($DIC->language()->txt('asq_header_feedback_answers'));
        $this->addItem($header);

        foreach (AnswerOptionFeedbackModeDefinition::getFields() as $field) {
            $this->addItem($field);
        }

        foreach ($this->question_dto->getAnswerOptions()->getOptions() as $answer_option) {

            $answer_specific_feedback = new \ilNonEditableValueGUI('test', $answer_option->getOptionId(), true);

            $DIC->ctrl()->setParameterByClass($DIC->ctrl()->getCmdClass(), 'page_type', ilAsqAnswerOptionFeedbackPageGUI::PAGE_TYPE);
            $DIC->ctrl()->setParameterByClass($DIC->ctrl()->getCmdClass(), ilAsqQuestionAuthoringGUI::VAR_QUESTION_ID, $this->question_dto->getId());
            $DIC->ctrl()->setParameterByClass($DIC->ctrl()->getCmdClass(), ilAsqAnswerOptionFeedbackPageGUI::VAR_ANSWER_OPTION_INT_ID, $answer_option->getOptionId());
            $label = $DIC->language()->txt('asq_link_edit_feedback_page');

            //TODO
            $action = $DIC->ctrl()->getLinkTargetByClass([ilAsqQuestionFeedbackEditorGUI::class, ilAsqAnswerOptionFeedbackPageGUI::class], ilAsqAnswerOptionFeedbackPageGUI::CMD_EDIT);

            $link = new Standard($label, $action);

            $answer_specific_feedback->setValue($DIC->ui()->renderer()->render($link));

            $this->addItem($answer_specific_feedback);
            //            $answer_option->getFeedbackDefinition()->

        }
    }

        /*$page_object_specific_feedback_configuration = AnswerSpecificPageObjectFeedbackConfiguration::create($this->page, $this->question_dto->getAnswerOptions());
        foreach(AnswerSpecificPageObjectFeedback::generateFields($page_object_specific_feedback_configuration) as $field) {
            $this->addItem($field);
        }
    }*/

     /**
     * @return QuestionDto
     * @throws Exception
     */
     public function getQuestion() : QuestionDto {
        $question = $this->question_dto;


        $question->setFeedback(new Feedback(AnswerOptionFeedbackModeDefinition::getValueFromPost()));

        return $question;

    }

}
