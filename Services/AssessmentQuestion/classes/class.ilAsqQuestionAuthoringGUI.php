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
use ILIAS\Services\AssessmentQuestion\PublicApi\Authoring\AuthoringService;
use ILIAS\UI\Component\Link\Standard as UiStandardLink;

/**
 * Class ilAsqQuestionAuthoringGUI
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 *
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
     * @var UiStandardLink
     */
    protected $container_back_link;

    /**
     * @var int
     */
    protected $container_obj_id;

    /**
     * @var int
     */
    protected $actor_user_id;

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
     * @param UiStandardLink $container_back_link
     * @param int            $container_ref_id
     * @param int            $container_obj_id
     * @param string         $container_obj_type
     * @param int            $actor_user_id
     * @param bool           $actor_has_write_access
     */
	function __construct(
        UiStandardLink $container_back_link,
        int $container_ref_id,
        int $container_obj_id,
        string $container_obj_type,
        int $actor_user_id,
        bool $actor_has_write_access
    )
	{
	    global $DIC; /* @var ILIAS\DI\Container $DIC */

        $this->container_back_link = $container_back_link;
        $this->container_ref_id = $container_ref_id;
        $this->container_obj_id = $container_obj_id;
        $this->container_obj_type = $container_obj_type;
        $this->actor_user_id = $actor_user_id;
        $this->actor_has_write_access = $actor_has_write_access;

        $this->authoring_application_service = new AuthoringApplicationService($container_obj_id, $actor_user_id);
        $this->authoring_service = $DIC->assessment()->questionAuthoring($container_obj_id, $actor_user_id);

        $this->question_id = $this->authoring_service->currentOrNewQuestionId();
    }

    protected function initAuthoringTabs()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */

        $question = $this->authoring_service->question(
            $this->question_id, $this->container_back_link
        );

        $DIC->tabs()->clearTargets();

        $DIC->tabs()->setBackTarget(
            $this->container_back_link->getLabel(),
            $this->container_back_link->getAction()
        );

        if( $this->actor_has_write_access )
        {
            $link = $question->getEditPageLink();
            $DIC->tabs()->addTab(self::TAB_ID_PAGEVIEW, $link->getLabel(), $link->getAction());
        }

        $link = $question->getPreviewLink(array());
        $DIC->tabs()->addTab(self::TAB_ID_PREVIEW, $link->getLabel(), $link->getAction());

        if( $this->actor_has_write_access )
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
            $this->container_obj_type, $this->container_ref_id, $this->container_obj_id
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

	public function executeCommand()
	{
		global $DIC; /* @var ILIAS\DI\Container $DIC */

		switch( $DIC->ctrl()->getNextClass() )
        {
            case strtolower(ilAsqQuestionCreationGUI::class):

                $gui = new ilAsqQuestionCreationGUI();
                $DIC->ctrl()->forwardCommand($gui);

                break;

            case strtolower(ilAsqQuestionPreviewGUI::class):

                $this->initHeaderAction();
                $this->initAuthoringTabs();

                $gui = new ilAsqQuestionPreviewGUI();
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
	public function createQuestion()
	{
	    global $DIC;

	    $form = new QuestionTypeSelectForm();

	    switch($_SERVER['REQUEST_METHOD'])
	    {
	        case "GET":
	            $DIC->ui()->mainTemplate()->setContent($form->getHTML());
	            break;
	        case "POST":
	            $guid = Guid::create();
	            $type = $form->getQuestionType();
	            $this->authoring_application_service->CreateQuestion(new DomainObjectId($guid), $this->container_obj_id, $type);
	            $DIC->ctrl()->setParameter($this, self::VAR_QUESTION_ID, $guid);
	            $DIC->ctrl()->redirect($this, self::CMD_EDIT_QUESTION);
	            break;
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

        $player = new PlayApplicationService($this->container_obj_id,$this->actor_user_id);

        $question_component = new QuestionComponent($question);
        switch($_SERVER['REQUEST_METHOD'])
        {
            case "POST":
                $answer = new Answer($DIC->user()->getId(), $question_id, $this->container_obj_id, $question_component->readAnswer());
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
        $player = new PlayApplicationService($this->container_obj_id,$this->actor_user_id);
        $question = $player->GetQuestion($revision_id);
        
        $question_component = new QuestionComponent($question);
        switch($_SERVER['REQUEST_METHOD'])
        {
            case "GET":
                $answer = $player->GetUserAnswer($question->getId(), (int)$DIC->user()->getId(), $this->container_obj_id);
                if (!is_null($answer)) {
                    $question_component->setAnswer($answer);
                }
                break;
            case "POST":
                $answer = new Answer($DIC->user()->getId(), $question->getId(), $this->container_obj_id, $question_component->readAnswer());
                $player->AnswerQuestion($answer);
                $question_component->setAnswer($answer);
                break;
        }
        
        
        $DIC->ui()->mainTemplate()->setContent($question_component->renderHtml());
    }
    
    public function scoreQuestion()
    {
        global $DIC;
        
        $player = new PlayApplicationService();
        
        $question_id = $_GET[self::VAR_QUESTION_ID];
        
        $DIC->ui()->mainTemplate()->setContent($player->GetPointsByUser($question_id,
        $DIC->user()->getId(), $this->container_obj_id));

    }
}