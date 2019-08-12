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

/**
 * Class ilAssessmentQuestionExporter
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ilAsqQuestionAuthoringGUI
{
    const VAR_QUESTION_ID = "questionId";
    
	const CMD_CREATE_QUESTION = "createQuestion";
	const CMD_EDIT_QUESTION = "editQuestion";
	const CMD_PREVIEW_QUESTION = "previewQuestion";
	const CMD_SCORE_QUESTION = "scoreQuestion";
	const CMD_DISPLAY_QUESTION = "displayQuestion";
	const DEBUG_TEST_ID = 23;
	
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
     * ilAsqQuestionAuthoringGUI constructor.
     *
     * @param int $container_obj_id
     * @param int $actor_user_id
     */
	function __construct(int $container_obj_id, int $actor_user_id)
	{
	    global $DIC;
        $this->container_obj_id = $container_obj_id;
        $this->actor_user_id = $actor_user_id;

	    $this->authoring_application_service = new AuthoringApplicationService($container_obj_id, $actor_user_id);

	}

	public function executeCommand()
	{
		global $DIC;

		$cmd = $DIC->ctrl()->getCmd();
		$this->{$cmd}();
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
                $answer = new Answer($DIC->user()->getId(), $question_id, self::DEBUG_TEST_ID, $question_component->readAnswer());
                $question_component->setAnswer($answer);

                $scoring_class = QuestionPlayConfiguration::getScoringClass($question->getPlayConfiguration());
                $scoring = new $scoring_class($question);

                ilUtil::sendInfo("Score: ".$scoring->score($answer));
                break;
        }

        
        $DIC->ui()->mainTemplate()->setContent($question_component->renderHtml());
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
                $answer = $player->GetUserAnswer($question->getId(), (int)$DIC->user()->getId(), self::DEBUG_TEST_ID);
                if (!is_null($answer)) {
                    $question_component->setAnswer($answer);
                }
                break;
            case "POST":
                $answer = new Answer($DIC->user()->getId(), $question->getId(), self::DEBUG_TEST_ID, $question_component->readAnswer());
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
        $DIC->user()->getId(), self::DEBUG_TEST_ID));

    }
}