<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Table/classes/class.ilTable2GUI.php';

/**
* Table presentation of conditions
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*/
class ilConditionHandlerTableGUI extends ilTable2GUI
{

	/**
	 * Constructor
	 * @param ilObjectGUI $a_parent_obj
	 * @param string $a_parent_cmd
	 */
	public function __construct($a_parent_obj,$a_parent_cmd)
	{
		parent::__construct($a_parent_obj,$a_parent_cmd);

		$this->initTable();
	}

	/**
	 * Fill row template
	 * @param array $a_row
	 */
	public function fillRow($a_row)
	{
		global $ilCtrl;

		$this->tpl->setVariable('OBJ_SRC', $a_row['icon']);
		$this->tpl->setVariable('OBJ_ALT', $a_row['icon_alt']);
		$this->tpl->setVariable('OBJ_TITLE',$a_row['title']);
		include_once './classes/class.ilLink.php';
		$this->tpl->setVariable('OBJ_LINK', ilLink::_getLink($a_row['ref_id'], $a_row['type']));
		$this->tpl->setVariable('OBJ_DESCRIPTION', $a_row['description']);
		$this->tpl->setVariable('COND_ID', $a_row['id']);
		$this->tpl->setVariable('OBJ_CONDITION', $a_row['condition']);

		$this->tpl->setVariable('OBL_SRC', ilUtil::getImagePath($a_row['obligatory'] ? 'icon_ok.gif' : 'icon_not_ok.gif'));
		$this->tpl->setVariable(
			'OBL_ALT',
			$this->lng->txt($a_row['obligatory'] ?
				'precondition_obligatory_alt' :
				'precondition_not_obligatory_alt')
		);
		$ilCtrl->setParameterByClass(get_class($this->getParentObject()),'condition_id',$a_row['id']);
		$this->tpl->setVariable('EDIT_LINK',$ilCtrl->getLinkTargetByClass(get_class($this->getParentObject()),'edit'));
		$this->tpl->setVariable('TXT_EDIT', $this->lng->txt('edit'));

	}

	/**
	 * Set and parse conditions
	 * @param array $a_conditions
	 */
	public function setConditions($a_conditions)
	{
		foreach((array) $a_conditions as $condition)
		{
			$row['id'] = $condition['condition_id'];
			$row['ref_id'] = $condition['trigger_ref_id'];
			$row['type'] = $condition['trigger_type'];
			$row['title'] = ilObject::_lookupTitle($condition['trigger_obj_id']);
			$row['description'] = ilObject::_lookupDescription($condition['trigger_obj_id']);
			$row['icon'] = ilUtil::getImagePath('icon_'.$condition['trigger_type'].'_s.gif');
			$row['icon_alt'] = $this->lng->txt('obj_'.$condition['trigger_type']);
			$row['condition'] = $this->lng->txt('condition_'.$condition['operator']);
			$row['obligatory'] = $condition['obligatory'];
			
			$rows[] = $row;
		}
		$this->setData($rows);
	}

	/**
	 * Init Table
	 * @global ilCtrl
	 */
	protected function initTable()
	{
		global $ilCtrl;

		$this->lng->loadLanguageModule('rbac');

		$this->setRowTemplate('tpl.condition_handler_row.html', 'Services/AccessControl');

		$this->setTitle($this->lng->txt('active_preconditions'));

		$this->addColumn('','','1');
		$this->addColumn($this->lng->txt('title'),'title','66%');
		$this->addColumn($this->lng->txt('condition'), 'condition');
		$this->addColumn($this->lng->txt('precondition_obligatory'),'obligatory');
		$this->addColumn($this->lng->txt('actions'));

		$this->enable('select_all');
		$this->setSelectAllCheckbox('conditions');

		$this->setFormAction($ilCtrl->getFormAction($this->getParentObject(),$this->getParentCmd()));
		$this->addMultiCommand('delete', $this->lng->txt('delete'));

	}

	
}
?>