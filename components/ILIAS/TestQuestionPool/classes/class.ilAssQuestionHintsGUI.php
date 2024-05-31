<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use ILIAS\TestQuestionPool\QuestionPoolDIC;
use ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository;

/**
 * @ilCtrl_Calls ilAssQuestionHintsGUI: ilAssQuestionHintGUI
 *
 * GUI class for hints management of assessment questions
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/TestQuestionPool
 *
 * @ilCtrl_Calls ilAssQuestionHintsGUI: ilAssQuestionHintsTableGUI
 * @ilCtrl_Calls ilAssQuestionHintsGUI: ilAssHintPageGUI
 * @ilCtrl_Calls ilAssQuestionHintsGUI: ilToolbarGUI, ilConfirmationGUI
 */
class ilAssQuestionHintsGUI extends ilAssQuestionHintAbstractGUI
{
    /**
     * command constants
     */
    public const CMD_SHOW_LIST = 'showList';
    public const CMD_SHOW_HINT = 'showHint';
    public const CMD_CONFIRM_DELETE = 'confirmDelete';
    public const CMD_PERFORM_DELETE = 'performDelete';
    public const CMD_SAVE_LIST_ORDER = 'saveListOrder';
    public const CMD_CUT_TO_ORDERING_CLIPBOARD = 'cutToOrderingClipboard';
    public const CMD_PASTE_FROM_ORDERING_CLIPBOARD_BEFORE = 'pasteFromOrderingClipboardBefore';
    public const CMD_PASTE_FROM_ORDERING_CLIPBOARD_AFTER = 'pasteFromOrderingClipboardAfter';
    public const CMD_RESET_ORDERING_CLIPBOARD = 'resetOrderingClipboard';
    public const CMD_CONFIRM_SYNC = 'confirmSync';
    public const CMD_SYNC = 'sync';

    private ?ilAssQuestionHintsOrderingClipboard $hintOrderingClipboard = null;
    private GeneralQuestionPropertiesRepository $questionrepository;
    protected bool $editingEnabled = false;
    private \ilGlobalTemplateInterface $main_tpl;

    public function __construct(assQuestionGUI $questionGUI)
    {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->ctrl = $DIC->ctrl();

        $local_dic = QuestionPoolDIC::dic();
        $this->questionrepository = $local_dic['question.general_properties.repository'];

        parent::__construct($questionGUI);

        $this->hintOrderingClipboard = new ilAssQuestionHintsOrderingClipboard($questionGUI->getObject());
    }

    public function isEditingEnabled(): bool
    {
        return $this->editingEnabled;
    }

    public function setEditingEnabled(bool $editingEnabled): void
    {
        $this->editingEnabled = $editingEnabled;
    }

    public function executeCommand(): void
    {
        global $DIC;
        $ilHelp = $DIC['ilHelp'];
        $ilHelp->setScreenIdComponent('qpl');

        $DIC->ui()->mainTemplate()->setCurrentBlock("ContentStyle");
        $DIC->ui()->mainTemplate()->setVariable("LOCATION_CONTENT_STYLESHEET", ilObjStyleSheet::getContentStylePath(0));
        $DIC->ui()->mainTemplate()->parseCurrentBlock();

        $cmd = $this->ctrl->getCmd(self::CMD_SHOW_LIST);
        $nextClass = $this->ctrl->getNextClass($this);

        switch ($nextClass) {
            case 'ilassquestionhintgui':
                if (!$this->isEditingEnabled()) {
                    return;
                }

                $gui = new ilAssQuestionHintGUI($this->question_gui);
                $this->ctrl->forwardCommand($gui);
                break;

            case 'ilasshintpagegui':
                if ($this->isEditingEnabled()) {
                    $presentationMode = ilAssQuestionHintPageObjectCommandForwarder::PRESENTATION_MODE_AUTHOR;
                } else {
                    $presentationMode = ilAssQuestionHintPageObjectCommandForwarder::PRESENTATION_MODE_PREVIEW;
                }

                $forwarder = new ilAssQuestionHintPageObjectCommandForwarder(
                    $this->question_obj,
                    $this->ctrl,
                    $this->tabs,
                    $this->tabs
                );
                $forwarder->setPresentationMode($presentationMode);
                $forwarder->forward();
                break;

            default:
                $this->tabs->setTabActive('tst_question_hints_tab');
                $cmd .= 'Cmd';
                $this->$cmd();
                break;
        }
    }

    private function showListCmd(string $additional_content = ''): void
    {
        $this->initHintOrderingClipboardNotification();

        $toolbar = new ilToolbarGUI();

        $questionHintList = ilAssQuestionHintList::getListByQuestionId($this->question_obj->getId());

        if ($this->isEditingEnabled()) {
            if ($this->hintOrderingClipboard->hasStored()) {
                $questionHintList = $this->getQuestionHintListWithoutHintStoredInOrderingClipboard($questionHintList);

                $toolbar->addButton(
                    $this->lng->txt('tst_questions_hints_toolbar_cmd_reset_ordering_clipboard'),
                    $this->ctrl->getLinkTarget($this, self::CMD_RESET_ORDERING_CLIPBOARD)
                );
            } else {
                $toolbar->addButton(
                    $this->lng->txt('tst_questions_hints_toolbar_cmd_add_hint'),
                    $this->ctrl->getLinkTargetByClass('ilAssQuestionHintGUI', ilAssQuestionHintGUI::CMD_SHOW_FORM)
                );
            }

            $tableMode = ilAssQuestionHintsTableGUI::TBL_MODE_ADMINISTRATION;
        } else {
            $tableMode = ilAssQuestionHintsTableGUI::TBL_MODE_TESTOUTPUT;
        }

        $table = new ilAssQuestionHintsTableGUI(
            $this->question_obj,
            $questionHintList,
            $this,
            self::CMD_SHOW_LIST,
            $tableMode,
            $this->hintOrderingClipboard
        );

        $this->main_tpl->setContent($toolbar->getHTML() . $table->getHTML() . $additional_content);
    }

    private function confirmDeleteCmd(): void
    {
        $hint_ids = $this->fetchHintIdsParameter();

        if (!count($hint_ids)) {
            $this->main_tpl->setOnScreenMessage(
                'failure',
                $this->lng->txt('tst_question_hints_delete_hints_missing_selection_msg'),
                true
            );
            $this->ctrl->redirectByClass(self::class);
        }

        $confirmation = new ilConfirmationGUI();

        $confirmation->setHeaderText($this->lng->txt('tst_question_hints_delete_hints_confirm_header'));
        $confirmation->setFormAction($this->ctrl->getFormAction($this));
        $confirmation->setConfirm($this->lng->txt('tst_question_hints_delete_hints_confirm_cmd'), self::CMD_PERFORM_DELETE);
        $confirmation->setCancel($this->lng->txt('cancel'), self::CMD_SHOW_LIST);

        $questionHintList = ilAssQuestionHintList::getListByQuestionId($this->question_obj->getId());

        foreach ($questionHintList as $questionHint) {
            /* @var $questionHint ilAssQuestionHint */

            if (in_array($questionHint->getId(), $hint_ids)) {
                $confirmation->addItem('hint_ids[]', $questionHint->getId(), sprintf(
                    $this->lng->txt('tst_question_hints_delete_hints_confirm_item'),
                    $questionHint->getIndex(),
                    $questionHint->getText()
                ));
            }
        }

        $this->main_tpl->setContent($this->ctrl->getHtml($confirmation));
    }

    private function performDeleteCmd(): void
    {
        if (!$this->isEditingEnabled()) {
            return;
        }

        $hintIds = $this->fetchHintIdsParameter();

        if (!count($hintIds)) {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt('tst_question_hints_delete_hints_missing_selection_msg'), true);
            $this->ctrl->redirectByClass(self::class);
        }

        $questionCompleteHintList = ilAssQuestionHintList::getListByQuestionId($this->question_obj->getId());

        $questionRemainingHintList = new ilAssQuestionHintList();

        foreach ($questionCompleteHintList as $questionHint) {
            /* @var $questionHint ilAssQuestionHint */

            if (in_array($questionHint->getId(), $hintIds)) {
                $questionHint->delete();
            } else {
                $questionRemainingHintList->addHint($questionHint);
            }
        }

        $questionRemainingHintList->reIndex();

        $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('tst_question_hints_delete_success_msg'), true);

        if ($this->question_gui->needsSyncQuery()) {
            $this->ctrl->redirectByClass(
                ilAssQuestionHintsGUI::class,
                ilAssQuestionHintsGUI::CMD_CONFIRM_SYNC
            );
        }

        $this->ctrl->redirectByClass(self::class);
    }

    private function saveListOrderCmd(): void
    {
        if (!$this->isEditingEnabled()) {
            return;
        }

        $hintIndexes = $this->fetchHintIndexesParameter();

        if (!count($hintIndexes)) {
            $this->main_tpl->setOnScreenMessage(
                'failure',
                $this->lng->txt('tst_question_hints_save_order_unkown_failure_msg'),
                true
            );
            $this->ctrl->redirectByClass(self::class);
        }

        $curQuestionHintList = ilAssQuestionHintList::getListByQuestionId($this->question_obj->getId());

        $newQuestionHintList = new ilAssQuestionHintList();

        foreach (array_keys($hintIndexes) as $hintId) {
            if (!$curQuestionHintList->hintExists($hintId)) {
                $this->main_tpl->setOnScreenMessage(
                    'failure',
                    $this->lng->txt('tst_question_hints_save_order_unkown_failure_msg'),
                    true
                );
                $this->ctrl->redirectByClass(self::class);
            }

            $questionHint = $curQuestionHintList->getHint($hintId);

            $newQuestionHintList->addHint($questionHint);
        }

        $newQuestionHintList->reIndex();

        $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('tst_question_hints_save_order_success_msg'), true);

        if ($this->question_gui->needsSyncQuery()) {
            $this->ctrl->redirectByClass(
                ilAssQuestionHintsGUI::class,
                ilAssQuestionHintsGUI::CMD_CONFIRM_SYNC
            );
        }

        $this->ctrl->redirectByClass(self::class);
    }

    private function cutToOrderingClipboardCmd(): void
    {
        if (!$this->isEditingEnabled()) {
            return;
        }

        $moveHintIds = $this->fetchHintIdsParameter();
        $this->checkForSingleHintIdAndRedirectOnFailure($moveHintIds);

        $moveHintId = current($moveHintIds);

        $this->checkForExistingHintRelatingToCurrentQuestionAndRedirectOnFailure($moveHintId);

        $this->hintOrderingClipboard->setStored($moveHintId);

        $this->ctrl->redirect($this, self::CMD_SHOW_LIST);
    }

    private function pasteFromOrderingClipboardBeforeCmd(): void
    {
        if (!$this->isEditingEnabled()) {
            return;
        }

        $targetHintIds = $this->fetchHintIdsParameter();
        $this->checkForSingleHintIdAndRedirectOnFailure($targetHintIds);

        $targetHintId = current($targetHintIds);

        $this->checkForExistingHintRelatingToCurrentQuestionAndRedirectOnFailure($targetHintId);

        $curQuestionHintList = ilAssQuestionHintList::getListByQuestionId($this->question_obj->getId());
        $newQuestionHintList = new ilAssQuestionHintList($this->question_obj->getId());

        foreach ($curQuestionHintList as $questionHint) {
            /* @var $questionHint ilAssQuestionHint */

            if ($questionHint->getId() == $this->hintOrderingClipboard->getStored()) {
                continue;
            }

            if ($questionHint->getId() == $targetHintId) {
                $targetQuestionHint = $questionHint;

                $pasteQuestionHint = ilAssQuestionHint::getInstanceById($this->hintOrderingClipboard->getStored());

                $newQuestionHintList->addHint($pasteQuestionHint);
            }

            $newQuestionHintList->addHint($questionHint);
        }

        $successMsg = sprintf(
            $this->lng->txt('tst_question_hints_paste_before_success_msg'),
            $pasteQuestionHint->getIndex(),
            $targetQuestionHint->getIndex()
        );

        $newQuestionHintList->reIndex();

        $this->hintOrderingClipboard->resetStored();

        $this->main_tpl->setOnScreenMessage('success', $successMsg, true);

        $this->ctrl->redirect($this, self::CMD_SHOW_LIST);
    }

    /**
     * pastes a hint from ordering clipboard after the selected one
     *
     * @access	private
     * @global	ilCtrl		$ilCtrl
     * @global	ilLanguage	$lng
     */
    private function pasteFromOrderingClipboardAfterCmd(): void
    {
        if (!$this->isEditingEnabled()) {
            return;
        }

        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];

        $targetHintIds = $this->fetchHintIdsParameter();
        $this->checkForSingleHintIdAndRedirectOnFailure($targetHintIds);

        $targetHintId = current($targetHintIds);

        $this->checkForExistingHintRelatingToCurrentQuestionAndRedirectOnFailure($targetHintId);

        $curQuestionHintList = ilAssQuestionHintList::getListByQuestionId($this->question_obj->getId());
        $newQuestionHintList = new ilAssQuestionHintList($this->question_obj->getId());

        foreach ($curQuestionHintList as $questionHint) {
            /* @var $questionHint ilAssQuestionHint */

            if ($questionHint->getId() == $this->hintOrderingClipboard->getStored()) {
                continue;
            }

            $newQuestionHintList->addHint($questionHint);

            if ($questionHint->getId() == $targetHintId) {
                $targetQuestionHint = $questionHint;

                $pasteQuestionHint = ilAssQuestionHint::getInstanceById($this->hintOrderingClipboard->getStored());

                $newQuestionHintList->addHint($pasteQuestionHint);
            }
        }

        $successMsg = sprintf(
            $lng->txt('tst_question_hints_paste_after_success_msg'),
            $pasteQuestionHint->getIndex(),
            $targetQuestionHint->getIndex()
        );

        $newQuestionHintList->reIndex();

        $this->hintOrderingClipboard->resetStored();

        $this->main_tpl->setOnScreenMessage('success', $successMsg, true);

        $ilCtrl->redirect($this, self::CMD_SHOW_LIST);
    }

    /**
     * resets the ordering clipboard
     *
     * @access	private
     * @global	ilCtrl		$ilCtrl
     * @global	ilLanguage	$lng
     */
    private function resetOrderingClipboardCmd(): void
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];

        $this->hintOrderingClipboard->resetStored();

        $this->main_tpl->setOnScreenMessage('info', $lng->txt('tst_question_hints_ordering_clipboard_resetted'), true);
        $ilCtrl->redirect($this, self::CMD_SHOW_LIST);
    }

    /**
     * inits the notification telling the user,
     * that a hint is stored to hint ordering clipboard
     *
     * @access	private
     * @global	ilLanguage	$lng
     */
    private function initHintOrderingClipboardNotification(): void
    {
        global $DIC;
        $lng = $DIC['lng'];

        if (!$this->hintOrderingClipboard->hasStored()) {
            return;
        }

        $questionHint = ilAssQuestionHint::getInstanceById($this->hintOrderingClipboard->getStored());

        $this->main_tpl->setOnScreenMessage('info', sprintf(
            $lng->txt('tst_question_hints_item_stored_in_ordering_clipboard'),
            $questionHint->getIndex()
        ));
    }

    /**
     * checks for an existing hint relating to current question and redirects
     * with corresponding failure message on failure
     *
     * @access	private
     * @param	integer	$hintId
     */
    private function checkForExistingHintRelatingToCurrentQuestionAndRedirectOnFailure($hintId): void
    {
        $questionHintList = ilAssQuestionHintList::getListByQuestionId($this->question_obj->getId());

        if (!$questionHintList->hintExists($hintId)) {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt('tst_question_hints_invalid_hint_id'), true);
            $this->ctrl->redirect($this, self::CMD_SHOW_LIST);
        }
    }

    private function getQuestionHintListWithoutHintStoredInOrderingClipboard(
        ilAssQuestionHintList $questionHintList
    ): ilAssQuestionHintList {
        $filteredQuestionHintList = new ilAssQuestionHintList();

        foreach ($questionHintList as $questionHint) {
            if ($questionHint->getId() !== $this->hintOrderingClipboard->getStored()) {
                $filteredQuestionHintList->addHint($questionHint);
            }
        }

        return $filteredQuestionHintList;
    }

    private function checkForSingleHintIdAndRedirectOnFailure(array $hint_ids): void
    {
        if ($hint_ids === []) {
            $this->main_tpl->setOnScreenMessage(
                'failure',
                $this->lng->txt('tst_question_hints_cut_hints_missing_selection_msg'),
                true
            );
            $this->ctrl->redirect($this, self::CMD_SHOW_LIST);
        }

        if (count($hint_ids) > 1) {
            $this->main_tpl->setOnScreenMessage(
                'failure',
                $this->lng->txt('tst_question_hints_cut_hints_single_selection_msg'),
                true
            );
            $this->ctrl->redirect($this, self::CMD_SHOW_LIST);
        }
    }

    private function fetchHintIdsParameter(): array
    {
        $hint_ids = [$this->request->int('hint_id')];
        if ($hint_ids[0] !== 0) {
            return $hint_ids;
        }

        return $this->request->retrieveArrayOfIntsFromPost('hint_ids') ?? [];
    }

    private function fetchHintIndexesParameter(): array
    {
        $hint_indexes = $this->request->retrieveArrayOfIntsFromPost('hint_indexes') ?? [];
        asort($hint_indexes);
        return $hint_indexes;
    }

    public function confirmSyncCmd(): void
    {
        $modal = $this->question_gui->getQuestionSyncModal(self::CMD_SYNC, self::class);
        $this->showListCmd($modal);
    }

    public function syncCmd(): void
    {
        $this->question_obj->syncWithOriginal();
        $this->showListCmd();
    }

    public function getHintPresentationLinkTarget(
        int $hint_id,
        bool $xml_style = true
    ): string {
        if ($this->question_obj->isAdditionalContentEditingModePageObject()) {
            $this->ctrl->setParameterByClass('ilasshintpagegui', 'hint_id', $hint_id);
            return $this->ctrl->getLinkTargetByClass('ilAssHintPageGUI', '', '', false, $xml_style);
        }

        $this->ctrl->setParameter($this, 'hintId', $hint_id);
        return $this->ctrl->getLinkTarget($this, self::CMD_SHOW_HINT, '', false, $xml_style);
    }

    private function showHintCmd(): void
    {
        if (!$this->request->isset('hintId') || !(int) $this->request->raw('hintId')) {
            throw new ilTestException('no hint id given');
        }

        $this->tabs->clearTargets();
        $this->tabs->clearSubTabs();

        $this->tabs->setBackTarget(
            $this->lng->txt('tst_question_hints_back_to_hint_list'),
            $this->ctrl->getLinkTargetByClass(self::class, self::CMD_SHOW_LIST)
        );

        $questionHint = ilAssQuestionHint::getInstanceById((int) $this->request->raw('hintId'));

        // build form

        $form = new ilPropertyFormGUI();

        $form->setFormAction($this->ctrl->getFormAction($this));

        $form->setTableWidth('100%');

        $form->setTitle(sprintf(
            $this->lng->txt('tst_question_hints_form_header_edit'),
            $questionHint->getIndex(),
            $this->question_obj->getTitle()
        ));

        // form input: hint text

        $nonEditableHintText = new ilNonEditableValueGUI(
            $this->lng->txt('tst_question_hints_form_label_hint_text'),
            'hint_text',
            true
        );
        $nonEditableHintText->setValue(
            ilLegacyFormElementsUtil::prepareTextareaOutput($questionHint->getText(), true)
        );
        $form->addItem($nonEditableHintText);

        // form input: hint points

        $nonEditableHintPoints = new ilNonEditableValueGUI(
            $this->lng->txt('tst_question_hints_form_label_hint_points'),
            'hint_points'
        );
        $nonEditableHintPoints->setValue($questionHint->getPoints());
        $form->addItem($nonEditableHintPoints);

        $this->main_tpl->setContent($form->getHTML());
    }
}
