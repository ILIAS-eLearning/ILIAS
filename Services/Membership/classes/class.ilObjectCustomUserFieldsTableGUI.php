<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Table/classes/class.ilTable2GUI.php';

/**
 * @classDescription Table presentation of course/group relevant user data fields
 * 
 * @author Stefan Meyer <meyer@leifos.com>
 * @Id: $Id$
 */
class ilObjectCustomUserFieldsTableGUI extends ilTable2GUI
{
	/**
	 * Constructor
	 */
	public function __construct($a_parent_obj, $a_parent_cmd)
	{
		global $ilCtrl,$lng;
		
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
		
		parent::__construct($a_parent_obj,$a_parent_cmd);
		
		$this->setFormAction($this->ctrl->getFormAction($this->getParentObject(),$this->getParentCmd()));
		
		$this->setTitle($this->lng->txt(ilObject::_lookupType($this->getParentObject()->getObjId()).'_custom_user_fields'));
		
		$this->addColumn('','',1);
		$this->addColumn($this->lng->txt('ps_cdf_name'),'name','30%');
		$this->addColumn($this->lng->txt('ps_cdf_type'),'type','30%');
		$this->addColumn($this->lng->txt('ps_cdf_required'),'','20%');
		$this->addColumn('','','20%');
		
		$this->setDefaultOrderField('name');
		$this->setDefaultOrderDirection('asc');
		
		$this->addMultiCommand('confirmDeleteFields', $this->lng->txt('delete'));
		$this->addCommandButton('saveFields', $this->lng->txt('save'));

		$this->setSelectAllCheckbox('field_ids[]');

		$this->enable('sort');
		$this->enable('header');
		$this->enable('numinfo');
		$this->enable('select_all');
		
		$this->setRowTemplate('tpl.mem_cust_user_data_table_row.html','Services/Membership');
	}
	
	/**
	 * Fill row
	 * @param array $row
	 * @return 
	 */
	public function fillRow($row)
	{
		$this->tpl->setVariable('VAL_ID',$row['field_id']);
		$this->tpl->setVariable('VAL_NAME',$row['name']);
		$this->tpl->setVariable('VAL_TYPE',$row['type']);
		$this->tpl->setVariable('REQUIRED_CHECKED',$row['required'] ? 'checked="checked"' : '');
		
		$this->ctrl->setParameter($this->getParentObject(),'field_id',$row['field_id']);
		$this->tpl->setVariable('EDIT_LINK',$this->ctrl->getLinkTarget($this->getParentObject(),'editField'));
		$this->tpl->setVariable('TXT_EDIT',$this->lng->txt('edit'));
	}
	
	/**
	 * Parse table data
	 * @param object ilCourseDefinedFieldDefinition
	 * @return 
	 */
	public function parse($a_defs)
	{
		$rows = array();
		foreach($a_defs as $def)
		{
			$rows[$def->getId()]['field_id'] = $def->getId(); 
			$rows[$def->getId()]['name'] = $def->getName();
			
			switch($def->getType())
			{
				case IL_CDF_TYPE_SELECT:
					$rows[$def->getId()]['type'] = $this->lng->txt('ps_type_select');
					break;

				case IL_CDF_TYPE_TEXT:
					$rows[$def->getId()]['type'] = $this->lng->txt('ps_type_text');
					break;
			}
			
			$rows[$def->getId()]['required'] = (bool) $def->isRequired();
		}
		$this->setData($rows);
		if(!sizeof($rows))
		{
			$this->clearCommandButtons();
		}
	}
}
?>