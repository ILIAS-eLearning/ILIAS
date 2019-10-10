<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\AssessmentQuestion\Application\PlayApplicationService;
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
    const CMD_SCORE_PREVIEW = 'scorePreview';
    /**
     * @var AuthoringContextContainer
     */
    protected $contextContainer;
    /**
     * @var AssessmentEntityId
     */
    protected $questionId;
    /**
     * @var AuthoringApplicationService
     */
    protected $authoringApplicationService;
    /**
     * @var AuthoringService
     */
    protected $publicAuthoringService;
    /**
     * @var QuestionComponent
     */
    protected $questionComponent;


    /**
     * ilAsqQuestionCreationGUI constructor.
     *
     * @param AuthoringContextContainer $contextContainer
     */
    public function __construct(
        AuthoringContextContainer $contextContainer,
        AssessmentEntityId $questionId,
        AuthoringService $publicAuthoringService,
        AuthoringApplicationService $authoringApplicationService
    ) {
        $this->contextContainer = $contextContainer;
        $this->questionId = $questionId;
        $this->publicAuthoringService = $publicAuthoringService;
        $this->authoringApplicationService = $authoringApplicationService;
    }


    public function executeCommand()
    {
        global $DIC;
        /* @var ILIAS\DI\Container $DIC */

        switch ($DIC->ctrl()->getNextClass()) {
            case strtolower(self::class):
            default:

                $cmd = $DIC->ctrl()->getCmd(self::CMD_SHOW_PREVIEW);
                $this->{$cmd}();
        }
    }


    public function showPreview()
    {
        global $DIC;
        /* @var \ILIAS\DI\Container $DIC */

        $question_dto = $this->authoringApplicationService->getQuestion($this->questionId->getId());

        $question_commands = new QuestionCommands();
        $question_commands->setShowFeedbackCommand(self::CMD_SCORE_PREVIEW);

        $question_config = new QuestionConfig();
        $question_config->setFeedbackOnDemand(true);

        $play_application_service = new PlayApplicationService($question_dto->getContainerObjId(), $DIC->user()->getId(), $question_config);
        $question_page = $play_application_service->getQuestionPresentation($question_dto, $question_commands);

        $tpl = new ilTemplate('tpl.question_preview_container.html', true, true, 'Services/AssessmentQuestion');

        $tpl->setVariable('FORMACTION', $DIC->ctrl()->getFormAction($this, self::CMD_SHOW_PREVIEW));
        $tpl->setVariable('QUESTION_OUTPUT', $question_page->preview());

        $DIC->ui()->mainTemplate()->setContent($tpl->get());
    }


    public function scorePreview()
    {
        $this->questionComponent = $this->publicAuthoringService->questionComponent($this->questionId);

        /**
         * TODO: we should think about the QuestionComponent again (later).
         * Currently it handles rendering of the question inputs
         * as well as reading from request,
         * altough the answer behavior is settable from outside.
         */

        $answer = new Answer(
            $this->contextContainer->getActorId(),
            $this->questionId->getId(),
            $this->contextContainer->getObjId(),
            $this->questionComponent->readAnswer()
        );

        $this->questionComponent->setAnswer($answer);

        $scoring_class = QuestionPlayConfiguration::getScoringClass($this->questionComponent->getQuestionDto()->getPlayConfiguration());
        $scoring = new $scoring_class($this->questionComponent->getQuestionDto());

        ilUtil::sendInfo("Score: " . $scoring->score($answer));

        $this->showPreview();
    }
}
