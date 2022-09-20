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

require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintAbstractGUI.php';
require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintGUI.php';
require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintsOrderingClipboard.php';

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

    private $hintOrderingClipboard = null;
    private ilLanguage $lng;
    private ilCtrl $ctrl;

    /**
     * @var bool
     */
    protected $editingEnabled = false;
    private \ilGlobalTemplateInterface $main_tpl;

    /**
     * Constructor
     *
     * @access	public
     * @param	assQuestionGUI	$questionGUI
     */
    public function __construct(assQuestionGUI $questionGUI)
    {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        parent::__construct($questionGUI);

        $this->hintOrderingClipboard = new ilAssQuestionHintsOrderingClipboard($questionGUI->object);
    }

    /**
     * @return bool
     */
    public function isEditingEnabled(): bool
    {
        return $this->editingEnabled;
    }

    /**
     * @param bool $editingEnabled
     */
    public function setEditingEnabled(bool $editingEnabled): void
    {
        $this->editingEnabled = $editingEnabled;
    }

    public function executeCommand(): void
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $ilTabs = $DIC['ilTabs'];
        $lng = $DIC['lng'];
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        $ilHelp = $DIC['ilHelp']; /* @var ilHelpGUI $ilHelp */
        $ilHelp->setScreenIdComponent('qpl');

        require_once "./Services/Style/Content/classes/class.ilObjStyleSheet.php";
        $DIC->ui()->mainTemplate()->setCurrentBlock("ContentStyle");
        $DIC->ui()->mainTemplate()->setVariable("LOCATION_CONTENT_STYLESHEET", ilObjStyleSheet::getContentStylePath(0));
        $DIC->ui()->mainTemplate()->parseCurrentBlock();

        $cmd = $ilCtrl->getCmd(self::CMD_SHOW_LIST);
        $nextClass = $ilCtrl->getNextClass($this);

        switch ($nextClass) {
            case 'ilassquestionhintgui':

                if (!$this->isEditingEnabled()) {
                    return;
                }

                require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintGUI.php';
                $gui = new ilAssQuestionHintGUI($this->questionGUI);
                $ilCtrl->forwardCommand($gui);
                break;

            case 'ilasshintpagegui':

                if ($this->isEditingEnabled()) {
                    $presentationMode = ilAssQuestionHintPageObjectCommandForwarder::PRESENTATION_MODE_AUTHOR;
                } else {
                    $presentationMode = ilAssQuestionHintPageObjectCommandForwarder::PRESENTATION_MODE_PREVIEW;
                }

                require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintPageObjectCommandForwarder.php';
                $forwarder = new ilAssQuestionHintPageObjectCommandForwarder($this->questionOBJ, $ilCtrl, $ilTabs, $lng);
                $forwarder->setPresentationMode($presentationMode);
                $forwarder->forward();
                break;

            default:

                $cmd .= 'Cmd';
                $this->$cmd();
                break;
        }
    }

    /**
     * shows a table with existing hints
     *
     * @access	private
     * @global	ilTemplate	$tpl
     */
    private function showListCmd(): void
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $tpl = $DIC['tpl'];
        $lng = $DIC['lng'];

        $this->initHintOrderingClipboardNotification();

        require_once 'Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php';
        require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintsTableGUI.php';

        $toolbar = new ilToolbarGUI();

        $questionHintList = ilAssQuestionHintList::getListByQuestionId($this->questionOBJ->getId());

        if ($this->isEditingEnabled()) {
            if ($this->hintOrderingClipboard->hasStored()) {
                $questionHintList = $this->getQuestionHintListWithoutHintStoredInOrderingClipboard($questionHintList);

                $toolbar->addButton(
                    $lng->txt('tst_questions_hints_toolbar_cmd_reset_ordering_clipboard'),
                    $ilCtrl->getLinkTarget($this, self::CMD_RESET_ORDERING_CLIPBOARD)
                );
            } else {
                $toolbar->addButton(
                    $lng->txt('tst_questions_hints_toolbar_cmd_add_hint'),
                    $ilCtrl->getLinkTargetByClass('ilAssQuestionHintGUI', ilAssQuestionHintGUI::CMD_SHOW_FORM)
                );
            }

            $tableMode = ilAssQuestionHintsTableGUI::TBL_MODE_ADMINISTRATION;
        } else {
            $tableMode = ilAssQuestionHintsTableGUI::TBL_MODE_TESTOUTPUT;
        }

        $table = new ilAssQuestionHintsTableGUI(
            $this->questionOBJ,
            $questionHintList,
            $this,
            self::CMD_SHOW_LIST,
            $tableMode,
            $this->hintOrderingClipboard
        );

        $tpl->setContent($ilCtrl->getHtml($toolbar) . $ilCtrl->getHtml($table));
    }

    /**
     * shows a confirmation screen with selected hints for deletion
     *
     * @access	private
     * @global	ilCtrl		$ilCtrl
     * @global	ilTemplate	$tpl
     * @global	ilLanguage	$lng
     */
    private function confirmDeleteCmd(): void
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $tpl = $DIC['tpl'];
        $lng = $DIC['lng'];

        $hintIds = self::fetchHintIdsParameter();

        if (!count($hintIds)) {
            $this->main_tpl->setOnScreenMessage('failure', $lng->txt('tst_question_hints_delete_hints_missing_selection_msg'), true);
            $ilCtrl->redirect($this);
        }

        $confirmation = new ilConfirmationGUI();

        $confirmation->setHeaderText($lng->txt('tst_question_hints_delete_hints_confirm_header'));
        $confirmation->setFormAction($ilCtrl->getFormAction($this));
        $confirmation->setConfirm($lng->txt('tst_question_hints_delete_hints_confirm_cmd'), self::CMD_PERFORM_DELETE);
        $confirmation->setCancel($lng->txt('cancel'), self::CMD_SHOW_LIST);

        $questionHintList = ilAssQuestionHintList::getListByQuestionId($this->questionOBJ->getId());

        foreach ($questionHintList as $questionHint) {
            /* @var $questionHint ilAssQuestionHint */

            if (in_array($questionHint->getId(), $hintIds)) {
                $confirmation->addItem('hint_ids[]', $questionHint->getId(), sprintf(
                    $lng->txt('tst_question_hints_delete_hints_confirm_item'),
                    $questionHint->getIndex(),
                    $questionHint->getText()
                ));
            }
        }

        $tpl->setContent($ilCtrl->getHtml($confirmation));
    }

    /**
     * performs confirmed deletion for selected hints
     *
     * @access	private
     * @global	ilCtrl		$ilCtrl
     * @global	ilLanguage	$lng
     */
    private function performDeleteCmd(): void
    {
        if (!$this->isEditingEnabled()) {
            return;
        }

        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $tpl = $DIC['tpl'];
        $lng = $DIC['lng'];

        $hintIds = self::fetchHintIdsParameter();

        if (!count($hintIds)) {
            $this->main_tpl->setOnScreenMessage('failure', $lng->txt('tst_question_hints_delete_hints_missing_selection_msg'), true);
            $ilCtrl->redirect($this);
        }

        $questionCompleteHintList = ilAssQuestionHintList::getListByQuestionId($this->questionOBJ->getId());

        $questionRemainingHintList = new ilAssQuestionHintList();

        foreach ($questionCompleteHintList as $listKey => $questionHint) {
            /* @var $questionHint ilAssQuestionHint */

            if (in_array($questionHint->getId(), $hintIds)) {
                $questionHint->delete();
            } else {
                $questionRemainingHintList->addHint($questionHint);
            }
        }

        $questionRemainingHintList->reIndex();

        $this->main_tpl->setOnScreenMessage('success', $lng->txt('tst_question_hints_delete_success_msg'), true);

        $originalexists = $this->questionOBJ->_questionExistsInPool((int) $this->questionOBJ->getOriginalId());
        include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
        global $DIC;
        $ilUser = $DIC['ilUser'];
        if ($this->request->raw("calling_test") && $originalexists && assQuestion::_isWriteable($this->questionOBJ->getOriginalId(), $ilUser->getId())) {
            $ilCtrl->redirectByClass('ilAssQuestionHintsGUI', ilAssQuestionHintsGUI::CMD_CONFIRM_SYNC);
        }

        $ilCtrl->redirect($this);
    }

    /**
     * saves the order based on index values passed from table's form
     * (the table must not be paginated, because ALL hints index values are required)
     *
     * @access	private
     * @global	ilCtrl		$ilCtrl
     * @global	ilLanguage	$lng
     */
    private function saveListOrderCmd(): void
    {
        if (!$this->isEditingEnabled()) {
            return;
        }

        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];

        $hintIndexes = self::orderHintIndexes(
            self::fetchHintIndexesParameter()
        );

        if (!count($hintIndexes)) {
            $this->main_tpl->setOnScreenMessage('failure', $lng->txt('tst_question_hints_save_order_unkown_failure_msg'), true);
            $ilCtrl->redirect($this);
        }

        $curQuestionHintList = ilAssQuestionHintList::getListByQuestionId($this->questionOBJ->getId());

        $newQuestionHintList = new ilAssQuestionHintList();

        foreach ($hintIndexes as $hintId => $hintIndex) {
            if (!$curQuestionHintList->hintExists($hintId)) {
                $this->main_tpl->setOnScreenMessage('failure', $lng->txt('tst_question_hints_save_order_unkown_failure_msg'), true);
                $ilCtrl->redirect($this);
            }

            $questionHint = $curQuestionHintList->getHint($hintId);

            $newQuestionHintList->addHint($questionHint);
        }

        $newQuestionHintList->reIndex();

        $this->main_tpl->setOnScreenMessage('success', $lng->txt('tst_question_hints_save_order_success_msg'), true);

        $originalexists = $this->questionOBJ->_questionExistsInPool((int) $this->questionOBJ->getOriginalId());
        include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
        global $DIC;
        $ilUser = $DIC['ilUser'];
        if ($this->request->raw("calling_test") && $originalexists && assQuestion::_isWriteable($this->questionOBJ->getOriginalId(), $ilUser->getId())) {
            $ilCtrl->redirectByClass('ilAssQuestionHintsGUI', ilAssQuestionHintsGUI::CMD_CONFIRM_SYNC);
        }

        $ilCtrl->redirect($this);
    }

    /**
     * cuts a hint from question hint list and stores it to ordering clipboard
     *
     * @access	private
     * @global	ilCtrl	$ilCtrl
     */
    private function cutToOrderingClipboardCmd(): void
    {
        if (!$this->isEditingEnabled()) {
            return;
        }

        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];

        $moveHintIds = self::fetchHintIdsParameter();
        $this->checkForSingleHintIdAndRedirectOnFailure($moveHintIds);

        $moveHintId = current($moveHintIds);

        $this->checkForExistingHintRelatingToCurrentQuestionAndRedirectOnFailure($moveHintId);

        $this->hintOrderingClipboard->setStored($moveHintId);

        $ilCtrl->redirect($this, self::CMD_SHOW_LIST);
    }

    /**
     * pastes a hint from ordering clipboard before the selected one
     *
     * @access	private
     * @global	ilCtrl		$ilCtrl
     * @global	ilLanguage	$lng
     */
    private function pasteFromOrderingClipboardBeforeCmd(): void
    {
        if (!$this->isEditingEnabled()) {
            return;
        }

        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];

        $targetHintIds = self::fetchHintIdsParameter();
        $this->checkForSingleHintIdAndRedirectOnFailure($targetHintIds);

        $targetHintId = current($targetHintIds);

        $this->checkForExistingHintRelatingToCurrentQuestionAndRedirectOnFailure($targetHintId);

        $curQuestionHintList = ilAssQuestionHintList::getListByQuestionId($this->questionOBJ->getId());
        $newQuestionHintList = new ilAssQuestionHintList($this->questionOBJ->getId());

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
            $lng->txt('tst_question_hints_paste_before_success_msg'),
            $pasteQuestionHint->getIndex(),
            $targetQuestionHint->getIndex()
        );

        $newQuestionHintList->reIndex();

        $this->hintOrderingClipboard->resetStored();

        $this->main_tpl->setOnScreenMessage('success', $successMsg, true);

        $ilCtrl->redirect($this, self::CMD_SHOW_LIST);
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

        $targetHintIds = self::fetchHintIdsParameter();
        $this->checkForSingleHintIdAndRedirectOnFailure($targetHintIds);

        $targetHintId = current($targetHintIds);

        $this->checkForExistingHintRelatingToCurrentQuestionAndRedirectOnFailure($targetHintId);

        $curQuestionHintList = ilAssQuestionHintList::getListByQuestionId($this->questionOBJ->getId());
        $newQuestionHintList = new ilAssQuestionHintList($this->questionOBJ->getId());

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
        $questionHintList = ilAssQuestionHintList::getListByQuestionId($this->questionOBJ->getId());

        if (!$questionHintList->hintExists($hintId)) {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt('tst_question_hints_invalid_hint_id'), true);
            $this->ctrl->redirect($this, self::CMD_SHOW_LIST);
        }
    }

    /**
     * returns a new quastion hint list that contains all question hints
     * from the passed list except for the hint that is stored to ordering clipboard
     *
     * @access	private
     * @param	ilAssQuestionHintList	$questionHintList
     * @return	ilAssQuestionHintList	$filteredQuestionHintList
     */
    private function getQuestionHintListWithoutHintStoredInOrderingClipboard(ilAssQuestionHintList $questionHintList): ilAssQuestionHintList
    {
        $filteredQuestionHintList = new ilAssQuestionHintList();

        foreach ($questionHintList as $questionHint) {
            /* @var $questionHint ilAssQuestionHint */

            if ($questionHint->getId() != $this->hintOrderingClipboard->getStored()) {
                $filteredQuestionHintList->addHint($questionHint);
            }
        }

        return $filteredQuestionHintList;
    }

    /**
     * checks for a hint id in the passed array and redirects
     * with corresponding failure message if not exactly one id is given
     *
     * @access	private
     * @global	ilCtrl		$ilCtrl
     * @global	ilLanguage	$lng
     * @param	array		$hintIds
     */
    private function checkForSingleHintIdAndRedirectOnFailure($hintIds): void
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];

        if (!count($hintIds)) {
            $this->main_tpl->setOnScreenMessage('failure', $lng->txt('tst_question_hints_cut_hints_missing_selection_msg'), true);
            $ilCtrl->redirect($this, self::CMD_SHOW_LIST);
        } elseif (count($hintIds) > 1) {
            $this->main_tpl->setOnScreenMessage('failure', $lng->txt('tst_question_hints_cut_hints_single_selection_msg'), true);
            $ilCtrl->redirect($this, self::CMD_SHOW_LIST);
        }
    }

    /**
     * fetches either an array of hint ids from POST or a single hint id from GET
     * and returns an array of (a single) hint id(s) casted to integer in both cases
     *
     * @access	private
     * @static
     * @return	array	$hintIds
     */
    private static function fetchHintIdsParameter(): array
    {
        global $DIC;
        $request = $DIC->testQuestionPool()->internal()->request();
        $hintIds = array();

        if (isset($_POST['hint_ids']) && is_array($_POST['hint_ids'])) {
            foreach ($_POST['hint_ids'] as $hintId) {
                if ((int) $hintId) {
                    $hintIds[] = (int) $hintId;
                }
            }
        } elseif ($request->isset('hint_id') && (int) $request->raw('hint_id')) {
            $hintIds[] = (int) $request->raw('hint_id');
        }

        return $hintIds;
    }

    /**
     * fetches an array of hint index values from POST
     *
     * @access	private
     * @static
     * @return	array	$hintIndexes
     */
    private static function fetchHintIndexesParameter(): array
    {
        $hintIndexes = array();

        if (isset($_POST['hint_indexes']) && is_array($_POST['hint_indexes'])) {
            foreach ($_POST['hint_indexes'] as $hintId => $hintIndex) {
                if ((int) $hintId) {
                    $hintIndexes[(int) $hintId] = $hintIndex;
                }
            }
        }

        return $hintIndexes;
    }

    /**
     * sorts the array of indexes by index value so keys (hint ids)
     * get into new order submitted by user
     *
     * @access	private
     * @static
     * @return	array	$hintIndexes
     */
    private static function orderHintIndexes($hintIndexes): array
    {
        asort($hintIndexes);

        return $hintIndexes;
    }

    public function confirmSyncCmd(): void
    {
        $this->questionGUI->originalSyncForm('showHints');
    }

    /**
     * returns the link target for hint request presentation
     *
     * @param integer $hintId
     * @param boolean $xmlStyle
     * @return string $linkTarget
     */
    public function getHintPresentationLinkTarget($hintId, $xmlStyle = true): string
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];

        if ($this->questionOBJ->isAdditionalContentEditingModePageObject()) {
            $ilCtrl->setParameterByClass('ilasshintpagegui', 'hint_id', $hintId);
            $linkTarget = $ilCtrl->getLinkTargetByClass('ilAssHintPageGUI', '', '', false, $xmlStyle);
        } else {
            $ilCtrl->setParameter($this, 'hintId', $hintId);
            $linkTarget = $ilCtrl->getLinkTarget($this, self::CMD_SHOW_HINT, '', false, $xmlStyle);
        }

        return $linkTarget;
    }

    /**
     * shows an allready requested hint
     *
     * @access	private
     * @global	ilCtrl $ilCtrl
     * @global	ilTemplate $tpl
     * @global	ilLanguage $lng
     */
    private function showHintCmd(): void
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $tpl = $DIC['tpl'];
        $lng = $DIC['lng'];

        if (!$this->request->isset('hintId') || !(int) $this->request->raw('hintId')) {
            throw new ilTestException('no hint id given');
        }

        $DIC->tabs()->clearTargets();
        $DIC->tabs()->clearSubTabs();

        $DIC->tabs()->setBackTarget(
            $DIC->language()->txt('tst_question_hints_back_to_hint_list'),
            $DIC->ctrl()->getLinkTarget($this, self::CMD_SHOW_LIST)
        );

        $questionHint = ilAssQuestionHint::getInstanceById((int) $this->request->raw('hintId'));

        // build form

        $form = new ilPropertyFormGUI();

        $form->setFormAction($ilCtrl->getFormAction($this));

        $form->setTableWidth('100%');

        $form->setTitle(sprintf(
            $lng->txt('tst_question_hints_form_header_edit'),
            $questionHint->getIndex(),
            $this->questionOBJ->getTitle()
        ));

        // form input: hint text

        $nonEditableHintText = new ilNonEditableValueGUI($lng->txt('tst_question_hints_form_label_hint_text'), 'hint_text', true);
        $nonEditableHintText->setValue(ilLegacyFormElementsUtil::prepareTextareaOutput($questionHint->getText(), true));
        $form->addItem($nonEditableHintText);

        // form input: hint points

        $nonEditableHintPoints = new ilNonEditableValueGUI($lng->txt('tst_question_hints_form_label_hint_points'), 'hint_points');
        $nonEditableHintPoints->setValue($questionHint->getPoints());
        $form->addItem($nonEditableHintPoints);

        $tpl->setContent($form->getHTML());
    }
}
