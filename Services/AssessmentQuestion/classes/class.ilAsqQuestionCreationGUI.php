<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\AssessmentQuestion\CQRS\Aggregate\DomainObjectId;
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
     * @var ilAsqQuestionPageService
     */
    protected $questionPageService;


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

        $this->questionPageService = new \ilAsqQuestionPageService();
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

                $cmd = $DIC->ctrl()->getCmd(self::CMD_SHOW_CREATE_FORM);
                $this->{$cmd}();
        }
    }


    protected function buildCreationForm() : QuestionTypeSelectForm
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $form = new QuestionTypeSelectForm();
        $form->setFormAction($DIC->ctrl()->getFormAction($this, self::CMD_SHOW_CREATE_FORM));
        $form->addCommandButton(self::CMD_CREATE_QUESTION,$DIC->language()->txt('create'));
        $form->addCommandButton(self::CMD_CANCEL_CREATION,$DIC->language()->txt('cancel'));

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

        $guid = $this->questionId->getId();

        $this->authoringApplicationService->CreateQuestion(
            new DomainObjectId($guid),
            $this->contextContainer->getObjId(), 
            null, //new questions get dynamic ids
            $form->getQuestionType()
        );

        $question = $this->authoringApplicationService->GetQuestion($guid);

        if( \ilObjAssessmentFolder::isAdditionalQuestionContentEditingModePageObjectEnabled() )
        {
            if( $form->hasContentEditingMode() )
            {
                // TODO: save content editing mode to question in any way.
            }
        }

        $this->questionPageService->createPage(
            $this->contextContainer->getObjId(), $question->getQuestionIntId()
        );

        $DIC->ctrl()->redirectToURL(str_replace('&amp;', '&',
            $this->publicAuthoringService->question($this->questionId)->getEditLink(array())->getAction()
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
