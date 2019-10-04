<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Form;

use ilFormSectionHeaderGUI;
use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\ImageAndTextDisplayDefinition;
use ILIAS\AssessmentQuestion\UserInterface\Web\Page\Page;
use ILIAS\AssessmentQuestion\UserInterface\Web\Page\PageFactory;
use ILIAS\Services\AssessmentQuestion\DomainModel\Feedback\AnswerSpecificPageObjectFeedback;
use ILIAS\Services\AssessmentQuestion\DomainModel\Feedback\AnswerSpecificPageObjectFeedbackConfiguration;
use ILIAS\Services\AssessmentQuestion\DomainModel\Feedback\CommonPageObjectFeedback;
use ILIAS\Services\AssessmentQuestion\DomainModel\Feedback\CommonPageObjectFeedbackConfiguration;
use ILIAS\Services\AssessmentQuestion\DomainModel\Feedback\Feedback;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\AuthoringContextContainer;
use ilRadioGroupInputGUI;
use ilRadioOption;

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
        Page $page,
        QuestionDto $question_dto
    )
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        parent::__construct();

        $this->page = $page;
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
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $page_object_common_feedback_configuration = CommonPageObjectFeedbackConfiguration::create($this->page);
        foreach(CommonPageObjectFeedback::generateFields($page_object_common_feedback_configuration) as $field) {
            $this->addItem($field);
        }

        $header = new ilFormSectionHeaderGUI();
        $header->setTitle($DIC->language()->txt('asq_header_feedback_answers'));
        $this->addItem($header);
        $page_object_specific_feedback_configuration = AnswerSpecificPageObjectFeedbackConfiguration::create($this->page, $this->question_dto->getAnswerOptions());
        foreach(AnswerSpecificPageObjectFeedback::generateFields($page_object_specific_feedback_configuration) as $field) {
            $this->addItem($field);
        }
    }

}
