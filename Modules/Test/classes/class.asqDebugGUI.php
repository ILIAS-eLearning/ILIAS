<?php

use ILIAS\Services\AssessmentQuestion\PublicApi\Authoring\AuthoringService;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\AssessmentEntityId;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\entityIdBuilder;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\QuestionConfig;
use ILIAS\Services\AssessmentQuestion\PublicApi\Processing\ProcessingService;
use ILIAS\UI\Component\Link\Link;

/**
 * Class asqDebugGUI
 *
 * @author            studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author            Adrian Lüthi <al@studer-raimann.ch>
 * @author            Björn Heyser <bh@bjoernheyser.de>
 * @author            Martin Studer <ms@studer-raimann.ch>
 * @author            Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_Calls      asqDebugGUI: ilAsqQuestionAuthoringGUI
 * @ilCtrl_Calls      asqDebugGUI: ilAsqQuestionProcessingGUI
 * @ilCtrl_IsCalledBy asqDebugGUI: ilObjTestGUI
 */
class asqDebugGUI
{

    const VAR_QUESTION_REVISION_KEY = "question_revision_key";
    const CMD_START_TEST = "startTest";
    const CMD_CHOOSE_QUESTION = "chooseQuestion";
    const CMD_SHOW_EDIT_LIST = "showEditList";
    const CMD_SET_ONLINE = "setOnline";
    const CMD_PREVIEW_SCORING = "previewScoring";
    const CMD_SHOW_TEST_START_PAGE = "showTestStart";
    const CMD_ANSWER_QUESTION = "answerQuestion";
    /**
     * @var ProcessingService
     */
    protected $processing_service;
    /**
     * @var AuthoringService
     */
    protected $authoring_service;
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

        $this->renderSubTabs();

        $this->authoring_service = $DIC->assessment()->questionAuthoring($DIC->ctrl()->getContextObjId(), $DIC->user()->getId());
        $this->entity_id_builder = $DIC->assessment()->entityIdBuilder();
        $this->back_link = $DIC->ui()->factory()->link()->standard('Back', $DIC->ctrl()->getLinkTarget($this));
        $question_config = new QuestionConfig();
        $question_config->setFeedbackForAnswerOption(true);
        $question_config->setFeedbackOnDemand(true);
        $question_config->setFeedbackOnSubmit(true);
        $question_config->setFeedbackShowCorrectSolution(true);
        $question_config->setFeedbackShowScore(true);
        $question_config->setHintsActivated(true);

        $action = $DIC->ctrl()->getLinkTarget($this);
        $btn_next = $DIC->ui()->factory()->button()->primary($DIC->language()->txt('btn_next'), '');
        $question_config->setBtnNext($btn_next);

        $this->question_config = $question_config;

        $this->processing_service = $DIC->assessment()->questionProcessing($DIC->ctrl()->getContextObjId(), $DIC->user()->getId(), $question_config);

        //The Test will not use this private application serivcie! we us it to choose unanswered answers
        $this->processing_application_service = new \ILIAS\AssessmentQuestion\Application\ProcessingApplicationService($DIC->ctrl()->getContextObjId(), $DIC->user()->getId(), $question_config,
            $DIC->language()->getDefaultLanguage());
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
                    case strtolower(self::CMD_CHOOSE_QUESTION):
                    $revision_key = $this->chooseNewQuestion();
                    if(!empty($revision_key)) {
                        $DIC->ctrl()->setParameter($this, self::VAR_QUESTION_REVISION_KEY, $revision_key);
                        $this->redirectToQuestion($revision_key);
                    }
                        $this->showTestIsFinished();
                    break;
                    default:
                        $revision_key = strval(filter_input(INPUT_GET, self::VAR_QUESTION_REVISION_KEY));
                        $DIC->ctrl()->setParameter($this, self::VAR_QUESTION_REVISION_KEY, $revision_key);
                        $processing_question_gui = $this->processing_service->question($revision_key)->getProcessingQuestionGUI(self::CMD_CHOOSE_QUESTION);
                        $DIC->ctrl()->forwardCommand($processing_question_gui);
                        break;
                }
                break;

            case strtolower(ilAsqQuestionAuthoringGUI::class):
                //Get the specific question authoring service
                $authoring_gui = $this->authoring_service->question($this->authoring_service->currentOrNewQuestionId())->getAuthoringGUI(
                    $this->back_link, $_GET['ref_id'], 'tst', $this->question_config, true,
                    [ilObjTestGUI::class], ilObjTestGUI::CMD_REGISTER_CREATED_QUESTION
                );
                $DIC->ctrl()->forwardCommand($authoring_gui);
                break;
            default:
                switch ($DIC->ctrl()->getCmd()) {
                    case self::CMD_START_TEST:
                        $this->startTest();
                        break;
                    case self::CMD_PREVIEW_SCORING:
                        $this->previewScoring();
                        break;
                    case self::CMD_SET_ONLINE:
                        $this->setOnline();
                        break;
                    case self::CMD_ANSWER_QUESTION:
                    case ilAsqQuestionProcessingGUI::CMD_SHWOW_FEEDBACK:
                        $this->showProcessingQuestion();
                        break;
                        break;
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

        $first_question_revision_key = "";
        foreach ($this->authoring_service->questionList()->getQuestionsOfContainerAsDtoList() as $question_dto) {
            $this->authoring_service->question($this->entity_id_builder->fromString($question_dto->getId()))->publishNewRevision();
            if (empty($first_question_revision_key)) {
                $first_question_revision_key = $this->authoring_service->question($this->entity_id_builder->fromString($question_dto->getId()))->getQuestionDto()->getRevisionId();
            }
        }
        $this->redirectToQuestion($first_question_revision_key);
    }


    private function chooseNewQuestion():string
    {
        global $DIC;
        $revision_key = "";
        foreach ($this->processing_service->questionList()->getQuestionsOfContainerAsDtoList() as $question_dto) {
            //The Test will choose the answer himself - we choose the next unanswered answer by the private application - service for this demo!
            if (is_null($this->processing_application_service->GetUserAnswer($question_dto->getId(), $question_dto->getRevisionId(), $DIC->user()->getId(), $DIC->ctrl()->getContextObjId()))) {
                $revision_key = $question_dto->getRevisionId();
                return $revision_key;

            }
        }
        return $revision_key;
    }


    public function showTestIsFinished()
    {
        global $DIC;
        $DIC->ui()->mainTemplate()->setContent('Test is finished');
    }


    private function redirectToQuestion(string $revision_key)
    {
        global $DIC;
        $DIC->ctrl()->setParameter($this, self::VAR_QUESTION_REVISION_KEY, $revision_key);
        $DIC->ctrl()->redirect($this->processing_service->question($revision_key)->getProcessingQuestionGUI(self::CMD_CHOOSE_QUESTION), ilAsqQuestionProcessingGUI::CMD_SHOW_QUESTION);
    }


    ////////////////
    ///
    ///
    ///
    /**
     * Authoring
     */
    ///
    ///
    ///
    ////////////////
    protected function showEditList()
    {
        global $DIC;

        $this->renderEditToolbar();

        $html = "";

        /**
         * Example Assoc Array for using in Tables
         */
        $questions = $this->authoring_service->questionList()->getQuestionsOfContainerAsAssocArray();

        if (count($questions) > 0) {
            $html = "<ul>";
            foreach ($questions as $question) {
                $row = array();
                $row[] = $question['id'];
                $row[] = $question['revision_id'];
                $row[] = $question['data_title'];
                $row[] = $DIC->ui()->renderer()->render(
                    $this->authoring_service->question(
                        $this->entity_id_builder->fromString(
                            $question['id'])
                    )->getEditLink([ilRepositoryGUI::class, ilObjTestGUI::class, asqDebugGUI::class])
                );
                $row[] = $DIC->ui()->renderer()->render(
                    $this->authoring_service->question(
                        $this->entity_id_builder->fromString(
                            $question['id'])
                    )->getPreviewLink([ilRepositoryGUI::class, ilObjTestGUI::class, asqDebugGUI::class])
                );
                $html .= '<li>' . implode($row, " | ") . '</li>';
            }
            $html .= "<ul>";
        }

        $DIC->ui()->mainTemplate()->setContent($html);
    }


    protected function renderEditToolbar()
    {
        global $DIC;

        //Create Button
        $creationLinkComponent = $this->authoring_service->question($this->authoring_service->currentOrNewQuestionId())->getCreationLink([
            ilRepositoryGUI::class,
            ilObjTestGUI::class,
            asqDebugGUI::class,
        ]);

        require_once 'Services/UIComponent/Button/classes/class.ilLinkButton.php';
        $btn = ilJsLinkButton::getInstance();
        $btn->setCaption($creationLinkComponent->getLabel(), false);
        $btn->setOnClick('alert(\'\nBUTTON DEPRECATED :-)\n\nPlease use:\n\nQuestions > List View > Create Question\');');
        $btn->setPrimary(true);
        $DIC->toolbar()->addButtonInstance($btn);

        //Set Online Button
        $btn = ilLinkButton::getInstance();
        $btn->setCaption("Set Online (Publish - creates new revisions of all questions)", false);
        $btn->setUrl($DIC->ctrl()->getLinkTarget($this, 'setOnline'));
        $DIC->toolbar()->addButtonInstance($btn);
    }




    ////////////////
    ///
    ///
    ///
    /**
     * Common
     */
    ///
    ///
    ///
    ////////////////
    public function renderSubTabs()
    {
        global $DIC;
        $DIC->tabs()->addSubTab(self::CMD_SHOW_EDIT_LIST, self::CMD_SHOW_EDIT_LIST, $DIC->ctrl()->getLinkTarget($this, self::CMD_SHOW_EDIT_LIST));

        // $DIC->tabs()->addSubTab(self::CMD_SHOW_PROCESSING_LIST, self::CMD_SHOW_PROCESSING_LIST, $DIC->ctrl()->getLinkTarget($this, self::CMD_SHOW_PROCESSING_LIST));

        $DIC->tabs()->activateSubTab($DIC->ctrl()->getCmd(self::CMD_SHOW_EDIT_LIST));
    }
}