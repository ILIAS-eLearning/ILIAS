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

/**
 * Table GUI for managing list of hints for a question
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/TestQuestionPool
 */
class ilAssQuestionHintsTableGUI extends ilTable2GUI
{
    /**
     * the factor the ordering position value is multiplicated with
     * (so the user gets non decimal gaps for reordering .. e.g. 10, 20, 30 .. not 1, 2, 3)
     */
    public const INDEX_TO_POSITION_FACTOR = 10;

    /**
     * the available table modes controlling the tables behaviour
     */
    public const TBL_MODE_TESTOUTPUT = '1';
    public const TBL_MODE_ADMINISTRATION = '2';

    /**
     * the object instance for current question
     *
     * @access	private
     * @var		assQuestion
     */
    private $questionOBJ = null;

    /**
     * the table mode controlling the tables behaviour
     * (either self::TBL_MODE_TESTOUTPUT or self::TBL_MODE_ADMINISTRATION)
     *
     * @var string
     */
    private $tableMode = null;

    private $hintOrderingClipboard = null;

    /**
     * Constructor
     *
     * @access	public
     * @global	ilCtrl					$ilCtrl
     * @global	ilLanguage				$lng
     * @param	assQuestion				$questionOBJ
     * @param	ilAssQuestionHintList	$questionHintList
     * @param	ilAssQuestionHintsGUI	$parentGUI
     * @param	string					$parentCmd
     */
    public function __construct(
        assQuestion $questionOBJ,
        ilAssQuestionHintList $questionHintList,
        ilAssQuestionHintAbstractGUI $parentGUI,
        $parentCmd,
        $tableMode = self::TBL_MODE_TESTOUTPUT,
        ilAssQuestionHintsOrderingClipboard $hintOrderingClipboard = null
    ) {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];

        $this->questionOBJ = $questionOBJ;
        $this->tableMode = $tableMode;
        $this->hintOrderingClipboard = $hintOrderingClipboard;

        $this->setPrefix('tsthints' . $tableMode);
        $this->setId('tsthints' . $tableMode);

        parent::__construct($parentGUI, $parentCmd);

        $this->setTitle(sprintf($lng->txt('tst_question_hints_table_header'), $questionOBJ->getTitle()));
        $this->setNoEntriesText($lng->txt('tst_question_hints_table_no_items'));

        // we don't take care about offset/limit values, so this avoids segmentation in general
        // --> required for ordering via clipboard feature
        $this->setExternalSegmentation(true);

        $tableData = $questionHintList->getTableData();

        if ($this->questionOBJ->isAdditionalContentEditingModePageObject()) {
            require_once 'Modules/TestQuestionPool/classes/class.ilAssHintPageGUI.php';

            foreach ($tableData as $key => $data) {
                $this->ensurePageObjectExists('qht', $data['hint_id']);
                $pageObjectGUI = new ilAssHintPageGUI($data['hint_id']);
                $pageObjectGUI->setOutputMode("presentation");
                $tableData[$key]['hint_text'] = $pageObjectGUI->presentation();
            }
        }

        $this->setData($tableData);

        if ($this->tableMode == self::TBL_MODE_ADMINISTRATION) {
            $this->setRowTemplate('tpl.tst_question_hints_administration_table_row.html', 'Modules/TestQuestionPool');

            $this->setSelectAllCheckbox('hint_ids[]');

            $rowCount = count($tableData);
            $this->initAdministrationColumns($rowCount);
            $this->initAdministrationCommands($rowCount);
        } else {
            $this->setRowTemplate('tpl.tst_question_hints_testoutput_table_row.html', 'Modules/TestQuestionPool');

            $this->initTestoutputColumns();
            $this->initTestoutputCommands();
        }
    }

    /**
     * inits the required command buttons / multi selection commands
     * for administration table mode
     *
     * @access	private
     * @global	ilCtrl		$ilCtrl
     * @global	ilLanguage	$lng
     * @param	integer		$rowCount
     */
    private function initAdministrationCommands($rowCount): void
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];

        $this->setFormAction($ilCtrl->getFormAction($this->parent_obj));

        if ($this->hintOrderingClipboard->hasStored()) {
            $this->addMultiCommand(
                ilAssQuestionHintsGUI::CMD_PASTE_FROM_ORDERING_CLIPBOARD_BEFORE,
                $lng->txt('tst_questions_hints_table_multicmd_paste_hint_before')
            );

            $this->addMultiCommand(
                ilAssQuestionHintsGUI::CMD_PASTE_FROM_ORDERING_CLIPBOARD_AFTER,
                $lng->txt('tst_questions_hints_table_multicmd_paste_hint_after')
            );
        } elseif ($rowCount > 0) {
            $this->addMultiCommand(
                ilAssQuestionHintsGUI::CMD_CONFIRM_DELETE,
                $lng->txt('tst_questions_hints_table_multicmd_delete_hint')
            );

            if ($rowCount > 1) {
                $this->addMultiCommand(
                    ilAssQuestionHintsGUI::CMD_CUT_TO_ORDERING_CLIPBOARD,
                    $lng->txt('tst_questions_hints_table_multicmd_cut_hint')
                );
            }

            $this->addCommandButton(
                ilAssQuestionHintsGUI::CMD_SAVE_LIST_ORDER,
                $lng->txt('tst_questions_hints_table_cmd_save_order')
            );
        }
    }

    /**
     * inits the required command buttons / multi selection commands
     * for testoutput table mode
     *
     * @access	private
     * @global	ilCtrl		$ilCtrl
     * @global	ilLanguage	$lng
     */
    private function initTestoutputCommands(): void
    {
        if ($this->parent_obj instanceof ilAssQuestionHintsGUI) {
            return;
        }

        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];

        $this->setFormAction($ilCtrl->getFormAction($this->parent_obj));

        $this->addCommandButton(
            ilAssQuestionHintRequestGUI::CMD_BACK_TO_QUESTION,
            $lng->txt('tst_question_hints_back_to_question')
        );
    }

    /**
     * inits the required columns
     * for administration table mode
     *
     * @access	private
     * @global	ilLanguage	$lng
     * @param	integer		$rowCount
     */
    private function initAdministrationColumns($rowCount): void
    {
        global $DIC;
        $lng = $DIC['lng'];

        $this->addColumn('', '', '30', true);

        $this->addColumn($lng->txt('tst_question_hints_table_column_hint_order'), 'hint_index', '60');
        $this->addColumn($lng->txt('tst_question_hints_table_column_hint_text'), 'hint_text');
        $this->addColumn($lng->txt('tst_question_hints_table_column_hint_points'), 'hint_points', '250');

        $this->addColumn('', '', '100');

        $this->setDefaultOrderField("hint_index");
        $this->setDefaultOrderDirection("asc");

        if ($rowCount < 1) {
            $this->disable('header');
        }
    }

    /**
     * inits the required columns
     * for testoutput table mode
     *
     * @access	private
     * @global	ilLanguage	$lng
     */
    private function initTestoutputColumns(): void
    {
        global $DIC;
        $lng = $DIC['lng'];

        $this->addColumn($lng->txt('tst_question_hints_table_column_hint_index'), 'hint_index', '200');
        $this->addColumn($lng->txt('tst_question_hints_table_column_hint_text'), 'hint_text');
        $this->addColumn($lng->txt('tst_question_hints_table_column_hint_points'), 'hint_points', '200');

        $this->setDefaultOrderField("hint_index");
        $this->setDefaultOrderDirection("asc");
    }

    /**
     * returns the fact wether the passed field
     * is to be ordered numerically or not
     * @access	public
     * @param	string $a_field
     * @return	boolean	$numericOrdering
     */
    public function numericOrdering(string $a_field): bool
    {
        switch ($a_field) {
            case 'hint_index':
            case 'hint_points':

                return true;
        }

        return false;
    }

    /**
     * renders a table row by filling wor data to table row template
     * @access	public
     * @param	array		$a_set
     *@global	ilLanguage	$lng
     * @global	ilCtrl		$ilCtrl
     */
    public function fillRow(array $a_set): void
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];

        if ($this->tableMode == self::TBL_MODE_ADMINISTRATION) {
            $list = new ilAdvancedSelectionListGUI();
            $list->setListTitle($lng->txt('actions'));
            $list->setId("advsl_hint_{$a_set['hint_id']}_actions");

            if ($this->questionOBJ->isAdditionalContentEditingModePageObject()) {
                $editPointsHref = $ilCtrl->getLinkTargetByClass('ilAssQuestionHintGUI', ilAssQuestionHintGUI::CMD_SHOW_FORM);
                $editPointsHref = ilUtil::appendUrlParameterString($editPointsHref, "hint_id={$a_set['hint_id']}", true);
                $list->addItem($lng->txt('tst_question_hints_table_link_edit_hint_points'), '', $editPointsHref);

                $editPageHref = $ilCtrl->getLinkTargetByClass('ilasshintpagegui', 'edit');
                $editPageHref = ilUtil::appendUrlParameterString($editPageHref, "hint_id={$a_set['hint_id']}", true);
                $list->addItem($lng->txt('tst_question_hints_table_link_edit_hint_page'), '', $editPageHref);
            } else {
                $editHref = $ilCtrl->getLinkTargetByClass('ilAssQuestionHintGUI', ilAssQuestionHintGUI::CMD_SHOW_FORM);
                $editHref = ilUtil::appendUrlParameterString($editHref, "hint_id={$a_set['hint_id']}", true);
                $list->addItem($lng->txt('tst_question_hints_table_link_edit_hint'), '', $editHref);
            }

            $deleteHref = $ilCtrl->getLinkTarget($this->parent_obj, ilAssQuestionHintsGUI::CMD_CONFIRM_DELETE);
            $deleteHref = ilUtil::appendUrlParameterString($deleteHref, "hint_id={$a_set['hint_id']}", true);
            $list->addItem($lng->txt('tst_question_hints_table_link_delete_hint'), '', $deleteHref);

            $this->tpl->setVariable('ACTIONS', $list->getHTML());

            $this->tpl->setVariable('HINT_ID', $a_set['hint_id']);

            $hintIndex = $a_set['hint_index'] * self::INDEX_TO_POSITION_FACTOR;
        } else {
            $showHref = $this->parent_obj->getHintPresentationLinkTarget($a_set['hint_id']);

            $this->tpl->setVariable('HINT_HREF', $showHref);

            $hintIndex = ilAssQuestionHint::getHintIndexLabel($lng, $a_set['hint_index']);
        }

        $this->tpl->setVariable('HINT_INDEX', $hintIndex);
        $txt = ilLegacyFormElementsUtil::prepareTextareaOutput($a_set['hint_text'], true);
        $this->tpl->setVariable('HINT_TEXT', $txt);
        $this->tpl->setVariable('HINT_POINTS', $a_set['hint_points']);
    }

    protected function ensurePageObjectExists($pageObjectType, $pageObjectId): void
    {
        if (!ilAssHintPage::_exists($pageObjectType, $pageObjectId)) {
            $pageObject = new ilAssHintPage();
            $pageObject->setParentId($this->questionOBJ->getId());
            $pageObject->setId($pageObjectId);
            $pageObject->createFromXML();
        }
    }
}
