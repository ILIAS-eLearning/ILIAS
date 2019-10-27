<?php

use ILIAS\Services\AssessmentQuestion\PublicApi\Authoring\AuthoringService;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\AssessmentEntityId;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\entityIdBuilder;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\QuestionConfig;
use ILIAS\Services\AssessmentQuestion\PublicApi\Processing\ProcessingQuestion;
use ILIAS\Services\AssessmentQuestion\PublicApi\Processing\ProcessingService;
use ILIAS\UI\Component\Link\Link;

/**
 * Class asqPlayerGUI
 *
 * @author            studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author            Adrian Lüthi <al@studer-raimann.ch>
 * @author            Björn Heyser <bh@bjoernheyser.de>
 * @author            Martin Studer <ms@studer-raimann.ch>
 * @author            Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_Calls      asqPlayerGUI: ilAsqQuestionAuthoringGUI
 * @ilCtrl_Calls      asqPlayerGUI: ilAsqQuestionProcessingGUI
 * @ilCtrl_IsCalledBy asqPlayerGUI: asqDebugGUI
 */
class asqPlayerGUI
{
    const CMD_SHOW_TEST_START = "showTestStart";
    const CMD_START_TEST = "startTest";
    const CMD_SHOW_NEXT_QUESTION = "showNextQuestion";
    const CMD_SHOW_PREVIOUS_QUESTION = "showPreviousQuestion";

    /**
     * @var ProcessingService
     */
    protected $processing_service;
    /**
     * @var entityIdBuilder
     */
    protected $entity_id_builder;
    /**
     * @var Link
     */
    protected $back_link;
    /**
     * @var QuestionConfig
     */
    protected $question_config;


    public function __construct()
    {
        global $DIC;

        $DIC->tabs()->activateSubTab($DIC->ctrl()->getCmd(self::CMD_SHOW_TEST_START));


        $this->entity_id_builder = $DIC->assessment()->entityIdBuilder();
        $this->back_link = $DIC->ui()->factory()->link()->standard('Back', $DIC->ctrl()->getLinkTarget($this));

        $this->processing_service = $DIC->assessment()->questionProcessing($DIC->ctrl()->getContextObjId(), $DIC->user()->getId());

        $this->question_config = new QuestionConfig();
        $this->question_config->setQuestionPageActionMenu($this->getActionsList());
        $this->question_config->setFeedbackForAnswerOption(true);
        $this->question_config->setFeedbackOnDemand(true);
        $this->question_config->setFeedbackOnSubmit(true);
        $this->question_config->setFeedbackShowCorrectSolution(true);
        $this->question_config->setFeedbackShowScore(true);
        $this->question_config->setHintsActivated(true);
        $this->question_config->setShowTotalPointsOfQuestion(true);

        //NEXT
        $action = $DIC->ctrl()->getLinkTarget($this, self::CMD_SHOW_NEXT_QUESTION);
        $this->question_config ->setShowNextQuestionAction($action);
        //Previous
        if ($this->getPreviousQuestionKey()) {
            $action = $DIC->ctrl()->getLinkTarget($this, self::CMD_SHOW_PREVIOUS_QUESTION);
            $this->question_config ->setShowPreviousQuestionAction($action);
        }
    }


    /**
     * execute command
     */
    function executeCommand()
    {
        global $DIC;

        switch (strtolower($DIC->ctrl()->getNextClass())) {
            case strtolower(ilAsqQuestionProcessingGUI::class):
                switch (strtolower($DIC->ctrl()->getCmd())) {
                    default:
                        $processing_question_gui = $this->chooseNewQuestion();
                        $DIC->ctrl()->forwardCommand($processing_question_gui);
                        break;
                }
                break;

            default:
                switch ($DIC->ctrl()->getCmd()) {
                    case self::CMD_START_TEST:
                        $this->startTest();
                        break;
                    case self::CMD_SHOW_NEXT_QUESTION:
                        $this->showNextQuestion();
                        break;
                    case self::CMD_SHOW_PREVIOUS_QUESTION:
                        $this->showPreviousQuestion();
                        break;
                    case self::CMD_SHOW_TEST_START:
                    default:
                        $this->showTestStart();
                        break;
                }
        }
    }

    ////////////////
    ///
    ///
    ///
    /**
     * Processing
     */
    ///
    ///
    ///
    ////////////////
    public function showTestStart()
    {
        global $DIC;

        //Set Online Button
        $btn = ilLinkButton::getInstance();
        $btn->setCaption("Set Online (Publish & Start)", false);
        $btn->setUrl($DIC->ctrl()->getLinkTarget($this, self::CMD_START_TEST));
        $DIC->toolbar()->addButtonInstance($btn);
    }



    public function startTest()
    {
        global $DIC;
        $authoring_service = $DIC->assessment()->questionAuthoring($DIC->ctrl()->getContextObjId(), $DIC->user()->getId());
        //Publish Questions
        foreach ($authoring_service->questionList()->getQuestionsOfContainerAsDtoList() as $question_dto) {
            $authoring_service->question($this->entity_id_builder->fromString($question_dto->getId()))->publishNewRevision();
        }

        $this->showNextQuestion();
    }

    public function showNextQuestion() {
        global $DIC;
        $asq_processing_authoring_gui = $this->chooseNewQuestion();

        if (is_object($asq_processing_authoring_gui)) {
            $DIC->ctrl()->forwardCommand($asq_processing_authoring_gui);
        } else {
            $this->showTestIsFinished();
        }

    }

    public function showPreviousQuestion()
    {
        global $DIC;
        $asq_processing_authoring_gui = $this->getPreviousQuestionKey();
        if (is_object($asq_processing_authoring_gui)) {
            $DIC->ctrl()->forwardCommand($asq_processing_authoring_gui);
        } else {
            $this->showTestIsFinished();
        }
    }


    public function showTestIsFinished()
    {
        global $DIC;
        $DIC->ui()->mainTemplate()->setContent('Test is finished');
    }



    private function chooseNewQuestion() : ?ilAsqQuestionProcessingGUI
    {
        global $DIC;

        //The Test will choose the answer himself - we choose the next unanswered answer by the private application - service for this demo!
        $processing_application_service = new \ILIAS\AssessmentQuestion\Application\ProcessingApplicationService($DIC->ctrl()->getContextObjId(), $DIC->user()->getId(), $DIC->language()->getDefaultLanguage());

        $total_questions = count($this->processing_service->questionList()->getQuestionsOfContainerAsDtoList());
        $current_question = 1;
        foreach ($this->processing_service->questionList()->getQuestionsOfContainerAsDtoList() as $question_dto) {
            if (is_null($processing_application_service->GetUserAnswer($question_dto->getId(), $question_dto->getRevisionId(), $DIC->user()->getId(), $DIC->ctrl()->getContextObjId()))) {
                $processing_question =  $this->processing_service->question($question_dto->getRevisionId());

                $question_config =  $this->question_config;
                $question_config->setSubline( sprintf($DIC->language()->txt("tst_position"), $current_question,$total_questions));

                return $processing_question->getProcessingQuestionGUI($question_config);
            }
            $current_question += 1;
        }
        return NULL;
    }

    public function getPreviousQuestionKey() : ?ilAsqQuestionProcessingGUI
    {
        global $DIC;

        //Only for show the example - the test knows the previous answered question; the test will not call the asq processing service for this!
        global $DIC;
        $previous_revision_key = "";
        $revision_key = "";

        $processing_service = $DIC->assessment()->questionProcessing($DIC->ctrl()->getContextObjId(), $DIC->user()->getId());
        $processing_application_service = new \ILIAS\AssessmentQuestion\Application\ProcessingApplicationService($DIC->ctrl()->getContextObjId(), $DIC->user()->getId(), $DIC->language()->getDefaultLanguage());

        $total_questions = count($this->processing_service->questionList()->getQuestionsOfContainerAsDtoList());
        $current_question = 1;
        foreach ($processing_service->questionList()->getQuestionsOfContainerAsDtoList() as $question_dto) {
            //The Test will choose the answer himself - we choose the next unanswered answer by the private application - service for this demo!
            if (is_null($processing_application_service->GetUserAnswer($question_dto->getId(), $question_dto->getRevisionId(), $DIC->user()->getId(), $DIC->ctrl()->getContextObjId()))) {
                $processing_question =  $this->processing_service->question($previous_revision_key);

                $question_config =  $this->question_config;
                $question_config->setSubline( sprintf($DIC->language()->txt("tst_position"), $current_question-1,$total_questions));

                return $processing_question->getProcessingQuestionGUI($question_config);
            }
            $previous_revision_key = $question_dto->getRevisionId();
            $current_question += 1;
        }

        return null;
    }


    /**
     * @return null|ilAdvancedSelectionListGUI
     * @throws ilTemplateException
     */
    private function getActionsList() : ?ilAdvancedSelectionListGUI
    {
        global $DIC;

        $actions = new ilGroupedListGUI();
        $actions->setAsDropDown(true, true);

        $actions->addEntry($DIC->language()->txt('tst_revert_changes'), $DIC->ctrl()->getLinkTarget($this, ''),
            '', '', '', 'asq_revert_changes_action');
        $actions->addEntry($DIC->language()->txt('discard_answer'),'#',
            '','','ilTestQuestionAction ilTestDiscardSolutionAction','tst_discard_solution_action');
        $actions->addSeparator();
        $actions->addEntry($DIC->language()->txt('char_selector_btn_label'),'#',
            '','','ilAsqAction ilCharSelectorMenuToggle','ilCharSelectorMenuToggleLink');


        $list = new ilAdvancedSelectionListGUI();
        $list->setSelectionHeaderClass('btn-primary');
        $list->setId('QuestionActions');
        $list->setListTitle($DIC->language()->txt("actions"));
        $list->setStyle(ilAdvancedSelectionListGUI::STYLE_LINK);
        $list->setGroupedList($actions);

        return $list;
        //return null;
    }
}