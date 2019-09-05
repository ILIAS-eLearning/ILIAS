<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Services\AssessmentQuestion\PublicApi\Common\AuthoringContextContainer;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\AssessmentEntityId;
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
     * @var QuestionDto
     */
    protected $questionDto;

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
        AuthoringApplicationService $authoringApplicationService

    )
    {
        $this->contextContainer = $contextContainer;
        $this->questionId = $questionId;
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
            $this->questionDto = $this->authoringApplicationService->GetQuestion(
                $this->questionId->getId()
            );

            $this->questionComponent = new QuestionComponent($this->questionDto);
        }

        $questionHtml = $this->questionComponent->renderHtml(self::CMD_SCORE_PREVIEW);

        $qstPageGUI = new ilAsqQuestionPageGUI($this->questionDto->getQuestionIntId());
        $qstPageGUI->setRenderPageContainer(false);
        $qstPageGUI->setEditPreview(true);
        $qstPageGUI->setEnabledTabs(false);

        $qstPageGUI->setQuestionHTML([
            $this->questionDto->getQuestionIntId() => $questionHtml
        ]);

        $qstPageGUI->setPresentationTitle($this->questionDto->getData()->getTitle());

        $tpl = new ilTemplate('tpl.question_preview_container.html', true, true, 'Services/AssessmentQuestion');

        $tpl->setVariable('FORMACTION', $DIC->ctrl()->getFormAction($this, self::CMD_SHOW_PREVIEW));
        $tpl->setVariable('QUESTION_OUTPUT', $qstPageGUI->preview());

        $DIC->ui()->mainTemplate()->setContent($tpl->get());
    }

    public function scorePreview()
    {
        $this->questionDto = $this->authoringApplicationService->GetQuestion($this->questionId->getId());
        $this->questionComponent = new QuestionComponent($this->questionDto);

        $answer = new Answer(
            $this->contextContainer->getActorId(),
            $this->questionId->getId(),
            $this->contextContainer->getObjId(),
            $this->questionComponent->readAnswer()
        );

        $this->questionComponent->setAnswer($answer);

        $scoring_class = QuestionPlayConfiguration::getScoringClass($this->questionDto->getPlayConfiguration());
        $scoring = new $scoring_class($this->questionDto);

        ilUtil::sendInfo("Score: ".$scoring->score($answer));

        $this->showPreview();
    }
}
