<?php 

include_once('Services/Table/classes/class.ilTable2GUI.php');

class ilChatSmiliesTableGUI extends ilTable2GUI {
	public function __construct($a_ref, $title) {
		global $lng, $ilCtrl;

		parent::__construct($a_ref, $title);
		$this->setTitle($lng->txt('chat_available_smilies'));
		
	 	$this->addColumn('', 'checkbox', '1%');
	 	$this->addColumn($lng->txt('chat_smiley_image'), 'image' , '20%');
	 	$this->addColumn($lng->txt('chat_smiley_keyword'), 'keyword' , '30%');
	 	$this->addColumn('', 'edit', '15%');
	 		 	
		$this->setFormAction($ilCtrl->getFormAction($a_ref));
		$this->setRowTemplate('tpl.chat_smiley_list_row.html', 'Modules/Chat');
		$this->setSelectAllCheckbox('smiley_id');
		
		$this->addCommandButton("deleteMultiple", $lng->txt("chat_delete_selected"));
	}
	
	public function fillRow($a_set)
	{		
		global $ilCtrl;
		$this->tpl->setVariable('VAL_SMILEY_ID', $a_set['smiley_id']);
		$this->tpl->setVariable('VAL_SMILEY_PATH', $a_set['smiley_path']);
		$this->tpl->setVariable('VAL_SMILEY_KEYWORDS', $a_set['smiley_keywords']);
		$this->tpl->setVariable('VAL_SMILEY_KEYWORDS_NONL', str_replace("\n", "", $a_set['smiley_keywords']));
		$this->tpl->setVariable('VAL_SORTING_TEXTINPUT', ilUtil::formInput('sorting['.$a_set['id'].']', $a_set['sorting']));

		$ilCtrl->setParameter($this->parent_obj, 'topic_id', $a_set['id']);
		
		$this->tpl->setVariable('EDIT_LINK', $ilCtrl->getLinkTarget($this->parent_obj, 'showEditSmileyEntryForm')."&smiley_id=".$a_set['smiley_id']);
		$this->tpl->setVariable('DELETE_LINK', $ilCtrl->getLinkTarget($this->parent_obj, 'showDeleteSmileyForm')."&smiley_id=".$a_set['smiley_id']);
		$this->tpl->setVariable('TXT_EDIT_RECORD', $this->lng->txt('edit'));
		$this->tpl->setVariable('TXT_DELETE_RECORD', $this->lng->txt('delete'));
	}

}