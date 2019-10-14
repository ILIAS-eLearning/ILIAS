<?php

use ILIAS\AssessmentQuestion\Application\AuthoringApplicationService;
use ILIAS\AssessmentQuestion\DomainModel\Hint\Hint;
use ILIAS\AssessmentQuestion\DomainModel\Hint\QuestionHints;
use ILIAS\AssessmentQuestion\DomainModel\QuestionDto;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Hint\Form\HintFormGUI;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Hint\Table\HintTableFieldSelectHint;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Hint\Table\ilAsqHintsTableGUI;

/**
 * Class AsqQuestionHintEditorGUI
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class AsqQuestionHintEditorGUI
{
    //Form
    const CMD_SHOW_HINT_FORM = 'showHintForm';
    const CMD_SAVE_HINT = 'saveHint';
    const CMD_CANCEL = 'cancel';

    //Table
    const CMD_SHOW_HINT_TABLE = 'showHintTable';
    const CMD_SAVE_ORDER_NUMBERS = 'saveOrderNumbers';

    //Common
    const CMD_CONFIRM_DELETE_HINTS = 'confirmDeleteHints';
    const CMD_DELETE_HINTS = 'deleteHints';


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


    /**
     * @throws ilAsqException
     */
    public function executeCommand()
    {
        global $DIC;

        switch($DIC->ctrl()->getCmd()) {

            case self::CMD_SHOW_HINT_FORM:
                $this->showHintForm();
                break;
            case self::CMD_SAVE_HINT:
                $this->saveHint();
                break;
            case self::CMD_SAVE_ORDER_NUMBERS:
                $this->saveOrderNumbers();
                break;
            case self::CMD_CONFIRM_DELETE_HINTS:
                $this->confirmDeleteHints();
                break;
            case self::CMD_DELETE_HINTS:
                $this->deleteHints();
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
        $button->setCaption($DIC->language()->txt('tst_questions_hints_toolbar_cmd_add_hint'), false);
        $button->setUrl(  $DIC->ctrl()->getLinkTarget($this,self::CMD_SHOW_HINT_FORM));
        $DIC->toolbar()->addButtonInstance($button);

        $table = new ilAsqHintsTableGUI($this, 'show',$this->question_dto);

        $DIC->ui()->mainTemplate()->setContent($table->getHTML());
    }


    /**
     * @throws ilAsqException
     */
    public function saveOrderNumbers() {
        global $DIC;

        $new_order_sugestions = ilAsqHintsTableGUI::getHintOrderSuggestionFromPost();

        $hint_suggestions = [];
        foreach($new_order_sugestions as $current_order_number => $new_order_sugestion) {
            $hint = $this->question_dto->getQuestionHints()->getSpecificHint($current_order_number);
            $hint_suggestions[] = Hint::createWithNewOrderNumber($hint,($new_order_sugestion * Hint::ORDER_GAP));
        }

        $this->question_dto->setQuestionHints($this->getQuestionHintsFromArray($hint_suggestions));
        $this->authoring_application_service->saveQuestion($this->question_dto);
        $DIC->ctrl()->redirect($this);
    }


    /**
     * @throws ilAsqException
     */
    public function showHintForm() {
        global $DIC;

        $hint_order_number = intval(filter_input(INPUT_GET, ilAsqHintsTableGUI::VAR_HINT_ORDER_NUMBER, FILTER_VALIDATE_INT));
        if($hint_order_number === 0) {
            //new hint!
            $hint = new Hint((count($this->question_dto->getQuestionHints()->getHints()) + 1) * Hint::ORDER_GAP, '', 0);
        } else {
            $hint = $this->question_dto->getQuestionHints()->getSpecificHint($hint_order_number);
        }

        $form = new HintFormGUI($this->question_dto, $hint);

        $form->setFormAction($DIC->ctrl()->getFormAction($this, self::CMD_SAVE_HINT));
        $form->addCommandButton(self::CMD_SAVE_HINT,$DIC->language()->txt('save'));
        $form->addCommandButton(self::CMD_CANCEL,$DIC->language()->txt('cancel'));

        $DIC->ui()->mainTemplate()->setContent($form->getHTML());
    }


    /**
     * @throws ilAsqException
     */
    protected function saveHint()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $current_hint = HintFormGUI::getHintFromPost();
        $form = new HintFormGUI($this->question_dto, $current_hint);
        if(!$form->checkInput())
        {
            $this->showHintForm($form);
            return;
        }

        $hints_to_save = [];
        //current hint
        $hints_to_save[] = $current_hint;

        //all existing hints
        $existing_hints = $this->question_dto->getQuestionHints()->getHints();
        foreach($existing_hints as $hint) {
            if($current_hint->getOrderNumber() == $hint->getOrderNumber()) {
                continue;
            }
            $hints_to_save[] = $hint;
        }

        $this->question_dto->setQuestionHints($this->getQuestionHintsFromArray($hints_to_save));
        $this->authoring_application_service->saveQuestion($this->question_dto);
        $DIC->ctrl()->redirect($this);
    }


    /**
     * @throws Exception
     */
    protected function confirmDeleteHints()
    {
        global $DIC;

        $hint_order_numbers = [];
        SWITCH($_SERVER['REQUEST_METHOD'] ) {
            case "GET":
                $hint_order_numbers[] = intval(filter_input(INPUT_GET, ilAsqHintsTableGUI::VAR_HINT_ORDER_NUMBER,FILTER_VALIDATE_INT));
                break;
            case "POST":
                $hint_order_numbers = ilAsqHintsTableGUI::getSelectedHintOrderNumnbersFromPost();
                break;
        }

        if( !is_array($hint_order_numbers) || !count($hint_order_numbers) )
        {
            ilUtil::sendFailure($DIC->language()->txt('tst_question_hints_delete_hints_missing_selection_msg'), true);
            $DIC->ctrl()->redirect($this);
        }

        require_once 'Services/Utilities/classes/class.ilConfirmationGUI.php';
        $confirmation = new ilConfirmationGUI();

        $confirmation->setHeaderText($DIC->language()->txt('tst_question_hints_delete_hints_confirm_header'));
        $confirmation->setFormAction($DIC->ctrl()->getFormAction($this));
        $confirmation->setConfirm($DIC->language()->txt('tst_question_hints_delete_hints_confirm_cmd'), self::CMD_DELETE_HINTS);
        $confirmation->setCancel($DIC->language()->txt('cancel'), self::CMD_CANCEL);

        $current_hints = $this->question_dto->getQuestionHints()->getHints();

        foreach($current_hints as $hint)
        {
            /* @var $questionHint ilAssQuestionHint */

            if( in_array($hint->getOrderNumber(), $hint_order_numbers) )
            {
                $confirmation->addItem(HintTableFieldSelectHint::VAR_HINTS_BY_ORDER_NUMBER.'[]', $hint->getOrderNumber(), sprintf(
                    $DIC->language()->txt('tst_question_hints_delete_hints_confirm_item'), $hint->getOrderNumber(), $hint->getContent()
                ));
            }
        }

        $DIC->ui()->mainTemplate()->setContent($confirmation->getHTML());
    }


    /**
     * @throws ilAsqException
     */
    protected function deleteHints() {
        global $DIC;

        $hints_to_delete_order_numbers = [];
        SWITCH($_SERVER['REQUEST_METHOD']) {
            case "GET":
                $hints_to_delete_order_numbers[] = intval(filter_input(INPUT_GET, ilAsqHintsTableGUI::VAR_HINT_ORDER_NUMBER,FILTER_VALIDATE_INT));
                break;
            case "POST":
                $hints_to_delete_order_numbers =  filter_input(INPUT_POST, HintTableFieldSelectHint::VAR_HINTS_BY_ORDER_NUMBER, FILTER_VALIDATE_INT, FILTER_REQUIRE_ARRAY);
                break;
        }

        $current_hints = $this->question_dto->getQuestionHints()->getHints();
        $hints_to_save = [];
        foreach($current_hints as $hint)
        {
            if(!in_array($hint->getOrderNumber(), $hints_to_delete_order_numbers)) {
                $hints_to_save[] = $hint;
            }
        }

        $this->question_dto->setQuestionHints($this->getQuestionHintsFromArray($hints_to_save));
        $this->authoring_application_service->saveQuestion($this->question_dto);
        $DIC->ctrl()->redirect($this);
    }


    /**
     * @param Hint[] $hints_to_save
     *
     * @return QuestionHints
     * @throws ilAsqException
     */
    private function getQuestionHintsFromArray(array $hints_to_save): QuestionHints {
        return new QuestionHints($this->authoring_application_service->reOrderListItems($hints_to_save, Hint::ORDER_GAP));
    }
}
