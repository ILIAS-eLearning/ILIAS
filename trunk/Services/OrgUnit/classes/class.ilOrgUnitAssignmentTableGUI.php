<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Table/classes/class.ilTable2GUI.php";


/**
* Organisation Unit Assignment Table
*
* @author	Bjoern Heyser <bheyser@databay.de>
* @version	$Id$
*
* @ingroup ServicesOrgUnit
* 
*/
class ilOrgUnitAssignmentTableGUI extends ilTable2GUI
{
	public function __construct($a_parent_obj, $a_parent_cmd, $a_user_id)
	{
		global $ilCtrl, $lng, $ilAccess, $lng, $rbacsystem;
		
		$this->setId("user");
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
//		$this->setTitle($this->lng->txt("users"));
		
		#$this->addColumn("", "", "1", true);
		$this->addColumn($this->lng->txt("org_unit_title"), "org_unit_title", "30%");
		$this->addColumn($this->lng->txt("org_unit_subtitle"), "org_unit_subtitle", "50%");
		$this->addColumn($this->lng->txt("org_unit_reporting_access"), "org_unit_reporting_access", "20%");
		
		$this->setExternalSorting(true);
		$this->setExternalSegmentation(true);
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($this->parent_obj, $this->parent_cmd));
		$this->setRowTemplate("tpl.org_unit_assignment_row.html", "Services/OrgUnit");
		//$this->disable("footer");
		$this->setEnableTitle(true);
		#$this->initFilter();
		#$this->setFilterCommand("applyFilter");
		$this->setDefaultOrderField("title");
		$this->setDefaultOrderDirection("asc");

		#$this->setSelectAllCheckbox("id[]");
		$this->setTopCommands(true);

		/*if ($rbacsystem->checkAccess('delete', $a_parent_obj->object->getRefId()))
		{
			$this->addMultiCommand("deleteUsers", $lng->txt("delete"));
		}
		$this->addMultiCommand("activateUsers", $lng->txt("activate"));
		$this->addMultiCommand("deactivateUsers", $lng->txt("deactivate"));
		$this->addMultiCommand("restrictAccess", $lng->txt("accessRestrict"));
		$this->addMultiCommand("freeAccess", $lng->txt("accessFree"));
		$this->addMultiCommand("exportUsers", $lng->txt("export"));
		$this->addCommandButton("importUserForm", $lng->txt("import_users"));
		$this->addCommandButton("addUser", $lng->txt("usr_add"));*/
		
		$this->getItems($a_user_id);
	}
	
	public function fillRow($a_set)
	{
		global $ilCtrl, $lng;
		
		$this->tpl->setVariable("VAL_TITLE", $a_set["title"]);
		$this->tpl->setVariable("VAL_SUBTITLE", $a_set["subtitle"]);
		$reporting_access = $a_set["reporting_access"] ? $lng->txt('yes') : $lng->txt('no');
		$this->tpl->setVariable("VAL_REPORTING_ACCESS", $reporting_access);
		#$ilCtrl->setParameterByClass("ilobjusergui", "obj_id", $a_set["usr_id"]);
		#$this->tpl->setVariable("HREF_LOGIN",
		#	$ilCtrl->getLinkTargetByClass("ilobjusergui", "view"));
		#$ilCtrl->setParameterByClass("ilobjusergui", "obj_id", "");
	}
	
	private function getItems($a_user_id)
	{
		global $lng;

		require_once('Services/OrgUnit/classes/class.ilOrgUnit.php');
		$units = ilOrgUnit::getInstancesByAssignedUser($a_user_id);
		
		$data = array();
		foreach($units as $unit)
		{
			$data[] = array(
				'title'			=> $unit->getTitle(),
				'subtitle'		=> $unit->getSubTitle(),
				'reporting_access'	=> $unit->hasUserReportingAccess($a_user_id)
			);
		}
		
		$this->setData($data);
	}
}

?>
