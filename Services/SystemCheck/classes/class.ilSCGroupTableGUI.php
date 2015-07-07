<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Table/classes/class.ilTable2GUI.php';

/**
 * Table GUI for system check groups overview
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilSCGroupTableGUI extends ilTable2GUI
{

	/**
	 * Constructor
	 * @param type $a_parent_obj
	 * @param type $a_parent_cmd
	 */
	public function __construct($a_parent_obj, $a_parent_cmd = "")
	{
		$this->setId('sc_groups');
		parent::__construct($a_parent_obj, $a_parent_cmd);
	}
	
	/**
	 * init table 
	 */
	public function init()
	{
		global $ilCtrl, $lng;

		$lng->loadLanguageModule('syscheck');
		$this->addColumn($this->lng->txt('title'),'title','60%');
		$this->addColumn($this->lng->txt('last_update'),'last_update_sort','20%');
		$this->addColumn($this->lng->txt('status'),'status','10%');
		$this->addColumn($this->lng->txt('actions'),'','10%');

		$this->setTitle($this->lng->txt('syscheck_overview'));

		$this->setRowTemplate('tpl.syscheck_groups_row.html','Services/SystemCheck');
		$this->setFormAction($ilCtrl->getFormAction($this->getParentObject()));
	}

	/**
	 * Fill row
	 * @param type $a_set
	 */
	public function fillRow($row)
	{
		$this->tpl->setVariable('VAL_TITLE',$row['title']);
		$this->tpl->setVariable('VAL_DESC',$row['description']);
		$this->tpl->setVariable('VAL_LAST_UPDATE',$row['last_update']);
		$this->tpl->setVariable('VAL_STATUS',$row['status']);
		
		// Actions
		include_once './Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php';
		$list = new ilAdvancedSelectionListGUI();
		$list->setSelectionHeaderClass('small');
		$list->setItemLinkClass('small');
		$list->setId('sysc_'.$row['id']);
		$list->setListTitle($this->lng->txt('actions'));
		
		$list->addItem($a_title);

		

	}


	/**
	 * Parse system check groups
	 */
	public function parse()
	{
		$data = array();
		include_once './Services/SystemCheck/classes/class.ilSCGroups.php';
		foreach(ilSCGroups::getInstance()->getGroups() as $group)
		{
			$item = array();
			$item['id'] = $group->getId();
			$item['title'] = $GLOBALS['lng']->txt($group->getTitle());
			$item['description'] = $GLOBALS['lng']->txt($group->getDescription());
			$item['last_update'] = ilDatePresentation::formatDate($group->getLastUpdate());
			$item['last_update_sort'] = $group->getLastUpdate()->get(IL_CAL_UNIX);
			$item['status'] = $group->getStatus();
			
			$data[] = $item;
		}
		
		$this->setData($data);
	}
	
	
}
?>
