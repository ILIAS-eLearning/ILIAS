<?php
include_once('./Services/Table/classes/class.ilTable2GUI.php');
/**
 * Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE
 * Date: 07.01.15
 * Time: 11:43
 */
/**
* Copy Permission Settings
*
* @author Fabian Wolf <wolf@leifos.com>
* @version $Id$
*
* @ingroup ServiceAccessControl
*/
class ilRoleAdoptPermissionTableGUI extends ilTable2GUI
{
	
	function __construct($a_parent_obj, $a_parent_cmd)
	{
		global $ilCtrl, $lng;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->setId("adopt_permission_".$a_parent_obj->obj_id);
		$this->addColumn("");
		$this->addColumn($lng->txt("title"), "title", "70%");
		$this->addColumn($lng->txt("type"), "type", "30%");
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.obj_role_adopt_permission_row.html",
		"Services/AccessControl");
		$this->addCommandButton("perm", $lng->txt("cancel"));
		$this->addMultiCommand("adoptPermSave", $lng->txt("save"));

		$this->setLimit(9999);
	}
	
	/**
	* Fill a single data row.
	*/
	protected function fillRow($a_set)
	{
		global $lng, $ilCtrl;
		$this->tpl->setVariable("PARAM", "adopt");
		$this->tpl->setVariable("VAL_ID", $a_set["role_id"]);
		$this->tpl->setVariable("VAL_TITLE", $a_set["role_name"]);
		if(strlen($a_set["role_desc"]))
		{
			$this->tpl->setVariable("VAL_DESCRIPTION", $a_set["role_desc"]);
		}
		$this->tpl->setVariable("VAL_TYPE", $a_set["type"]);
	}

}