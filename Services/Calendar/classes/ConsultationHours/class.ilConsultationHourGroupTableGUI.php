<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Calendar/classes/ConsultationHours/class.ilConsultationHourGroup.php';
include_once './Services/Table/classes/class.ilTable2GUI.php';

/**
 * Description of class
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilConsultationHourGroupTableGUI extends ilTable2GUI
{

	private $user_id = 0;
	
	/**
	 * Constructor
	 * @param type $a_parent_obj
	 * @param type $a_parent_cmd
	 * @param type $a_user_id
	 */
	public function __construct($a_parent_obj, $a_parent_cmd, $a_user_id)
	{
		$this->user_id = $a_user_id;
		$this->setId('chgrp_'.$this->user_id);
		parent::__construct($a_parent_obj,$a_parent_cmd);
		
		$this->initTable();
	}
	
	/**
	 * Init table
	 */
	protected function initTable()
	{
		$this->setRowTemplate('tpl.ch_group_row.html','Services/Calendar');
		
		$this->setTitle($GLOBALS['lng']->txt('cal_ch_grps'));
		$this->setFormAction($GLOBALS['ilCtrl']->getFormAction($this->getParentObject(),$this->getParentCmd()));
		
		$this->addColumn($GLOBALS['lng']->txt('title'),'title');
		$this->addColumn($GLOBALS['lng']->txt('cal_ch_assigned_apps'),'apps');
		$this->addColumn($GLOBALS['lng']->txt('cal_ch_max_books'), 'max_books');
		$this->addColumn($GLOBALS['lng']->txt('actions'), '');
		
		$this->enable('sort');
		$this->enable('header');
		$this->enable('num_info');
		
		$this->setDefaultOrderField('title');
	}
	
	/**
	 * Fill row
	 * @param type $a_set
	 */
	public function fillRow($a_set)
	{
		global $ilCtrl;
		
		$this->tpl->setVariable('TITLE',$a_set['title']);
		$this->tpl->setVariable('MAX_BOOKINGS',$a_set['max_books']);
		$this->tpl->setVariable('ASSIGNED',$a_set['assigned']);
		
		include_once './Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php';
		$list = new ilAdvancedSelectionListGUI();
		$list->setId('act_chgrp_'.$this->user_id.'_'.$a_set['id']);
		$list->setListTitle($this->lng->txt('actions'));

		$ilCtrl->setParameter($this->getParentObject(),'grp_id',$a_set['id']);
		$list->addItem(
			$this->lng->txt('edit'),
			'',
			$ilCtrl->getLinkTarget($this->getParentObject(),'editGroup')
		);
		
		// add members
		if($a_set['assigned'])
		{
			$list->addItem(
				$this->lng->txt('cal_ch_assign_participants'),
				'',
				$ilCtrl->getLinkTargetByClass('ilRepositorySearchGUI','')
			);
		}
		
		$list->addItem(
			$this->lng->txt('delete'),
			'',
			$ilCtrl->getLinkTarget($this->getParentObject(),'confirmDeleteGroup')
		);
		
		$this->tpl->setVariable('ACTIONS',$list->getHTML());
	}


	/**
	 * Parse Groups
	 * @param array $groups
	 */
	public function parse(array $groups)
	{
		$rows = array();
		$counter = 0;
		foreach($groups as $group)
		{
			$rows[$counter]['id'] = $group->getGroupId();
			$rows[$counter]['title'] = $group->getTitle();
			$rows[$counter]['max_books'] = $group->getMaxAssignments();
			$rows[$counter]['assigned'] = count(ilConsultationHourAppointments::getAppointmentIdsByGroup(
					$this->user_id,
					$group->getGroupId(),
					null
				)
			);
			++$counter;
		}
		$this->setData($rows);
	}
}
?>
