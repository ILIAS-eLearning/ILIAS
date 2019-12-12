<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\AssessmentQuestion\Application\ProcessingApplicationService;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\AuthoringContextContainer;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\AssessmentEntityId;
use ILIAS\Services\AssessmentQuestion\PublicApi\Authoring\AuthoringService;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\QuestionComponent;
use ILIAS\AssessmentQuestion\Application\AuthoringApplicationService;
use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ILIAS\AssessmentQuestion\DomainModel\QuestionPlayConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Answer;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\QuestionCommands;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\QuestionConfig;

/**
 * Class ilAsqQuestionPreviewGUI
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ilAsqQuestionPreviewGUI
{

    const CMD_SHOW_PREVIEW = 'showPreview';
    const CMD_SHWOW_Feedback = 'showFeedback';
    //const CMD_SCORE_PREVIEW = 'scorePreview';
    /**
     * @var ProcessingApplicationService
     */
    protected $processing_application_service;
    /**
     * @var QuestionConfig
     */
     protected  $question_config;
    /**
     * @var AssessmentEntityId
     */
    protected $question_id;
    /**
     * @var AuthoringApplicationService
     */
    protected $authoring_application_service;
    /**
     * @var AuthoringService
     */
    //  protected $public_authoring_service;
    /**
     * @var QuestionComponent
     */
    // protected $questionComponent;

    /**
     * ilAsqQuestionCreationGUI constructor.
     *
     * @param AuthoringContextContainer $contextContainer
     */
    public function __construct(
        AuthoringApplicationService $authoring_application_service,
        ProcessingApplicationService $processing_application_service,
        AssessmentEntityId $question_id,
        QuestionConfig $question_config
    ) {
        $this->authoring_application_service = $authoring_application_service;
        $this->processing_application_service = $processing_application_service;
        $this->question_id = $question_id;
        $this->question_config = $question_config;
    }


    public function executeCommand()
    {
        global $DIC;
        /* @var ILIAS\DI\Container $DIC */
        switch ($DIC->ctrl()->getNextClass()) {
            case strtolower(self::class):
            default:
                switch ($DIC->ctrl()->getCmd()) {
                    case self::CMD_SHWOW_Feedback:
                    case self::CMD_SHOW_PREVIEW:
                    default:
                        $this->showQuestion();
                        break;
                }
        }
    }

    public function showQuestion()
    {
        global $DIC;

        $question_dto = $this->authoring_application_service->getQuestion($this->question_id->getId());
        $question_page = $this->processing_application_service->getQuestionPageGUI($question_dto,  $this->question_config, new QuestionCommands());
        
        $question_tpl = new ilTemplate('tpl.question_preview_container.html', true, true, 'Services/AssessmentQuestion');
        $question_tpl->setVariable('FORMACTION', $DIC->ctrl()->getFormAction($this, self::CMD_SHOW_PREVIEW));
        $question_tpl->setVariable('QUESTION_OUTPUT', $question_page->showPage());

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $feedback_component = $this->processing_application_service->getFeedbackComponent(
                $question_dto, 
                $this->processing_application_service->createNewAnswer($question_dto, $question_page->getEnteredAnswer()));
            $question_tpl->setCurrentBlock('instant_feedback');
            $question_tpl->setVariable('INSTANT_FEEDBACK',$feedback_component->getHtml());
            $question_tpl->parseCurrentBlock();
        }

        $DIC->ui()->mainTemplate()->setContent($question_tpl->get());
    }
}
