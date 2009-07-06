<?php 

include_once('Services/Table/classes/class.ilTable2GUI.php');
include_once("./Services/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");

class ilChatRoomsTableGUI extends ilTable2GUI {
	
	private $objectRef;
	private $hasWritePerm = false;
	private $serverActive = false;
	
	public function __construct($a_ref, $hasWritePerm = false, $active, $title="") {
		global $lng, $ilCtrl, $rbacsystem, $ilSetting;
		
		$this->serverActive = $active;
		$this->objectRef = $a_ref;
		$this->hasWritePerm = $hasWritePerm;
		parent::__construct($a_ref, $title);
		$this->setTitle($lng->txt('chat_rooms'));
		
	 	$this->addColumn('', 'checkbox', '1%', true);
	 	$this->addColumn($lng->txt('chat_rooms'), 'room' , '79%');
	 	$this->addColumn($lng->txt("actions"), '' , '20%');

		$this->setFormAction($ilCtrl->getFormAction($a_ref));
		$this->setRowTemplate('tpl.chat_room_list_row.html', 'Modules/Chat');
		$this->setSelectAllCheckbox('del_id');
		
		if
		(
			$ilSetting->get('chat_export_status') == 0 ||
			(
				$ilSetting->get('chat_export_status') == 1 &&
				$rbacsystem->checkAccess("moderate", $this->objectRef->ref_id)
			)
		)
		{
			$this->addMultiCommand("exportRoom", $lng->txt("chat_html_export"));
		}
		
		if ($hasWritePerm)
		{
			$this->addMultiCommand("refreshRoom", $lng->txt("chat_refresh"));
		}

		$this->addMultiCommand("deleteRoom", $lng->txt("delete"));
	}
	
	public function fillRow($a_set)
	{		
		global $ilCtrl, $lng, $ilSetting, $rbacsystem;
		
		$this->tpl->setVariable('VAL_ROOM_ID', $a_set['room_id']);
		$this->tpl->setVariable('VAL_ROOM_TITLE', $a_set['title']);
		$this->tpl->setVariable('VAL_TXT_USERS', $this->lng->txt('chat_users_active'));
		$this->tpl->setVariable('VAL_USERS', $a_set['usercount']);

		$current_selection_list = new ilAdvancedSelectionListGUI();
		$current_selection_list->setListTitle($lng->txt("actions"));
		$current_selection_list->setId("act_".$a_set['room_id']);
		
		if ($this->serverActive)
		{
			$current_selection_list->addItem($this->lng->txt("show"), '', './ilias.php?baseClass=ilChatPresentationGUI&ref_id='.$this->objectRef->ref_id.'&room_id='.$a_set['room_id'], '', '', 'CHAT');
			
			//$this->tpl->setVariable('VAL_TXT_SHOW', $lng->txt('show'));
			$this->tpl->setVariable('VAL_LINK_SHOW', './ilias.php?baseClass=ilChatPresentationGUI&ref_id='.$this->objectRef->ref_id.'&room_id='.$a_set['room_id']);
			$this->tpl->setVariable('VAL_SHOW_TARGET', 'CHAT');
		}
		
		if ($this->hasWritePerm && $a_set['room_id'])
		{
			$ilCtrl->setParameter($this->objectRef, 'room_id', $a_set['room_id']);
			$current_selection_list->addItem($this->lng->txt("rename"), '', $ilCtrl->getLinkTarget($this->objectRef, 'rename'));
			$ilCtrl->clearParameters($this->objectRef);
			
			$ilCtrl->setParameter($this->objectRef, 'del_id', $a_set['room_id']);
			$current_selection_list->addItem($this->lng->txt("delete"), '', $ilCtrl->getLinkTarget($this->objectRef, 'deleteRoom'));
			$current_selection_list->addItem($this->lng->txt("chat_refresh"), '', $ilCtrl->getLinkTarget($this->objectRef, 'refreshRoom'));
			$ilCtrl->clearParameters($this->objectRef);
		}
		
		if
		(
			$ilSetting->get('chat_export_status') == 0 ||
			(
				$ilSetting->get('chat_export_status') == 1 &&
				$rbacsystem->checkAccess("moderate", $this->objectRef->ref_id)
			)
		)
		{
			$ilCtrl->setParameter($this->objectRef, 'del_id', $a_set['room_id']);
			$current_selection_list->addItem($this->lng->txt("chat_html_export"), '', $ilCtrl->getLinkTarget($this->objectRef, 'exportRoom'));
			$ilCtrl->clearParameters($this->objectRef);
		}
		
		$this->tpl->setVariable('ACTION_LIST', $current_selection_list->getHTML());
	}

}