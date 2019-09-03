<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Services\AssessmentQuestion\PublicApi\Common\AuthoringContextContainer;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\AssessmentEntityId;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\QuestionComponent;
use ILIS\AssessmentQuestion\Application\AuthoringApplicationService;
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


    public function showPreview(QuestionComponent $question_component = null)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        if( $question_component === null )
        {
            $question_component = new QuestionComponent(
                $this->authoringApplicationService->GetQuestion($this->questionId->getId())
            );
        }

        $DIC->ui()->mainTemplate()->setContent($question_component->renderHtml());
    }

    public function scorePreview()
    {
        $question = $this->authoringApplicationService->GetQuestion($this->questionId->getId());
        $question_component = new QuestionComponent($question);

        $answer = new Answer(
            $this->contextContainer->getActorId(),
            $this->questionId->getId(),
            $this->contextContainer->getObjId(),
            $question_component->readAnswer()
        );

        $question_component->setAnswer($answer);

        $scoring_class = QuestionPlayConfiguration::getScoringClass($question->getPlayConfiguration());
        $scoring = new $scoring_class($question);

        ilUtil::sendInfo("Score: ".$scoring->score($answer));

        $this->showPreview($question_component);
    }
}
