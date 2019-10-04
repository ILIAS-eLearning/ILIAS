<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\AssessmentQuestion\UserInterface\Web\AsqGUIElementFactory;
use ILIAS\AssessmentQuestion\UserInterface\Web\Page\PageFactory;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\AuthoringContextContainer;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\AssessmentEntityId;
use ILIAS\Services\AssessmentQuestion\PublicApi\Authoring\AuthoringService as PublicAuthoringService;
use ILIS\AssessmentQuestion\Application\AuthoringApplicationService;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\QuestionFeedbackFormGUI;

/**
 * Class ilAsqQuestionFeedbackEditorGUI
 *
 * @author       studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author       Adrian Lüthi <al@studer-raimann.ch>
 * @author       Björn Heyser <bh@bjoernheyser.de>
 * @author       Martin Studer <ms@studer-raimann.ch>
 * @author       Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_Calls ilAsqQuestionFeedbackEditorGUI: ilAsqGenericFeedbackPageGUI
 */
class ilAsqQuestionFeedbackEditorGUI
{

    const CMD_SHOW_FEEDBACK = 'showFeedback';
    const CMD_SAVE_FEEDBACK = 'saveFeedback';

    /**
     * @var PublicAuthoringService
     */
    protected $publicAuthoringService;
    /**
     * @var AuthoringApplicationService
     */
    protected $authoringApplicationService;
    /**
     * @var AssessmentEntityId
     */
    protected $questionUid;


    /**
     * ilAsqQuestionFeedbackEditorGUI constructor.
     *
     * @param AuthoringContextContainer   $contextContainer
     * @param PublicAuthoringService      $publicAuthoringService
     * @param AuthoringApplicationService $authoringApplicationService
     * @param AssessmentEntityId          $questionUid
     */
    public function __construct(
        PublicAuthoringService $publicAuthoringService,
        AuthoringApplicationService $authoringApplicationService,
        AssessmentEntityId $questionUid
    ) {
        $this->publicAuthoringService = $publicAuthoringService;
        $this->authoringApplicationService = $authoringApplicationService;
        $this->questionUid = $questionUid;
    }


    /**
     * @throws ilCtrlException
     */
    public function executeCommand()
    {
        global $DIC;
        /* @var ILIAS\DI\Container $DIC */
        switch ($DIC->ctrl()->getNextClass()) {
            case strtolower(ilAsqGenericFeedbackPageGUI::class):

                $DIC->tabs()->clearTargets();

                $DIC->tabs()->setBackTarget($DIC->language()->txt('asq_back_to_question_link'),
                    $DIC->ctrl()->getLinkTarget($this, self::CMD_SHOW_FEEDBACK)
                );

                $question = $this->authoringApplicationService->GetQuestion($this->questionUid->getId());
                //$feedbackIntId = $question->getFeedbackCorrect()->getIntId();

                $page_factory = new PageFactory($question->getContainerObjId(),$question->getQuestionIntId());
                $gui = $this->publicAuthoringService->getGenericFeedbackPageGUI($page_factory->getFeedbackPage());

                if (strlen($DIC->ctrl()->getCmd()) == 0 && !isset($_POST["editImagemapForward_x"])) {
                    // workaround for page edit imagemaps, keep in mind

                    $DIC->ctrl()->setCmdClass(strtolower(get_class($gui)));
                    $DIC->ctrl()->setCmd('preview');
                }

                $html = $DIC->ctrl()->forwardCommand($gui);
                $DIC->ui()->mainTemplate()->setContent($html);

                break;

            case strtolower(self::class):
            default:

                $cmd = $DIC->ctrl()->getCmd(self::CMD_SHOW_FEEDBACK);
                $this->{$cmd}();
        }
    }


    protected function showFeedback(QuestionFeedbackFormGUI $form = null)
    {
        global $DIC;
        /* @var \ILIAS\DI\Container $DIC */

        if ($form === null) {
            $form = $this->buildForm();
        }

        $DIC->ui()->mainTemplate()->setContent($form->getHTML());
    }


    protected function saveFeedback()
    {
        global $DIC;
        /* @var \ILIAS\DI\Container $DIC */

        $form = $this->buildForm();

        if ($form->checkInput()) {
            $question = $this->authoringApplicationService->GetQuestion($this->questionUid->getId());

            if ($question->getContentEditingMode()->isRteTextarea()) {
                //$question->getFeedbackCorrect()->setContent($form->getFeedbackCorrect());
                //$question->getFeedbackWrong()->setContent($form->getFeedbackWrong());
            }

            $DIC->ctrl()->redirect($this, self::CMD_SHOW_FEEDBACK);
        }

        $this->showFeedback($form);
    }


    /**
     * @return QuestionFeedbackFormGUI
     */
    protected function buildForm() : QuestionFeedbackFormGUI
    {
        global $DIC;
        /* @var \ILIAS\DI\Container $DIC */

        $form = AsqGUIElementFactory::CreateQuestionFeedbackForm($this->authoringApplicationService->GetQuestion($this->questionUid->getId()));

        $form->setFormAction($DIC->ctrl()->getFormAction($this, self::CMD_SHOW_FEEDBACK));
        $form->addCommandButton(self::CMD_SAVE_FEEDBACK, $DIC->language()->txt('save'));
        $form->addCommandButton(self::CMD_SHOW_FEEDBACK, $DIC->language()->txt('cancel'));

        return $form;
    }
}
