<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
* TableGUI class for account codes
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id$
*
* @ilCtrl_Calls ilAccountCodesTableGUI:
* @ingroup ServicesUser
*/
class ilAccountCodesTableGUI extends ilTable2GUI
{	
	/**
	* Constructor
	*/
	function __construct($a_parent_obj, $a_parent_cmd)
	{
		global $ilCtrl, $lng;
		
		$this->setId("account_code");
		
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
		$this->setRowTemplate("tpl.code_list_row.html", "Services/User");
		$this->setEnableTitle(true);
		$this->initFilter();
		$this->setFilterCommand("applyCodesFilter");
		$this->setResetCommand("resetCodesFilter");
		$this->setDefaultOrderField("code_generated");
		$this->setDefaultOrderDirection("desc");

		$this->setSelectAllCheckbox("id[]");
		$this->setTopCommands(true);
		$this->addMultiCommand("deleteCodeConfirmation", $lng->txt("delete"));
		
		$this->addCommandButton("exportCodes", $lng->txt("account_codes_export"));
		
		$this->getItems();
	}

	/**
	* Get user items
	*/
	function getItems()
	{
		global $lng, $rbacreview, $ilObjDataCache;

		$this->determineOffsetAndOrder();
		
		include_once("./Services/User/classes/class.ilAccountCode.php");

		$order_map = array(
			"account_code" => "code",
			"valid_until" => "valid_until", 
			"code_generated" => "generated", 
			"code_used" => "used"			
		);
		
		$codes_data = ilAccountCode::getCodesData(
			ilUtil::stripSlashes($order_map[$this->getOrderField()]),
			ilUtil::stripSlashes($this->getOrderDirection()),
			ilUtil::stripSlashes($this->getOffset()),
			ilUtil::stripSlashes($this->getLimit()),
			$this->filter["code"],
			$this->filter["valid_until"],
			$this->filter["generated"]
			);
			
		if (count($codes_data["set"]) == 0 && $this->getOffset() > 0)
		{
			$this->resetOffset();
			$codes_data = ilAccountCode::getCodesData(
				ilUtil::stripSlashes($this->getOrderField()),
				ilUtil::stripSlashes($this->getOrderDirection()),
				ilUtil::stripSlashes($this->getOffset()),
				ilUtil::stripSlashes($this->getLimit()),
				$this->filter["code"],
				$this->filter["valid_until"],
				$this->filter["generated"]
				);
		}
		
		$result = array();
		foreach ($codes_data["set"] as $k => $code)
		{
			$result[$k]["code_generated"] = ilDatePresentation::formatDate(new ilDateTime($code["generated"],IL_CAL_UNIX));

			if($code["used"])
			{
				$result[$k]["code_used"] = ilDatePresentation::formatDate(new ilDateTime($code["used"],IL_CAL_UNIX));
			}
			else
			{
				$result[$k]["code_used"] = "";
			}

			$result[$k]["account_code"] = $code["code"];
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
		global $lng, $rbacreview, $ilUser;
		
		include_once("./Services/User/classes/class.ilAccountCode.php");
		
		// code
		include_once("./Services/Form/classes/class.ilTextInputGUI.php");
		$ti = new ilTextInputGUI($lng->txt("account_code"), "query");
		$ti->setMaxLength(ilAccountCode::CODE_LENGTH);
		$ti->setSize(20);
		$ti->setSubmitFormOnEnter(true);
		$this->addFilterItem($ti);
		$ti->readFromSession();
		$this->filter["code"] = $ti->getValue();
		
		// generated
		include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
		$options = array("" => $this->lng->txt("account_code_generated_all"));
		foreach((array)ilAccountCode::getGenerationDates() as $date)
		{
			$options[$date] = ilDatePresentation::formatDate(new ilDateTime($date,IL_CAL_UNIX));
		}
		$si = new ilSelectInputGUI($this->lng->txt("code_generated"), "generated");
		$si->setOptions($options);
		$this->addFilterItem($si);
		$si->readFromSession();
		$this->filter["generated"] = $si->getValue();
	}
	
	public function getSelectedColumns()
	{
		return array("account_code", "valid_until", "code_generated", "code_used");
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
