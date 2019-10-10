<?php


use ILIAS\Services\AssessmentQuestion\PublicApi\Authoring\AuthoringService;
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
 * @ilCtrl_IsCalledBy asqDebugGUI: ilObjTestGUI
 */
class asqDebugGUI
{

    const CMD_SHOW_EDIT_LIST = "showEditList";
    const CMD_SET_ONLINE = "setOnline";
    const CMD_PREVIEW_SCORING = "previewScoring";
    const CMD_SHOW_PROCESSING_LIST = "showProcessingList";
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
     * @var ProcessingService
     */
    protected $processing_service;


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

        $this->processing_service = $DIC->assessment()->questionProcessing($DIC->ctrl()->getContextObjId(), $DIC->user()->getId(),$question_config);
    }


    /**
     * execute command
     */
    function executeCommand()
    {
        global $DIC;

        switch (strtolower($DIC->ctrl()->getNextClass())) {
            case strtolower(ilAsqQuestionAuthoringGUI::class):
                //Get the specific question authoring service
                $authoring_gui = $this->authoring_service->question($this->authoring_service->currentOrNewQuestionId())->getAuthoringGUI(
                    $this->back_link, $_GET['ref_id'], 'tst', true,
                    [ilObjTestGUI::class], ilObjTestGUI::CMD_REGISTER_CREATED_QUESTION
                );
                $DIC->ctrl()->forwardCommand($authoring_gui);
                break;
            default:
                switch ($DIC->ctrl()->getCmd()) {
                    case self::CMD_PREVIEW_SCORING:
                        $this->previewScoring();
                        break;
                    case self::CMD_SET_ONLINE:
                        $this->setOnline();
                        break;
                    case self::CMD_SHOW_PROCESSING_LIST:
                        $this->showProcessingList();
                        break;
                    default:
                        $this->showEditList();
                        break;
                }
        }
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


    protected function setOnline()
    {
        global $DIC;
        foreach ($this->authoring_service->questionList()->getQuestionsOfContainerAsDtoList() as $question_dto) {
            $this->authoring_service->question($this->entity_id_builder->fromString($question_dto->getId()))->publishNewRevision();
        }
        $DIC->ctrl()->redirect($this);
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
     * Processing
     */
    ///
    ///
    ///
    ////////////////
    public function showProcessingList()
    {
        global $DIC;

        /**
         * Example as DTO List
         */
        $arr_questions = $this->processing_service->questionList()->getQuestionsOfContainerAsDtoList();

        if (count($arr_questions) > 0) {
            $html = "<ul>";
            foreach ($arr_questions as $question) {
                $row = array();
                $row[] = $question->getId();
                $row[] = $question->getRevisionId();
                $row[] = $question->getData()->getTitle();

                $row[] = $DIC->ui()->renderer()->render(
                    $this->authoring_service->question(
                        $this->entity_id_builder->fromString(
                            $question->getRevisionId())
                        )->getDisplayLink([ilRepositoryGUI::class, ilObjTestGUI::class, asqDebugGUI::class])
                    );
                
                $html .= '<li>' . implode($row, " | ") . '</li>';
            }
            $html .= "<ul>";
        }

        $DIC->ui()->mainTemplate()->setContent($html);
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

        $DIC->tabs()->addSubTab(self::CMD_SHOW_PROCESSING_LIST, self::CMD_SHOW_PROCESSING_LIST, $DIC->ctrl()->getLinkTarget($this, self::CMD_SHOW_PROCESSING_LIST));

        $DIC->tabs()->activateSubTab($DIC->ctrl()->getCmd(self::CMD_SHOW_EDIT_LIST));
    }
}