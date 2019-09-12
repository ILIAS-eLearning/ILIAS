<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Services\AssessmentQuestion\PublicApi\Common\AuthoringContextContainer;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\AssessmentEntityId;
use ILIAS\AssessmentQuestion\UserInterface\Web\AsqGUIElementFactory;
use ILIS\AssessmentQuestion\Application\AuthoringApplicationService;
use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;

/**
 * Class ilAsqQuestionConfigEditorGUI
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ilAsqQuestionConfigEditorGUI
{
    const CMD_SHOW_FORM = 'showForm';
    const CMD_SAVE_FORM = 'saveForm';
    const CMD_SAVE_AND_RETURN = 'saveAndReturn';

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

                $cmd = $DIC->ctrl()->getCmd(self::CMD_SHOW_FORM);
                $this->{$cmd}();
        }
    }


    /**
     * @param ilPropertyFormGUI|null $form
     * @throws Exception
     */
    protected function showForm(ilPropertyFormGUI $form = null)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        if( $form === null )
        {
            $question = $this->buildQuestion();
            $form = $this->buildForm($question);
        }

        $DIC->ui()->mainTemplate()->addJavaScript(
            'Services/AssessmentQuestion/js/AssessmentQuestionAuthoring.js'
        );

        $DIC->ui()->mainTemplate()->setContent($form->getHTML());
    }


    /**
     * @throws Exception
     */
    protected function saveForm()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $form = $this->buildForm();

        if( !$form->checkInput() )
        {
            $this->showForm($form);
            return;
        }

        $question = $form->getQuestion();
        $this->authoringApplicationService->SaveQuestion($question);

        ilutil::sendSuccess("Question Saved", true);
        $DIC->ctrl()->redirect($this, self::CMD_SHOW_FORM);
    }

    /**
     * @throws Exception
     */
    protected function saveAndReturn()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $form = $this->buildForm();
        
        if( !$form->checkInput() )
        {
            $this->showForm($form);
            return;
        }
        
        $question = $form->getQuestion();
        $this->authoringApplicationService->SaveQuestion($question);

        $DIC->ctrl()->redirectToUrl(str_replace('&amp;', '&',
            $this->contextContainer->getBackLink()->getAction()
        ));
    }

    /**
     * @return ilPropertyFormGUI
     * @throws Exception
     */
    protected function buildForm() : ilPropertyFormGUI // TODO: should be any interface
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $question = $this->buildQuestion();

        $form = AsqGUIElementFactory::CreateQuestionForm($question);
        $form->setFormAction($DIC->ctrl()->getFormAction($this, self::CMD_SHOW_FORM));
        $form->addCommandButton(self::CMD_SAVE_AND_RETURN, $DIC->language()->txt('save_return'));
        $form->addCommandButton(self::CMD_SAVE_FORM, $DIC->language()->txt('save'));

        return $form;
    }


    /**
     * @return QuestionDto
     */
    protected function buildQuestion() : QuestionDto
    {
        $question_id = $this->questionId->getId();
        $question = $this->authoringApplicationService->GetQuestion($question_id);

        return $question;
    }
}
