<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
* TableGUI class for access keys
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesAccessibility
*/
class ilAccessKeyTableGUI extends ilTable2GUI
{
	
	/**
	* Constructor
	*/
	function __construct($a_parent_obj, $a_parent_cmd)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		
		// get keys
		include_once("./Services/Accessibility/classes/class.ilAccessKey.php");
		
		$keys = ilAccessKey::getKeys();
		$data = array();
		foreach ($keys as $f => $k)
		{
			$data[] = array("func_id" => $f, "access_key" => $k);
		}
		$this->setData($data);
		$this->setTitle($lng->txt("acc_access_keys"));
		$this->setLimit(9999);
		
		$this->addColumn($this->lng->txt("acc_component"), "", "");
		$this->addColumn($this->lng->txt("acc_function"), "", "");
		$this->addColumn($this->lng->txt("acc_access_key"), "", "");
		
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.access_key_row.html", "Services/Accessibility");
		$this->disable("footer");
		$this->setEnableTitle(true);

//		$this->addMultiCommand("", $lng->txt(""));
		if ($ilAccess->checkAccess("write", "", $_GET["ref_id"]))
		{
			$this->addCommandButton("saveAccessKeys", $lng->txt("save"));
		}
	}
	
	/**
	* Fill table row
	*/
	protected function fillRow($a_set)
	{
		global $lng;

		$this->tpl->setVariable("VAL_COMPONENT", ilAccessKey::getComponentNames($a_set["func_id"]));
		$this->tpl->setVariable("VAL_FUNCTION", ilAccessKey::getFunctionName($a_set["func_id"]));
		$this->tpl->setVariable("FUNC_ID", $a_set["func_id"]);
		$this->tpl->setVariable("VAL_ACC_KEY", ilUtil::prepareFormOutput($a_set["access_key"]));
	}

}
?>
