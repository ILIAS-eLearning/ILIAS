<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\QuestionHintFormGUI;
use ILIS\AssessmentQuestion\Application\AuthoringApplicationService;

/**
 * Class ilAsqQuestionHintsEditorGUI
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ilAsqQuestionHintsEditorGUI
{
    const CMD_SAVE_HINT = 'saveHint';
    const CMD_CANCEL = 'cancel';

    const CMD_SHOW_HINT_TABLE = 'showHintTable';
    const CMD_SHOW_ADD_HINT_FORM = 'showAddHintForm';
    const VAR_HINT_ORDER_NUMBER = 'hint_order_number';

    /**
     * @var AuthoringApplicationService
     */
    protected $authoring_application_service;
    /**
     * @var QuestionDto
     */
    protected $question_dto;

    /**
     * ilAsqQuestionPageGUI constructor.
     *
     * @param QuestionDto $question
     */
    function __construct(QuestionDto $question_dto, AuthoringApplicationService $authoring_application_service) {

        $this->question_dto = $question_dto;
        $this->authoring_application_service = $authoring_application_service;
    }


    public function executeCommand()
    {
        global $DIC;

        switch($DIC->ctrl()->getCmd()) {

            case self::CMD_SHOW_ADD_HINT_FORM:
                $this->showAddHintForm();
                break;
            case self::CMD_SAVE_HINT:
                $this->saveHint();
                break;
            case self::CMD_SHOW_HINT_TABLE:
            default:
                $this->showHintTable();
                break;


        }

    }

    public function showHintTable() {
        global $DIC;

        $button = ilLinkButton::getInstance();
        $button->setCaption($DIC->language()->txt('asq_questions_hints_toolbar_cmd_add_hint'), false);
        $button->setUrl(  $DIC->ctrl()->getLinkTarget($this,self::CMD_SHOW_ADD_HINT_FORM));
        $DIC->toolbar()->addButtonInstance($button);

        $table = new ilAsqHintsTableGUI($this, 'show',$this->question_dto);
        $DIC->ui()->mainTemplate()->setContent($table->getHTML());

    }

    /**
     * @throws Exception
     */
    protected function saveHint()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $hint_order_number = intval(filter_input(INPUT_GET, self::VAR_HINT_ORDER_NUMBER, FILTER_VALIDATE_INT));
        $hint = $this->question_dto->getHints()->getSpecificHint($hint_order_number);
        $form = new QuestionHintFormGUI($this->question_dto, $hint);

        if( !$form->checkInput() )
        {
            $this->showAddHintForm($form);
            return;
        }

        $question = $form->getQuestion();
        $this->authoring_application_service->SaveQuestion($question);

        $DIC->ctrl()->redirect($this);
    }

    public function showAddHintForm(QuestionHintFormGUI $form = null) {
        global $DIC;

        if(!is_object($form)) {
            $hint_order_number = intval(filter_input(INPUT_GET, self::VAR_HINT_ORDER_NUMBER, FILTER_VALIDATE_INT));
            $hint = $this->question_dto->getHints()->getSpecificHint($hint_order_number);
            $form = new QuestionHintFormGUI($this->question_dto, $hint);
        }

        $form->setFormAction($DIC->ctrl()->getFormAction($this, self::CMD_SAVE_HINT));
        $form->addCommandButton(self::CMD_SAVE_HINT,$DIC->language()->txt('save'));
        $form->addCommandButton(self::CMD_CANCEL,$DIC->language()->txt('cancel'));

        $DIC->ui()->mainTemplate()->setContent($form->getHTML());
    }
}
