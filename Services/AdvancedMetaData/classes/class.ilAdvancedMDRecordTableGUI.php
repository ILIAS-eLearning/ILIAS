<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/** 
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* 
* @ingroup ServicesAdvancedMetaData
*/
include_once('Services/Table/classes/class.ilTable2GUI.php');
include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php');

class ilAdvancedMDRecordTableGUI extends ilTable2GUI
{
	protected $lng = null;
	protected $ctrl;
	protected $permissions; // [ilAdvancedMDPermissionHelper]
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function __construct($a_parent_obj,$a_parent_cmd = '', ilAdvancedMDPermissionHelper $a_permissions, $a_in_object_context = false)
	{
	 	global $lng,$ilCtrl;
	 	
	 	$this->lng = $lng;
	 	$this->ctrl = $ilCtrl;
		$this->permissions = $a_permissions;
	 	
	 	parent::__construct($a_parent_obj,$a_parent_cmd);
	 	$this->addColumn('','',1);
	 	$this->addColumn($this->lng->txt('title'),'title');
	 	$this->addColumn($this->lng->txt('md_fields'),'fields');
	 	$this->addColumn($this->lng->txt('md_adv_active'),'active');
		$this->addColumn($this->lng->txt('md_obj_types'),'obj_types');		
		
	 	$this->addColumn($this->lng->txt('actions'));
	 	
		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.show_records_row.html","Services/AdvancedMetaData");
		$this->setDefaultOrderField("title");
		$this->setDefaultOrderDirection("desc");
	}
	
	/**
	 * Fill row
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function fillRow($a_set)
	{				
		// assigned object types
		$disabled = !$a_set["perm"][ilAdvancedMDPermissionHelper::ACTION_RECORD_EDIT_PROPERTY][ilAdvancedMDPermissionHelper::SUBACTION_RECORD_OBJECT_TYPES];			
		$options = array(
			0 => $this->lng->txt("meta_obj_type_inactive"),
			1 => $this->lng->txt("meta_obj_type_mandatory"),
			2 => $this->lng->txt("meta_obj_type_optional")
		);				
		foreach(ilAdvancedMDRecord::_getAssignableObjectTypes(true) as $obj_type)
		{
			$this->tpl->setCurrentBlock('ass_obj_types');
			$this->tpl->setVariable('VAL_OBJ_TYPE', $obj_type["text"]);
			
			$value = 0;
			foreach ($a_set['obj_types'] as $t)
			{
				if ($obj_type["obj_type"] == $t["obj_type"] && 
					$obj_type["sub_type"] == $t["sub_type"])
				{
					$value = $t["optional"]
						? 2
						: 1;				
					break;
				}				
			}
				
			if(!$a_set["readonly"] && !$a_set["local"])
			{				
				$select = ilUtil::formSelect($value, "obj_types[".$a_set['id']."][".$obj_type["obj_type"].":".$obj_type["sub_type"]."]", $options, false, true, 0, "", "", $disabled);			
				$this->tpl->setVariable('VAL_OBJ_TYPE_STATUS', $select);
			}
			else if(!$value)
			{
				continue;
			}

			$this->tpl->parseCurrentBlock();
		}
		
		if($a_set["readonly"] && !$a_set["local"])
		{
			$a_set['description'] .= ' <span class="il_ItemAlertProperty">'.$this->lng->txt("meta_global").'</span>';
		}
		
		$this->tpl->setVariable('VAL_ID',$a_set['id']);
		$this->tpl->setVariable('VAL_TITLE',$a_set['title']);
		if(strlen($a_set['description']))
		{
			$this->tpl->setVariable('VAL_DESCRIPTION',$a_set['description']);
		}
		$defs = ilAdvancedMDFieldDefinition::getInstancesByRecordId($a_set['id']);
		if(!count($defs))
		{
			$this->tpl->setVariable('TXT_FIELDS',$this->lng->txt('md_adv_no_fields'));
		}
		foreach($defs as $definition_obj)
		{
			$this->tpl->setCurrentBlock('field_entry');
			$this->tpl->setVariable('FIELD_NAME',$definition_obj->getTitle().
				": ".$this->lng->txt($definition_obj->getTypeTitle()));
			$this->tpl->parseCurrentBlock();
		}
		
		$this->tpl->setVariable('ACTIVE_CHECKED',$a_set['active'] ? ' checked="checked" ' : '');
		$this->tpl->setVariable('ACTIVE_ID',$a_set['id']);
		
		if(($a_set["readonly"] && !$a_set["optional"]) ||
			!$a_set["perm"][ilAdvancedMDPermissionHelper::ACTION_RECORD_TOGGLE_ACTIVATION])
		{			
			$this->tpl->setVariable('ACTIVE_DISABLED','disabled="disabled"');			
		}

		if(!$a_set["readonly"])
		{
			$this->ctrl->setParameter($this->parent_obj,'record_id',$a_set['id']);

			if ($a_set["perm"][ilAdvancedMDPermissionHelper::ACTION_RECORD_EDIT])
			{			
				$this->tpl->setVariable('EDIT_LINK',$this->ctrl->getLinkTarget($this->parent_obj,'editRecord'));
				$this->tpl->setVariable('TXT_EDIT_RECORD',$this->lng->txt('edit'));
			}
			if ($a_set["perm"][ilAdvancedMDPermissionHelper::ACTION_RECORD_EDIT_FIELDS])
			{
				$this->tpl->setVariable('EDIT_FIELDS_LINK',$this->ctrl->getLinkTarget($this->parent_obj,'editFields'));
				$this->tpl->setVariable('TXT_EDIT_FIELDS',$this->lng->txt('md_adv_field_table'));
			}
		}
	}
} 


?>