<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
* TableGUI class for registration codes
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilRegistrationCodesTableGUI:
* @ingroup ServicesRegistration
*/
class ilRegistrationCodesTableGUI extends ilTable2GUI
{
	
	/**
	* Constructor
	*/
	function __construct($a_parent_obj, $a_parent_cmd)
	{
		global $ilCtrl, $lng;
		
		$this->setId("registration_code");
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		
		$this->addColumn("", "", "1", true);
		foreach ($this->getSelectedColumns() as $c)
		{
			$this->addColumn($this->lng->txt($c), $c);
		}
				
		$this->setExternalSorting(true);
		$this->setExternalSegmentation(true);
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($this->parent_obj, "listCodes"));
		$this->setRowTemplate("tpl.code_list_row.html", "Services/Registration");
		$this->setEnableTitle(true);
		$this->initFilter();
		$this->setFilterCommand("applyCodesFilter");
		$this->setResetCommand("resetCodesFilter");
		$this->setDefaultOrderField("registration_generated");
		$this->setDefaultOrderDirection("desc");

		$this->setSelectAllCheckbox("id[]");
		$this->setTopCommands(true);
		$this->addMultiCommand("deleteConfirmation", $lng->txt("delete"));
		
		$this->addCommandButton("exportCodes", $lng->txt("registration_codes_export"));
		
		$this->getItems();
	}

	/**
	* Get user items
	*/
	function getItems()
	{
		global $lng, $rbacreview, $ilObjDataCache;

		$this->determineOffsetAndOrder();
		
		include_once("./Services/Registration/classes/class.ilRegistrationCode.php");

		$order_map = array(
			"registration_code" => "code",
			"role" => "role", 
			"registration_generated" => "generated", 
			"registration_used" => "used"			
		);
		
		$codes_data = ilRegistrationCode::getCodesData(
			ilUtil::stripSlashes($order_map[$this->getOrderField()]),
			ilUtil::stripSlashes($this->getOrderDirection()),
			ilUtil::stripSlashes($this->getOffset()),
			ilUtil::stripSlashes($this->getLimit()),
			$this->filter["code"],
			$this->filter["role"],
			$this->filter["generated"]
			);
			
		if (count($codes_data["set"]) == 0 && $this->getOffset() > 0)
		{
			$this->resetOffset();
			$codes_data = ilRegistrationCode::getCodesData(
				ilUtil::stripSlashes($this->getOrderField()),
				ilUtil::stripSlashes($this->getOrderDirection()),
				ilUtil::stripSlashes($this->getOffset()),
				ilUtil::stripSlashes($this->getLimit()),
				$this->filter["code"],
				$this->filter["role"],
				$this->filter["generated"]
				);
		}
		
		include_once './Services/AccessControl/classes/class.ilObjRole.php';
		$options = array();
		foreach($rbacreview->getGlobalRoles() as $role_id)
		{
			if(!in_array($role_id, array(SYSTEM_ROLE_ID, ANONYMOUS_ROLE_ID)))
			{
				$role_map[$role_id] = $ilObjDataCache->lookupTitle($role_id);
			}
		}
		
		$result = array();
		foreach ($codes_data["set"] as $k => $code)
		{
			$result[$k]["registration_generated"] = ilDatePresentation::formatDate(new ilDateTime($code["generated"],IL_CAL_UNIX));

			if($code["used"])
			{
				$result[$k]["registration_used"] = ilDatePresentation::formatDate(new ilDateTime($code["used"],IL_CAL_UNIX));
			}
			else
			{
				$result[$k]["registration_used"] = "";
			}

			if($code["role"])
			{
				$result[$k]["role"] = $this->role_map[$code["role"]];
			}
			$result[$k]["registration_code"] = $code["code"];
			$result[$k]["code_id"] = $code["code_id"];
		}
		
		$this->setMaxCount($codes_data["cnt"]);
		$this->setData($result);
	}
	
	
	/**
	* Init filter
	*/
	function initFilter()
	{
		global $lng, $rbacreview, $ilUser, $ilObjDataCache;
		
		include_once("./Services/Registration/classes/class.ilRegistrationCode.php");
		
		// code
		include_once("./Services/Form/classes/class.ilTextInputGUI.php");
		$ti = new ilTextInputGUI($lng->txt("registration_code"), "query");
		$ti->setMaxLength(ilRegistrationCode::CODE_LENGTH);
		$ti->setSize(20);
		$ti->setSubmitFormOnEnter(true);
		$this->addFilterItem($ti);
		$ti->readFromSession();
		$this->filter["code"] = $ti->getValue();
		
		// role
		
		$this->role_map = array();
		foreach($rbacreview->getGlobalRoles() as $role_id)
		{
			if(!in_array($role_id, array(SYSTEM_ROLE_ID, ANONYMOUS_ROLE_ID)))
			{
				$this->role_map[$role_id] = $ilObjDataCache->lookupTitle($role_id);
			}
		}		
		
		include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
 		include_once './Services/AccessControl/classes/class.ilObjRole.php';
		$options = array("" => $this->lng->txt("registration_roles_all"))+
			$this->role_map;	
		$si = new ilSelectInputGUI($this->lng->txt("role"), "role");
		$si->setOptions($options);
		$this->addFilterItem($si);
		$si->readFromSession();
		$this->filter["role"] = $si->getValue();
		
		// generated
		include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
		$options = array("" => $this->lng->txt("registration_generated_all"));
		foreach((array)ilRegistrationCode::getGenerationDates() as $date)
		{
			$options[$date] = ilDatePresentation::formatDate(new ilDateTime($date,IL_CAL_UNIX));
		}
		$si = new ilSelectInputGUI($this->lng->txt("registration_generated"), "generated");
		$si->setOptions($options);
		$this->addFilterItem($si);
		$si->readFromSession();
		$this->filter["generated"] = $si->getValue();
	}
	
	public function getSelectedColumns()
	{
		return array("registration_code", "role", "registration_generated", "registration_used");
	}
	
	/**
	* Fill table row
	*/
	protected function fillRow($code)
	{
		$this->tpl->setVariable("ID", $code["code_id"]);
		foreach ($this->getSelectedColumns() as $c)
		{
			$this->tpl->setVariable("VAL_".strtoupper($c), $code[$c]);
		}
	}

}
?>
