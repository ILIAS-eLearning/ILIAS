<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Services\AssessmentQuestion\PublicApi\Common\AuthoringContextContainer;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\AssessmentEntityId;
use ILIAS\Services\AssessmentQuestion\PublicApi\Authoring\AuthoringService;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\QuestionComponent;
use ILIS\AssessmentQuestion\Application\AuthoringApplicationService;
use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ILIAS\AssessmentQuestion\DomainModel\QuestionPlayConfiguration;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Answer;

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

    )
    {
        $this->contextContainer = $contextContainer;
        $this->questionId = $questionId;
        $this->publicAuthoringService = $publicAuthoringService;
        $this->authoringApplicationService = $authoringApplicationService;
    }


    public function executeCommand()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */

        switch( $DIC->ctrl()->getNextClass() )
        {
            case strtolower(self::class):
            default:

                $cmd = $DIC->ctrl()->getCmd(self::CMD_SHOW_PREVIEW);
                $this->{$cmd}();
        }
    }

    public function showPreview()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        if( $this->questionComponent === null )
        {
            $this->questionComponent = $this->publicAuthoringService->questionComponent($this->questionId);
        }

        $qstPageGUI = $this->publicAuthoringService->getQuestionPage(
            $this->questionComponent, self::CMD_SCORE_PREVIEW
        );

        $tpl = new ilTemplate('tpl.question_preview_container.html', true, true, 'Services/AssessmentQuestion');

        $tpl->setVariable('FORMACTION', $DIC->ctrl()->getFormAction($this, self::CMD_SHOW_PREVIEW));
        $tpl->setVariable('QUESTION_OUTPUT', $qstPageGUI->preview());

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

        ilUtil::sendInfo("Score: ".$scoring->score($answer));

        $this->showPreview();
    }
}
