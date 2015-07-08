<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Table/classes/class.ilTable2GUI.php';

/**
 * Table GUI for system check task overview
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilSCTaskTableGUI extends ilTable2GUI
{

	private $group_id = 0;
	
	/**
	 * Constructor
	 * @param type $a_parent_obj
	 * @param type $a_parent_cmd
	 */
	public function __construct($a_group_id, $a_parent_obj, $a_parent_cmd = "")
	{
		$this->group_id = $a_group_id;
		$this->setId('sc_groups');
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
	}
	
	/**
	 * Get group id
	 * @return type
	 */
	public function getGroupId()
	{
		return $this->group_id;
	}
	
	/**
	 * init table 
	 */
	public function init()
	{
		global $ilCtrl, $lng;

		$lng->loadLanguageModule('sysc');
		$this->addColumn($this->lng->txt('title'),'title','60%');
		$this->addColumn($this->lng->txt('last_update'),'last_update_sort','20%');
		$this->addColumn($this->lng->txt('status'),'status','10%');
		$this->addColumn($this->lng->txt('actions'),'','10%');

		$this->setTitle($this->lng->txt('sysc_task_overview'));

		$this->setRowTemplate('tpl.syscheck_tasks_row.html','Services/SystemCheck');
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
		
		$list->addItem(
				$this->lng->txt('show'),
				'',
				$GLOBALS['ilCtrl']->getLinkTarget($this->getParentObject(),'showGroup')
		);
		$this->tpl->setVariable('ACTIONS',$list->getHTML());
	}


	/**
	 * Parse system check groups
	 */
	public function parse()
	{
		$data = array();
		include_once './Services/SystemCheck/classes/class.ilSCTasks.php';
		foreach(ilSCTasks::getInstanceByGroupId($this->getGroupId())->getTasks() as $task)
		{
			$item = array();
			$item['id'] = $task->getId();
			$item['title'] = $GLOBALS['lng']->txt($task->getTitle());
			$item['description'] = $GLOBALS['lng']->txt($task->getDescription());
			$item['last_update'] = ilDatePresentation::formatDate($task->getLastUpdate());
			$item['last_update_sort'] = $task->getLastUpdate()->get(IL_CAL_UNIX);
			$item['status'] = $task->getStatus();
			
			$data[] = $item;
		}
		
		$this->setData($data);
	}
}
?>
