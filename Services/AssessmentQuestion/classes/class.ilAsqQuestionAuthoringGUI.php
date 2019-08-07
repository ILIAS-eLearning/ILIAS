<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\AssessmentQuestion\Authoring\Application\AuthoringApplicationService;
use ILIAS\AssessmentQuestion\Authoring\Application\AuthoringApplicationServiceSpec;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AuthoringServiceSpecContract;
use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\QuestionTypeSelectForm;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\DomainObjectId;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\Guid;
use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\AsqGUIElementFactory;
use ILIAS\AssessmentQuestion\Play\QuestionComponent;
use ILIAS\AssessmentQuestion\Play\Application\PlayApplicationService;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Answer;

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
	const CMD_PLAY_QUESTION = "playQuestion";
	const CMD_SCORE_QUESTION = "scoreQuestion";
	
	//TODO remove me when no longer needed
	const CMD_DEBUG_QUESTION = "debugQuestion";
    const DEBUG_TEST_ID = 23;
	
	/**
	 * @var AuthoringApplicationService
	 */
	private $authoring_service;
	
	/**
	 * ilAsqQuestionAuthoringGUI constructor.
	 * @param AuthoringServiceSpecContract $authoringQuestionServiceSpec
	 */
	public function __construct(AuthoringServiceSpecContract $authoringQuestionServiceSpec)
	{
	    global $DIC;

	    $asq_spec = new AuthoringApplicationServiceSpec($DIC->user()->getId());
	    $this->authoring_service = new AuthoringApplicationService($asq_spec);
	    
	}
	
	public function executeCommand()
	{
		global $DIC;

		$cmd = $DIC->ctrl()->getCmd();
		$this->{$cmd}();
	}
	
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
	            $this->authoring_service->CreateQuestion(new DomainObjectId($guid), null, $type);
	            $DIC->ctrl()->setParameter($this, self::VAR_QUESTION_ID, $guid);
	            $DIC->ctrl()->redirect($this, self::CMD_EDIT_QUESTION);
	            break;
	    }
	}
    
    public function editQuestion()
    {
        global $DIC;
        
        $question_id = $_GET[self::VAR_QUESTION_ID];
        $question = $this->authoring_service->GetQuestion($question_id);
        $form = AsqGUIElementFactory::CreateQuestionForm($question);
        
        switch($_SERVER['REQUEST_METHOD'])
        {
            case "POST":
                $question = $form->getQuestion();
                $this->authoring_service->SaveQuestion($question);
                $form = AsqGUIElementFactory::CreateQuestionForm($question);
                ilutil::sendSuccess("Question Saved");
                break;
        }
        
        $DIC->ui()->mainTemplate()->setContent($form->getHTML());
    }
    
    public function debugQuestions()
    {
        global $DIC;
        
        $questions = $this->authoring_service->GetQuestions();
        
        $DIC->ui()->mainTemplate()->setContent(join("\n", array_map(
            function($question) {
                global $DIC;
                
                $DIC->ctrl()->setParameter($this, self::VAR_QUESTION_ID, $question["aggregate_id"]);
                
                return "<div>" . 
                            $question["aggregate_id"] . 
                            "<a href='" . $DIC->ctrl()->getLinkTarget($this, self::CMD_EDIT_QUESTION) . "'>    Edit</a>" .
                            "<a href='" . $DIC->ctrl()->getLinkTarget($this, self::CMD_PLAY_QUESTION) . "'>   Play</a>" .
                            "<a href='" . $DIC->ctrl()->getLinkTarget($this, self::CMD_SCORE_QUESTION) . "'>   Score</a>" .
                        "</ div>";
            }, $questions)));
    }
    
    public function playQuestion()
    {
        global $DIC;
        
        $question_id = $_GET[self::VAR_QUESTION_ID];
        $question = $this->authoring_service->GetQuestion($question_id);
        
        $player = new PlayApplicationService();
        
        
        
        $question_component = new QuestionComponent($question);
        switch($_SERVER['REQUEST_METHOD'])
        {
            case "GET":
                $answer = $player->GetUserAnswer($question_id, $DIC->user()->getId(), self::DEBUG_TEST_ID);
                if (!is_null($answer)) {
                    $question_component->setAnswer($answer);
                }
                break;
            case "POST":
                $answer = new Answer($DIC->user()->getId(), $question_id, self::DEBUG_TEST_ID, $question_component->readAnswer());
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
        
        $DIC->ui()->mainTemplate()->setContent($player->GetPointsByUser($question_id, $DIC->user()->getId(), self::DEBUG_TEST_ID));
    }
}