<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\AssessmentQuestion\Application\AuthoringApplicationService;
use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\QuestionFeedbackFormGUI;

/**
 * Class AsqQuestionFeedbackEditorGUI
 *
 * @author       studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author       Adrian Lüthi <al@studer-raimann.ch>
 * @author       Björn Heyser <bh@bjoernheyser.de>
 * @author       Martin Studer <ms@studer-raimann.ch>
 * @author       Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_Calls AsqQuestionFeedbackEditorGUI: ilAsqGenericFeedbackPageGUI
 * @ilCtrl_Calls AsqQuestionFeedbackEditorGUI: ilAsqAnswerOptionFeedbackPageGUI
 */
class ilAsqQuestionFeedbackEditorGUI
{

    const CMD_SHOW_FEEDBACK_FORM = 'showFeedbackForm';
    const CMD_SAVE_FEEDBACK = 'saveFeedback';
    /**
     * @var QuestionDto
     */
    protected $question_dto;
    /**
     * @var AuthoringApplicationService
     */
    protected $authoring_application_service;


    /**
     * @param QuestionDto $question_dto
     * @param AuthoringApplicationService $authoring_application_service
     */
    public function __construct(
        QuestionDto $question_dto,
        AuthoringApplicationService $authoring_application_service
    ) {
        $this->question_dto = $question_dto;
        $this->authoring_application_service = $authoring_application_service;
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
                    $DIC->ctrl()->getLinkTarget($this, self::CMD_SHOW_FEEDBACK_FORM)
                );

                $gui = new \ilAsqGenericFeedbackPageGUI($this->question_dto);

                if (strlen($DIC->ctrl()->getCmd()) == 0 && !isset($_POST["editImagemapForward_x"])) {
                    // workaround for page edit imagemaps, keep in mind

                    $DIC->ctrl()->setCmdClass(strtolower(get_class($gui)));
                    $DIC->ctrl()->setCmd('preview');
                }

                $html = $DIC->ctrl()->forwardCommand($gui);
                $DIC->ui()->mainTemplate()->setContent($html);

                break;

            case strtolower(ilAsqAnswerOptionFeedbackPageGUI::class):

                $DIC->tabs()->clearTargets();

                $DIC->tabs()->setBackTarget($DIC->language()->txt('asq_back_to_question_link'),
                    $DIC->ctrl()->getLinkTarget($this, self::CMD_SHOW_FEEDBACK_FORM)
                );

                $gui = new \ilAsqAnswerOptionFeedbackPageGUI($this->question_dto);

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

                $cmd = $DIC->ctrl()->getCmd(self::CMD_SHOW_FEEDBACK_FORM);
                $this->{$cmd}();
        }
    }

    protected function saveFeedback() {
        global $DIC;
        
        $form = $this->createForm();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
            $form->checkInput()) 
        {
            $new_feedback = $form->getFeedbackFromPost();
            $this->question_dto->setFeedback($new_feedback);
            $this->authoring_application_service->saveQuestion($this->question_dto);
            ilutil::sendSuccess("Question Saved", true);
        }
            
        $DIC->ui()->mainTemplate()->setContent($form->getHTML());
    }

    protected function showFeedbackForm()
    {
        global $DIC;

        $form = $this->createForm();

        $DIC->ui()->mainTemplate()->setContent($form->getHTML());
    }

    private function createForm()
    {
        global $DIC;
        $form = new QuestionFeedbackFormGUI($this->question_dto);
        $form->setFormAction($DIC->ctrl()->getFormAction($this, self::CMD_SHOW_FEEDBACK_FORM));
        $form->addCommandButton(self::CMD_SAVE_FEEDBACK, $DIC->language()->txt('save'));
        $form->addCommandButton(self::CMD_SHOW_FEEDBACK_FORM, $DIC->language()->txt('cancel'));
        return $form;
    }

}
