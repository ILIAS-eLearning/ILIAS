<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Table/classes/class.ilTable2GUI.php';
require_once 'Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php';

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
	 * the object instance for current question
	 * 
	 * @access	private
	 * @var		assQuestion
	 */
	private $questionOBJ = null;
	
	/**
	 * hint clipboard for ordering operations
	 * 
	 * @access	private
	 * @var		ilAssQuestionHintOrderingClipboard
	 */
	private $hintOrderingClipboard = null;
	
	/**
	 * Constructor
	 * 
	 * @access	public
	 * @global	ilLanguage				$lng
	 * @param	assQuestion				$questionOBJ
	 * @param	ilAssQuestionHintsGUI	$parentGUI
	 * @param	string					$parentCmd 
	 */
	public function __construct(assQuestion $questionOBJ, ilAssQuestionHintsOrderingClipboard $hintOrderingClipboard,
			ilAssQuestionHintsGUI $parentGUI, $parentCmd)
	{
		global $ilCtrl, $lng;
		
		$this->questionOBJ = $questionOBJ;
		$this->hintOrderingClipboard = $hintOrderingClipboard;
		
		$this->setPrefix('tst_question_hints');
		$this->setId('tst_question_hints');
		
		$this->setSelectAllCheckbox('hint_ids[]');
		
		parent::__construct($parentGUI, $parentCmd);
		
		$this->setTitle( sprintf($lng->txt('tst_question_hints_table_header'), $questionOBJ->getTitle()) );
		$this->setNoEntriesText( $lng->txt('tst_question_hints_table_no_items') );
		
		$this->setRowTemplate('tpl.tst_question_hints_table_row.html', 'Modules/TestQuestionPool');
		
		$this->initCommands();
		$this->initColumns();
		
		// this avoids segmentation, because we do not
		// provide limit/offset values to this table
		$this->setExternalSegmentation(true);
	}
	
	/**
	 * inits the required command buttons / multi selection commands
	 *
	 * @access	private
	 * @global	ilCtrl		$ilCtrl
	 * @global	ilLanguage	$lng
	 */
	private function initCommands()
	{
		global $ilCtrl, $lng;
		
		$this->setFormAction( $ilCtrl->getFormAction($this->parent_obj) );
		
		if( $this->hintOrderingClipboard->hasStored() )
		{
			$this->addMultiCommand(
					ilAssQuestionHintsGUI::CMD_PASTE_FROM_ORDERING_CLIPBOARD_BEFORE,
					$lng->txt('tst_questions_hints_table_multicmd_paste_hint_before')
			);
			
			$this->addMultiCommand(
					ilAssQuestionHintsGUI::CMD_PASTE_FROM_ORDERING_CLIPBOARD_AFTER,
					$lng->txt('tst_questions_hints_table_multicmd_paste_hint_after')
			);
		}
		else
		{
			$this->addMultiCommand(
					ilAssQuestionHintsGUI::CMD_CONFIRM_DELETE,
					$lng->txt('tst_questions_hints_table_multicmd_delete_hint')
			);
			
			$this->addMultiCommand(
					ilAssQuestionHintsGUI::CMD_CUT_TO_ORDERING_CLIPBOARD,
					$lng->txt('tst_questions_hints_table_multicmd_cut_hint')
			);

			$this->addCommandButton(
					ilAssQuestionHintsGUI::CMD_SAVE_LIST_ORDER,
					$lng->txt('tst_questions_hints_table_cmd_save_order')
			);
		}
	}
	
	/**
	 * inits the required columns
	 * 
	 * @access	private
	 * @global	ilLanguage	$lng
	 */
	private function initColumns()
	{
		global $lng;
		
		$this->addColumn( '', '', '30', true );
		
		$this->addColumn( $lng->txt('tst_question_hints_table_column_hint_order'), 'hint_index', '60');
		$this->addColumn( $lng->txt('tst_question_hints_table_column_hint_text'), 'hint_text');
		$this->addColumn( $lng->txt('tst_question_hints_table_column_hint_points'), 'hint_points', '250');
		
		$this->addColumn('', '', '100');
		
		$this->setDefaultOrderField("hint_index");
		$this->setDefaultOrderDirection("asc");
	}
	
	/**
	 * returns the fact wether the passed field
	 * is to be ordered numerically or not
	 *
	 * @access	public
	 * @param	string	$field
	 * @return	boolean	$numericOrdering
	 */
	public function numericOrdering($field)
	{
		switch($field)
		{
			case 'hint_index':
			case 'hint_points':
				
				return true;
		}
		
		return false;
	}
	
	/**
	 * renders a table row by filling wor data to table row template
	 * 
	 * @access	public
	 * @global	ilCtrl		$ilCtrl
	 * @global	ilLanguage	$lng
	 * @param	array		$rowData
	 */
	public function fillRow($rowData)
	{
		global $ilCtrl, $lng;
		
		$editHref = $ilCtrl->getLinkTargetByClass('ilAssQuestionHintGUI', ilAssQuestionHintGUI::CMD_SHOW_FORM);
		$editHref = ilUtil::appendUrlParameterString($editHref, "hint_id={$rowData['hint_id']}", true);
		
		$deleteHref = $ilCtrl->getLinkTarget($this->parent_obj, ilAssQuestionHintsGUI::CMD_CONFIRM_DELETE);
		$deleteHref = ilUtil::appendUrlParameterString($deleteHref, "hint_id={$rowData['hint_id']}", true);
		
		$list = new ilAdvancedSelectionListGUI();
		$list->setListTitle($lng->txt('actions'));
		$list->setId("advsl_hint_{$rowData['hint_id']}_actions");
		
		$list->addItem($lng->txt('tst_question_hints_table_link_edit_hint'), '', $editHref);
		$list->addItem($lng->txt('tst_question_hints_table_link_delete_hint'), '', $deleteHref);
		
		$this->tpl->setVariable('HINT_ID', $rowData['hint_id']);
		$this->tpl->setVariable('HINT_INDEX', $rowData['hint_index'] * 10);
		$this->tpl->setVariable('HINT_TEXT', $rowData['hint_text']);
		$this->tpl->setVariable('HINT_POINTS', $rowData['hint_points']);
		$this->tpl->setVariable('ACTIONS', $list->getHTML());
	}
}

