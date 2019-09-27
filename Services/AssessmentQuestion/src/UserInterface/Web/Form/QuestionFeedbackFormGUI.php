<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\AssessmentQuestion\UserInterface\Web\Form;

use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ILIAS\AssessmentQuestion\UserInterface\Web\Page\Page;
use ILIAS\AssessmentQuestion\UserInterface\Web\Page\PageFactory;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\AuthoringContextContainer;

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
     * @var Page
     */
    protected $page;

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
     * @param Page                      $page
     * @param QuestionDto               $questionDto
     * @param bool                      $preventRteUsage
     */
    public function __construct(
        Page $page,
        QuestionDto $questionDto,
        bool $preventRteUsage
    )
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        parent::__construct();

        $this->page = $page;
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
            $DIC->language()->txt('asq_input_feedback_correct'), self::VAR_FEEDBACK_CORRECT
        );

        $feedbackCorrectInput->setValue($this->questionDto->getFeedbackCorrect()->getContent());

        $this->addItem($feedbackCorrectInput);

        $feedbackWrongInput = $this->buildFeedbackContentInputFormProperty(
            $DIC->language()->txt('asq_input_feedback_wrong'), self::VAR_FEEDBACK_WRONG
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
            $DIC->language()->txt('asq_input_feedback_correct'), self::VAR_FEEDBACK_CORRECT
        );


        $feedbackCorrectInput->setValue($this->getPageObjectNonEditableInputValueHtml($this->page));

        $this->addItem($feedbackCorrectInput);

        $feedbackWrongInput = $this->buildFeedbackPageObjectFormProperty(
            $DIC->language()->txt('asq_input_feedback_wrong'), self::VAR_FEEDBACK_WRONG
        );

        $feedbackWrongInput->setValue($this->getPageObjectNonEditableInputValueHtml($this->page));

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
     * @param $page Page
     *
     * @return string
     */
    protected function getPageObjectNonEditableInputValueHtml(Page $page):string
    {
        return $page->getPageEditingLink();
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
