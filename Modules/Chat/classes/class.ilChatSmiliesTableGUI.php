<?php 

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('Services/Table/classes/class.ilTable2GUI.php');
include_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");

class ilChatSmiliesTableGUI extends ilTable2GUI {
	public function __construct($a_ref, $title) {
		global $lng, $ilCtrl;

		parent::__construct($a_ref, $title);
		$this->setTitle($lng->txt('chat_available_smilies'));

		$this->setId('chat_smilies_tbl');
		
	 	$this->addColumn('', 'checkbox', '2%', true);
	 	$this->addColumn($lng->txt('chat_smiley_image'), '' , '28%');
	 	$this->addColumn($lng->txt('chat_smiley_keyword'), 'keyword' , '55%');
	 	$this->addColumn($lng->txt('actions'), '', '15%');

		$this->setFormAction($ilCtrl->getFormAction($a_ref));
		$this->setRowTemplate('tpl.chat_smiley_list_row.html', 'Modules/Chat');
		$this->setSelectAllCheckbox('smiley_id');
		
		$this->addMultiCommand("deleteMultiple", $lng->txt("chat_delete_selected"));
	}
	
	public function fillRow($a_set)
	{		
		global $ilCtrl;
		$this->tpl->setVariable('VAL_SMILEY_ID', $a_set['smiley_id']);
		$this->tpl->setVariable('VAL_SMILEY_PATH', $a_set['smiley_fullpath']);
		$this->tpl->setVariable('VAL_SMILEY_KEYWORDS', $a_set['smiley_keywords']);
		$this->tpl->setVariable('VAL_SMILEY_KEYWORDS_NONL', str_replace("\n", "", $a_set['smiley_keywords']));
		$this->tpl->setVariable('VAL_SORTING_TEXTINPUT', ilUtil::formInput('sorting['.$a_set['id'].']', $a_set['sorting']));

		$ilCtrl->setParameter($this->parent_obj, 'topic_id', $a_set['id']);

		$current_selection_list = new ilAdvancedSelectionListGUI();
		$current_selection_list->setListTitle($this->lng->txt("actions"));
		$current_selection_list->setId("act_".$a_set['smiley_id']);		

		$current_selection_list->addItem($this->lng->txt("edit"), '', $ilCtrl->getLinkTarget($this->parent_obj, 'showEditSmileyEntryForm')."&smiley_id=".$a_set['smiley_id']);
		$current_selection_list->addItem($this->lng->txt("delete"), '', $ilCtrl->getLinkTarget($this->parent_obj, 'showDeleteSmileyForm')."&smiley_id=".$a_set['smiley_id']);

		$this->tpl->setVariable('VAL_ACTIONS', $current_selection_list->getHTML());
		
	}

}
