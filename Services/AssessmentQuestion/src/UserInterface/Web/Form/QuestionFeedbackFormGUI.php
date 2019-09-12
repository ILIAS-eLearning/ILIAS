<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Form;

use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;

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
    const VAR_FEEDBACK_CORRECT = 'feedback_correct';
    const VAR_FEEDBACK_WRONG = 'feedback_wrong';

    /**
     * @var \ilAsqFeedbackPageService
     */
    protected $feedbackPageService;

    /**
     * @var QuestionDto
     */
    protected $questionDto;

    /**
     * @var bool
     */
    protected $preventRteUsage;


    /**
     * QuestionFeedbackFormGUI constructor.
     *
     * @param \ilAsqFeedbackPageService $feedbackPageService
     * @param QuestionDto               $questionDto
     * @param bool                      $preventRteUsage
     */
    public function __construct(
        \ilAsqFeedbackPageService $feedbackPageService,
        QuestionDto $questionDto,
        bool $preventRteUsage
    )
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        parent::__construct();

        $this->feedbackPageService = $feedbackPageService;
        $this->questionDto = $questionDto;
        $this->preventRteUsage = $preventRteUsage;

        $this->setTitle($DIC->language()->txt('asq_feedback_form_title'));

        $this->initForm();
    }

    protected function initForm()
    {
        if( $this->questionDto->getContentEditingMode()->isRteTextarea() )
        {
            $this->initRteTextareaForm();
        }
        elseif( $this->questionDto->getContentEditingMode()->isPageObject() )
        {
            $this->initPageObjectForm();
        }
    }

    protected function initRteTextareaForm()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $feedbackCorrectInput = $this->buildFeedbackContentInputFormProperty(
            $DIC->language()->txt('input_feedback_correct'), self::VAR_FEEDBACK_CORRECT
        );

        $feedbackCorrectInput->setValue($this->questionDto->getFeedbackCorrect()->getContent());

        $this->addItem($feedbackCorrectInput);

        $feedbackWrongInput = $this->buildFeedbackContentInputFormProperty(
            $DIC->language()->txt('input_feedback_wrong'), self::VAR_FEEDBACK_WRONG
        );

        $feedbackWrongInput->setValue($this->questionDto->getFeedbackWrong()->getContent());

        $this->addItem($feedbackWrongInput);
    }

    /**
     * @param string $label
     * @param string $postVar
     * @return \ilTextAreaInputGUI
     */
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
    }

    protected function initPageObjectForm()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $feedbackCorrectInput = $this->buildFeedbackPageObjectFormProperty(
            $DIC->language()->txt('input_feedback_correct'), self::VAR_FEEDBACK_CORRECT
        );

        $feedbackCorrectInput->setValue($this->getPageObjectNonEditableInputValueHtml(
            \ilAsqGenericFeedbackPage::PARENT_TYPE,
            $this->questionDto->getFeedbackCorrect()->getIntId()
        ));

        $this->addItem($feedbackCorrectInput);

        $feedbackWrongInput = $this->buildFeedbackPageObjectFormProperty(
            $DIC->language()->txt('input_feedback_wrong'), self::VAR_FEEDBACK_WRONG
        );

        $feedbackWrongInput->setValue($this->getPageObjectNonEditableInputValueHtml(
            \ilAsqGenericFeedbackPage::PARENT_TYPE,
            $this->questionDto->getFeedbackWrong()->getIntId()
        ));

        $this->addItem($feedbackWrongInput);
    }

    /**
     * @param string $label
     * @param string $postVar
     * @return \ilNonEditableValueGUI
     */
    protected function buildFeedbackPageObjectFormProperty($label, $postVar)
    {
        $property = new \ilNonEditableValueGUI($label, $postVar, true);
        return $property;
    }

    /**
     * @param string  $pageObjectType
     * @param integer $pageObjectId
     *
     * @return string $nonEditableValueHTML
     */
    protected function getPageObjectNonEditableInputValueHtml($pageObjectType, $pageObjectId)
    {
        $this->feedbackPageService->ensureFeedbackPageExists($pageObjectType, $pageObjectId);

        $link = $this->feedbackPageService->getFeedbackPageEditingLink($pageObjectType, $pageObjectId);
        $content = $this->feedbackPageService->getFeedbackPageContent($pageObjectType, $pageObjectId);

        return "$link<br /><br />$content";
    }

    public function getFeedbackCorrect()
    {
        return $this->getInput(self::VAR_FEEDBACK_CORRECT);
    }

    public function getFeedbackWrong()
    {
        return $this->getInput(self::VAR_FEEDBACK_WRONG);
    }
}
