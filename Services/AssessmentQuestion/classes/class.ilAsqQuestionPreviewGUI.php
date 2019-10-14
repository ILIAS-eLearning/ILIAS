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
     * @var AuthoringContextContainer
     */
    //  protected $context_container;
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
        AssessmentEntityId $question_id
    ) {
        $this->authoring_application_service = $authoring_application_service;
        $this->processing_application_service = $processing_application_service;
        $this->question_id = $question_id;
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
                        $this->showFeedback();
                        break;
                    case self::CMD_SHOW_PREVIEW:
                    default:
                        $this->showPreview();
                        break;
                }
        }
    }


    public function showPreview()
    {
        global $DIC;
        $DIC->ui()->mainTemplate()->setContent($this->getQuestionTpl()->get());
    }


    public function showFeedback()
    {
        global $DIC;
        $question_tpl = $this->getQuestionTpl();
        $question_dto = $this->authoring_application_service->getQuestion($this->question_id->getId());

        $feedback_component = $this->processing_application_service->getFeedbackComponent($question_dto);
        $question_tpl->setCurrentBlock('instant_feedback');
        $question_tpl->setVariable('INSTANT_FEEDBACK',$feedback_component->getHtml());
        $question_tpl->parseCurrentBlock();

        $DIC->ui()->mainTemplate()->setContent($question_tpl->get());
    }


    private function getQuestionTpl() : ilTemplate
    {
        global $DIC;
        /* @var \ILIAS\DI\Container $DIC */
        $question_dto = $this->authoring_application_service->getQuestion($this->question_id->getId());

        $question_commands = new QuestionCommands();
        $question_page = $this->processing_application_service->getQuestionPresentation($question_dto, $question_commands);

        $tpl = new ilTemplate('tpl.question_preview_container.html', true, true, 'Services/AssessmentQuestion');

        $tpl->setVariable('FORMACTION', $DIC->ctrl()->getFormAction($this, self::CMD_SHOW_PREVIEW));
        $tpl->setVariable('QUESTION_OUTPUT', $question_page->preview());

        return $tpl;
    }


    public function scorePreview()
    {
        global $DIC;
        $question_dto = $this->authoring_application_service->getQuestion($this->question_id->getId());
        $scoring_component = $this->processing_application_service->getScoringComponent($question_dto);

        /**
         * TODO: we should think about the QuestionComponent again (later).
         * Currently it handles rendering of the question inputs
         * as well as reading from request,
         * altough the answer behavior is settable from outside.
         */

        $answer = new Answer(
            $this->context_container->getActorId(),
            $this->question_id->getId(),
            $this->context_container->getObjId(),
            '',
            $this->questionComponent->readAnswer()
        );

        $this->questionComponent->setAnswer($answer);

        $scoring_class = QuestionPlayConfiguration::getScoringClass($this->questionComponent->getQuestionDto()->getPlayConfiguration());
        $scoring = new $scoring_class($this->questionComponent->getQuestionDto());

        ilUtil::sendInfo("Score: " . $scoring->score($answer));

        $this->showPreview();
    }
}
