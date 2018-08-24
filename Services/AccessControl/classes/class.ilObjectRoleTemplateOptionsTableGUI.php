<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once ('./Services/Table/classes/class.ilTable2GUI.php');
include_once './Services/AccessControl/classes/class.ilPermissionGUI.php';

/**
* Table for object role permissions
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
*
* @ingroup ServicesAccessControl
*/
class ilObjectRoleTemplateOptionsTableGUI extends ilTable2GUI
{
	private $role_id = null;
	private $obj_ref_id = null;

	private $show_admin_permissions = true;

	/**
	 * Constructor
	 * @return 
	 */
	public function __construct($a_parent_obj,$a_parent_cmd, $a_obj_ref_id,$a_role_id,$a_show_admin_permissions = false)
	{
		global $ilCtrl,$rbacreview,$tpl;

		$this->tpl_type = $a_type;
		$this->show_admin_permissions = $a_show_admin_permissions;

		parent::__construct($a_parent_obj,$a_parent_cmd);

		$this->setId('role_options_'.$a_ref_id.'_'.$a_role_id);
		
		$this->lng->loadLanguageModule('rbac');
		
		$this->role_id = $a_role_id;
		$this->obj_ref_id = $a_obj_ref_id;
		

		$this->setRowTemplate("tpl.obj_role_template_options_row.html", "Services/AccessControl");
		$this->setLimit(100);
		$this->setShowRowsSelector(false);
		$this->setDisableFilterHiding(true);
		
		$this->setEnableHeader(false);
		$this->disable('sort');
		$this->disable('numinfo');
		$this->disable('form');
		
		$this->addColumn('','','0');
		$this->addColumn('','','100%');
		
		$this->setTopCommands(false);
	}
	
	
	/**
	 * Get role folder of current object
	 * @return 
	 */
	public function getObjectRefId()
	{
		return $this->obj_ref_id;
	}
	
	/**
	 * Get currrent role id
	 * @return 
	 */
	public function getRoleId()
	{
		return $this->role_id;
	}
	
	
	/**
	 * Fill row template
	 * @return 
	 */
	public function fillRow($row)
	{
		global $rbacreview;
		
		if(isset($row['recursive']) and !$this->show_admin_permissions)
		{
			$this->tpl->setCurrentBlock('recursive');
			$this->tpl->setVariable('TXT_RECURSIVE',$this->lng->txt('change_existing_objects'));
			$this->tpl->setVariable('DESC_RECURSIVE',$this->lng->txt('change_existing_objects_desc'));
			return true;
		}
		elseif($row['protected'])
		{
			$this->tpl->setCurrentBlock('protected');
			
			if(!$rbacreview->isAssignable($this->getRoleId(), $this->getObjectRefId()))
			{
				$this->tpl->setVariable('DISABLED_PROTECTED','disabled="disabled"');
			}

			if($rbacreview->isProtected($this->getObjectRefId(), $this->getRoleId()))
			{
				$this->tpl->setVariable('PROTECTED_CHECKED','checked="checked"');
			}

			$this->tpl->setVariable('TXT_PROTECTED',$this->lng->txt('role_protect_permissions'));
			$this->tpl->setVariable('DESC_PROTECTED',$this->lng->txt('role_protect_permissions_desc'));
			$this->tpl->parseCurrentBlock();
			return true;
		}
	}
	
	/**
	 * Parse permissions
	 * @return 
	 */
	public function parse()
	{
		global $rbacreview, $objDefinition;
		
		$row[0]['recursive'] = 1;
		$row[1]['protected'] = 1;
		
		$this->setData($row);
		
	}
}
?>