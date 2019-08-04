<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AuthoringServiceSpecContract;
use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\QuestionTypeSelectForm;
use ILIAS\AssessmentQuestion\Authoring\_PublicApi\AsqAuthoringService;
use ILIAS\AssessmentQuestion\Authoring\_PublicApi\AsqAuthoringSpec;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\DomainObjectId;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\Guid;
use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\Form\CreateQuestionFormGUI;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\QuestionData;
use ILIAS\AssessmentQuestion\Authoring\UserInterface\Web\AsqGUIElementFactory;

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
	const CMD_CREATE_QUESTION_STEP_2 = "createQuestion2";
	const CMD_EDIT_QUESTION = "editQuestion";

	/**
	 * @var AsqAuthoringService
	 */
	private $authoring_service;
	
	/**
	 * ilAsqQuestionAuthoringGUI constructor.
	 * @param AuthoringServiceSpecContract $authoringQuestionServiceSpec
	 */
	public function __construct(AuthoringServiceSpecContract $authoringQuestionServiceSpec)
	{
	    global $DIC;

	    $asq_spec = new AsqAuthoringSpec($DIC->ui()->mainTemplate(), 
	        $DIC->language(), 0, $DIC->user()->getId());
	    $this->authoring_service = new AsqAuthoringService($asq_spec);
	    
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
	            $DIC->ctrl()->redirect($this, self::CMD_CREATE_QUESTION_STEP_2);
	            break;
	    }
	}
	
    public function createQuestion2()
    {
        global $DIC;
        
        $form = new CreateQuestionFormGUI();
        
        switch($_SERVER['REQUEST_METHOD'])
        {
            case "GET":
                $DIC->ui()->mainTemplate()->setContent($form->getHTML());
                break;
            case "POST":
                $question_id = $_GET[self::VAR_QUESTION_ID];
                $question = $this->authoring_service->GetQuestion($question_id);

                $question->setData(
                    new QuestionData
                    
                    (
                        $form->getQuestionTitle(), 
                        $form->getQuestionText(), 
                        $form->getQuestionAuthor(),
                        $form->getQuestionDescription()));
                
                $this->authoring_service->SaveQuestion($question);
                $DIC->ctrl()->setParameter($this, self::VAR_QUESTION_ID, $question_id);
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
}