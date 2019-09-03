<?php
declare(strict_types=1);

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


use ILIAS\AssessmentQuestion\Application\PlayApplicationService;
use ILIAS\AssessmentQuestion\CQRS\Aggregate\DomainObjectId;
use ILIAS\AssessmentQuestion\CQRS\Aggregate\Guid;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Answer;
use ILIAS\AssessmentQuestion\DomainModel\QuestionPlayConfiguration;
use ILIAS\AssessmentQuestion\UserInterface\Web\AsqGUIElementFactory;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\QuestionComponent;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\QuestionTypeSelectForm;
use ILIS\AssessmentQuestion\Application\AuthoringApplicationService;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\AvailableEditors;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Presenter\AvailablePresenters;
use ILIAS\AssessmentQuestion\DomainModel\Scoring\AvailableScorings;

use ILIAS\Services\AssessmentQuestion\PublicApi\Common\AssessmentEntityId;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\AuthoringContextContainer;
use ILIAS\Services\AssessmentQuestion\PublicApi\Authoring\AuthoringService;

/**
 * Class ilAsqQuestionAuthoringGUI
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_Calls ilAsqQuestionAuthoringGUI: ilAsqQuestionCreationGUI
 * @ilCtrl_Calls ilAsqQuestionAuthoringGUI: ilAsqQuestionPreviewGUI
 * @ilCtrl_Calls ilAsqQuestionAuthoringGUI: ilAsqQuestionPageEditorGUI
 * @ilCtrl_Calls ilAsqQuestionAuthoringGUI: ilAsqQuestionConfigEditorGUI
 * @ilCtrl_Calls ilAsqQuestionAuthoringGUI: ilAsqQuestionFeedbackEditorGUI
 * @ilCtrl_Calls ilAsqQuestionAuthoringGUI: ilAsqQuestionHintsEditorGUI
 * @ilCtrl_Calls ilAsqQuestionAuthoringGUI: ilAsqQuestionRecapitulationEditorGUI
 * @ilCtrl_Calls ilAsqQuestionAuthoringGUI: ilAsqQuestionStatisticsGUI
 * @ilCtrl_Calls ilAsqQuestionAuthoringGUI: ilCommonActionDispatcherGUI
 */
class ilAsqQuestionAuthoringGUI
{
    const TAB_ID_PREVIEW = 'qst_preview_tab';
    const TAB_ID_PAGEVIEW = 'qst_pageview_tab';
    const TAB_ID_CONFIG = 'qst_config_tab';
    const TAB_ID_FEEDBACK = 'qst_feedback_tab';
    const TAB_ID_HINTS = 'qst_hints_tab';
    const TAB_ID_RECAPITULATION = 'qst_recapitulation_tab';
    const TAB_ID_STATISTIC = 'qst_statistic_tab';

    const VAR_QUESTION_ID = "questionId";

    const CMD_REDRAW_HEADER_ACTION_ASYNC = '';

    /**
     * obsolete constans, commands will be moved
     */
	const CMD_CREATE_QUESTION = "createQuestion";
	const CMD_EDIT_QUESTION = "editQuestion";
	const CMD_PREVIEW_QUESTION = "previewQuestion";
	const CMD_SCORE_QUESTION = "scoreQuestion";
	const CMD_DISPLAY_QUESTION = "displayQuestion";
	const CMD_GET_FORM_SNIPPET = "getFormSnippet";
	const DEBUG_TEST_ID = 23;

    /**
     * @var AuthoringContextContainer
     */
	protected $contextContainer;

	/**
	 * @var AuthoringApplicationService
	 */
	private $authoring_application_service;

	/**
	 * @var AuthoringService
	 */
    protected $authoring_service;

    /**
     * @var AssessmentEntityId
     */
    protected $question_id;


    /**
     * ilAsqQuestionAuthoringGUI constructor.
     *
     * @param AuthoringContextContainer $authoringContextContainer
     */
	function __construct(AuthoringContextContainer $contextContainer)
	{
	    global $DIC; /* @var ILIAS\DI\Container $DIC */

	    $this->contextContainer = $contextContainer;

        $this->authoring_application_service = new AuthoringApplicationService(
            $this->contextContainer->getObjId(), $this->contextContainer->getActorId()
        );

        $this->authoring_service = $DIC->assessment()->questionAuthoring(
            $this->contextContainer->getObjId(), $this->contextContainer->getActorId()
        );

        $this->question_id = $this->authoring_service->currentOrNewQuestionId();
    }

    protected function initAuthoringTabs()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */

        $question = $this->authoring_service->question(
            $this->question_id, $this->contextContainer->getBackLink()
        );

        $DIC->tabs()->clearTargets();

        $DIC->tabs()->setBackTarget(
            $this->contextContainer->getBackLink()->getLabel(),
            $this->contextContainer->getBackLink()->getAction()
        );

        if( $this->contextContainer->hasWriteAccess() )
        {
            $link = $question->getEditPageLink();
            $DIC->tabs()->addTab(self::TAB_ID_PAGEVIEW, $link->getLabel(), $link->getAction());
        }

        $link = $question->getPreviewLink(array());
        $DIC->tabs()->addTab(self::TAB_ID_PREVIEW, $link->getLabel(), $link->getAction());

        if( $this->contextContainer->hasWriteAccess() )
        {
            $link = $question->getEditLink(array());
            $DIC->tabs()->addTab(self::TAB_ID_CONFIG, $link->getLabel(), $link->getAction());
        }

        $link = $question->getEditFeedbacksLink();
        $DIC->tabs()->addTab(self::TAB_ID_FEEDBACK, $link->getLabel(), $link->getAction());

        $link = $question->getEditHintsLink();
        $DIC->tabs()->addTab(self::TAB_ID_HINTS, $link->getLabel(), $link->getAction());

        $link = $question->getRecapitulationLink();
        $DIC->tabs()->addTab(self::TAB_ID_RECAPITULATION, $link->getLabel(), $link->getAction());

        $link = $question->getStatisticLink();
        $DIC->tabs()->addTab(self::TAB_ID_STATISTIC, $link->getLabel(), $link->getAction());
    }

    protected function getHeaderAction() : string
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */

        //$question = $this->authoring_application_service->GetQuestion($this->question_id->getId());

        /**
         * TODO: Get the old integer id of the question.
         * We still need the former integer sequence id of the question,
         * since several other services in ilias does only work with an int id.
         */

        //$integerQuestionId = $question->getLegacyIntegerId(); // or similar
        $integerQuestionId = 0;

        $dispatcher = new ilCommonActionDispatcherGUI(
            ilCommonActionDispatcherGUI::TYPE_REPOSITORY, $DIC->access(),
            $this->contextContainer->getObjType(),
            $this->contextContainer->getRefId(),
            $this->contextContainer->getObjId()
        );

        $dispatcher->setSubObject('quest', $integerQuestionId);

        $ha = $dispatcher->initHeaderAction();
        $ha->enableComments(true, false);

        return $ha->getHeaderAction($DIC->ui()->mainTemplate());
    }

    protected function initHeaderAction()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */

        $DIC->ui()->mainTemplate()->setVariable(
            'HEAD_ACTION', $this->getHeaderAction()
        );

        $notesUrl = $DIC->ctrl()->getLinkTargetByClass(
            array('ilCommonActionDispatcherGUI', 'ilNoteGUI'), '', '', true, false
        );

        ilNoteGUI::initJavascript($notesUrl,IL_NOTE_PUBLIC, $DIC->ui()->mainTemplate());

        $redrawActionsUrl = $DIC->ctrl()->getLinkTarget(
            $this, self::CMD_REDRAW_HEADER_ACTION_ASYNC, '', true
        );

        $DIC->ui()->mainTemplate()->addOnLoadCode("il.Object.setRedrawAHUrl('$redrawActionsUrl');");
    }

    protected function redrawHeaderAction()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        echo $this->getHeaderAction() . $DIC->ui()->mainTemplate()->getOnLoadCodeForAsynch();
        exit;
    }


    /**
     * @throws ilCtrlException
     */
	public function executeCommand()
	{
		global $DIC; /* @var ILIAS\DI\Container $DIC */

        $DIC->ctrl()->setParameter(
            $this, self::VAR_QUESTION_ID, $this->question_id->getId()
        );

		switch( $DIC->ctrl()->getNextClass() )
        {
            case strtolower(ilAsqQuestionCreationGUI::class):

                $gui = new ilAsqQuestionCreationGUI();
                $DIC->ctrl()->forwardCommand($gui);

                break;

            case strtolower(ilAsqQuestionPreviewGUI::class):

                $this->initHeaderAction();
                $this->initAuthoringTabs();
                $DIC->tabs()->activateTab(self::TAB_ID_PREVIEW);

                $gui = new ilAsqQuestionPreviewGUI();
                $DIC->ctrl()->forwardCommand($gui);

                break;

            case strtolower(ilAsqQuestionPageEditorGUI::class):

                $this->initHeaderAction();
                $this->initAuthoringTabs();
                $DIC->tabs()->activateTab(self::TAB_ID_PAGEVIEW);

                $gui = new ilAsqQuestionPageEditorGUI();
                $DIC->ctrl()->forwardCommand($gui);

                break;

            case strtolower(ilAsqQuestionConfigEditorGUI::class):

                $this->initHeaderAction();
                $this->initAuthoringTabs();
                $DIC->tabs()->activateTab(self::TAB_ID_CONFIG);

                $gui = new ilAsqQuestionConfigEditorGUI();
                $DIC->ctrl()->forwardCommand($gui);

                break;

            case strtolower(ilAsqQuestionFeedbackEditorGUI::class):

                $this->initHeaderAction();
                $this->initAuthoringTabs();
                $DIC->tabs()->activateTab(self::TAB_ID_FEEDBACK);

                $gui = new ilAsqQuestionFeedbackEditorGUI();
                $DIC->ctrl()->forwardCommand($gui);

                break;

            case strtolower(ilAsqQuestionHintsEditorGUI::class):

                $this->initHeaderAction();
                $this->initAuthoringTabs();
                $DIC->tabs()->activateTab(self::TAB_ID_HINTS);

                $gui = new ilAsqQuestionHintsEditorGUI();
                $DIC->ctrl()->forwardCommand($gui);

                break;

            case strtolower(ilAsqQuestionRecapitulationEditorGUI::class):

                $this->initHeaderAction();
                $this->initAuthoringTabs();
                $DIC->tabs()->activateTab(self::TAB_ID_RECAPITULATION);

                $gui = new ilAsqQuestionRecapitulationEditorGUI();
                $DIC->ctrl()->forwardCommand($gui);

                break;

            case strtolower(ilAsqQuestionStatisticsGUI::class):

                $this->initHeaderAction();
                $this->initAuthoringTabs();
                $DIC->tabs()->activateTab(self::TAB_ID_STATISTIC);

                $gui = new ilAsqQuestionStatisticsGUI();
                $DIC->ctrl()->forwardCommand($gui);

                break;

            case strtolower(ilCommonActionDispatcherGUI::class):

                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $DIC->ctrl()->forwardCommand($gui);

                break;

            case strtolower(self::class):
            default:

                $cmd = $DIC->ctrl()->getCmd();
                $this->{$cmd}();
        }
	}


    /**
     * @throws Exception
     */
    public function editQuestion()
    {
        global $DIC;
        
        $question_id = $_GET[self::VAR_QUESTION_ID];
        $question = $this->authoring_application_service->GetQuestion($question_id);
        $form = AsqGUIElementFactory::CreateQuestionForm($question);
        
        switch($_SERVER['REQUEST_METHOD'])
        {
            case "POST":
                $question = $form->getQuestion();
                $this->authoring_application_service->SaveQuestion($question);
                $form = AsqGUIElementFactory::CreateQuestionForm($question);
                ilutil::sendSuccess("Question Saved");
                break;
        }
        
        $DIC->ui()->mainTemplate()->addJavaScript('Services/AssessmentQuestion/js/AssessmentQuestionAuthoring.js');
        $DIC->ui()->mainTemplate()->setContent($form->getHTML());
    }
    
    public function previewQuestion()
    {
        global $DIC;

        $question_id = $_GET[self::VAR_QUESTION_ID];
        $question = $this->authoring_application_service->GetQuestion($question_id);

        $player = new PlayApplicationService(
            $this->contextContainer->getObjId(), $this->contextContainer->getActorId()
        );

        $question_component = new QuestionComponent($question);
        switch($_SERVER['REQUEST_METHOD'])
        {
            case "POST":
                $answer = new Answer($this->contextContainer->getActorId(), $question_id, $this->contextContainer->getObjId(), $question_component->readAnswer());
                $question_component->setAnswer($answer);

                $scoring_class = QuestionPlayConfiguration::getScoringClass($question->getPlayConfiguration());
                $scoring = new $scoring_class($question);

                ilUtil::sendInfo("Score: ".$scoring->score($answer));
                break;
        }

        $DIC->ui()->mainTemplate()->setContent($question_component->renderHtml());
    }
    
    public function getFormSnippet()
    {
        $name = $_GET['class'];

        $class = array_search($name, AvailableEditors::getAvailableEditors());
        if($class === false) {
            $class = array_search($name, AvailableScorings::getAvailableScorings());
        }
        if($class === false) {
            $class = array_search($name, AvailablePresenters::getAvailablePresenters());
        }
        if ($class === false) {
            return;
        }

        $form = new ilPropertyFormGUI();

        $fields = $class::generateFields(null);

        foreach ($fields as $field) {
            $form->addItem($field);
        }

        exit($form->getHTML());
    }

    // TODO move to player
    public function displayQuestion()
    {
        global $DIC;
        
        $revision_id = $_GET[self::VAR_QUESTION_ID];

        $player = new PlayApplicationService(
            $this->contextContainer->getObjId(), $this->contextContainer->getActorId()
        );

        $question = $player->GetQuestion($revision_id);
        
        $question_component = new QuestionComponent($question);
        switch($_SERVER['REQUEST_METHOD'])
        {
            case "GET":
                $answer = $player->GetUserAnswer($question->getId(), (int)$this->contextContainer->getActorId(), $this->contextContainer->getObjId());
                if (!is_null($answer)) {
                    $question_component->setAnswer($answer);
                }
                break;
            case "POST":
                $answer = new Answer($this->contextContainer->getActorId(), $question->getId(), $this->contextContainer->getObjId(), $question_component->readAnswer());
                $player->AnswerQuestion($answer);
                $question_component->setAnswer($answer);
                break;
        }
        
        
        $DIC->ui()->mainTemplate()->setContent($question_component->renderHtml());
    }
    
    public function scoreQuestion()
    {
        global $DIC;
        
        $player = new PlayApplicationService(
            $this->contextContainer->getObjId(), $this->contextContainer->getActorId()
        );
        
        $question_id = $_GET[self::VAR_QUESTION_ID];
        
        $DIC->ui()->mainTemplate()->setContent($player->GetPointsByUser($question_id,
        $this->contextContainer->getActorId(), $this->contextContainer->getObjId()));

    }
}