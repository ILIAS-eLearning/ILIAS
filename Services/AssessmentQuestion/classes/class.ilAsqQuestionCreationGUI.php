<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\AssessmentQuestion\CQRS\Aggregate\DomainObjectId;
use ILIAS\AssessmentQuestion\CQRS\Aggregate\Guid;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\AuthoringContextContainer;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\AssessmentEntityId;
use ILIAS\Services\AssessmentQuestion\PublicApi\Authoring\AuthoringService;
use ILIS\AssessmentQuestion\Application\AuthoringApplicationService;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\QuestionTypeSelectForm;

/**
 * Class ilAsqQuestionCreationGUI
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ilAsqQuestionCreationGUI
{
    const CMD_SHOW_CREATE_FORM = 'showCreationForm';
    const CMD_CREATE_QUESTION = 'createQuestion';
    const CMD_CANCEL_CREATION = 'cancelCreation';


    /**
     * @var AuthoringContextContainer
     */
    protected $contextContainer;

    /**
     * @var AssessmentEntityId
     */
    protected $questionId;

    /**
     * @var AuthoringService
     */
    protected $publicAuthoringService;

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
        AuthoringService $publicAuthoringService,
        AuthoringApplicationService $authoringApplicationService

    )
    {
        $this->contextContainer = $contextContainer;
        $this->questionId = $questionId;
        $this->publicAuthoringService = $publicAuthoringService;
        $this->authoringApplicationService = $authoringApplicationService;
    }


    /**
     * Execute Command
     */
    public function executeCommand()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */

        switch( $DIC->ctrl()->getNextClass() )
        {
            case strtolower(self::class):
            default:

                $cmd = $DIC->ctrl()->getCmd();
                $this->{$cmd}();
        }
    }


    protected function buildCreationForm() : QuestionTypeSelectForm
    {
        $form = new QuestionTypeSelectForm();
        return $form;
    }


    protected function showCreationForm(QuestionTypeSelectForm $form = null)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        if( $form === null )
        {
            $form = $this->buildCreationForm();
        }

        $DIC->ui()->mainTemplate()->setContent($form->getHTML());
    }


    /**
     * @throws Exception
     */
    protected function createQuestion()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $form = $this->buildCreationForm();

        if( !$form->checkInput() )
        {
            $this->showCreationForm($form);
            return;
        }

        // should be the question uuid handled by the ilAsqQuestionAuthoringGUI
        #$guid = Guid::create();
        $guid = $this->questionId->getId();

        $this->authoringApplicationService->CreateQuestion(new DomainObjectId($guid),
            $this->contextContainer->getObjId(), $form->getQuestionType()
        );

        // ilAsqQuestionAuthoringGUI does also handle surviving of this parameter
        #$DIC->ctrl()->setParameter(
        #    $this, \ilAsqQuestionAuthoringGUI::VAR_QUESTION_ID, $guid
        #);

        $DIC->ctrl()->redirectToURL(str_replace('&amp;', '',
            $this->publicAuthoringService->question($this->questionId)->getEditLink(array())
        ));
    }


    protected function cancelCreation()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $DIC->ctrl()->redirectToURL( str_replace('&amp;', '&',
            $this->contextContainer->getBackLink()->getAction()
        ));
    }
}
