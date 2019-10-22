<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerOption;
use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ILIAS\AssessmentQuestion\UserInterface\Web\AsqGUIElementFactory;
use ILIAS\AssessmentQuestion\UserInterface\Web\Page\PageFactory;
use ILIAS\Services\AssessmentQuestion\DomainModel\Feedback\Feedback;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\AuthoringContextContainer;
use ILIAS\Services\AssessmentQuestion\PublicApi\Common\AssessmentEntityId;
use ILIAS\Services\AssessmentQuestion\PublicApi\Authoring\AuthoringService as PublicAuthoringService;
use ILIAS\AssessmentQuestion\Application\AuthoringApplicationService;
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
     * AsqQuestionFeedbackEditorGUI constructor.
     *
     * @param AuthoringContextContainer   $contextContainer
     * @param PublicAuthoringService      $publicAuthoringService
     * @param AuthoringApplicationService $authoringApplicationService
     * @param AssessmentEntityId          $questionUid
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


    protected function showFeedbackForm()
    {
        global $DIC;
        /* @var \ILIAS\DI\Container $DIC */

        $form = new QuestionFeedbackFormGUI($this->question_dto,$this->question_dto->getFeedback(), $this->question_dto->getAnswerOptions());

        $form->setFormAction($DIC->ctrl()->getFormAction($this, self::CMD_SHOW_FEEDBACK_FORM));
        $form->addCommandButton(self::CMD_SAVE_FEEDBACK, $DIC->language()->txt('save'));
        $form->addCommandButton(self::CMD_SHOW_FEEDBACK_FORM, $DIC->language()->txt('cancel'));

        $DIC->ui()->mainTemplate()->setContent($form->getHTML());
    }


    protected function saveFeedback()
    {
        global $DIC;
        /* @var \ILIAS\DI\Container $DIC */

        $current_feedback = QuestionFeedbackFormGUI::getFeedbackFromPost();
        $form = new QuestionFeedbackFormGUI($this->question_dto, $current_feedback, $this->question_dto->getAnswerOptions());

        if (!$form->checkInput()) {
            $this->showFeedbackForm();
            return;
        }


        $this->question_dto->setFeedback($current_feedback);

        $this->authoring_application_service->saveQuestion($this->question_dto);

        ilutil::sendSuccess("Question Saved", true);
        $DIC->ctrl()->redirect($this, self::CMD_SHOW_FEEDBACK_FORM);
    }
}
