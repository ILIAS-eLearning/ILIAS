<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * Learning progress table: One object, rows: users, columns: properties
 * Example: A course, rows: members, columns: name, status, mark, ...
 *
 * PD, Personal Learning Progress -> UserObjectsProps
 * PD, Learning Progress of Users -> UserAggObjectsProps
 * Crs, Learnign Progress of Participants -> ObjectUsersProps
 * Details -> UserObjectsProps
 *
 * More:
 * PropUsersObjects (Grading Overview in Course)
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ilCtrl_Calls ilTrObjectUsersPropsTableGUI: ilFormPropertyDispatchGUI
 * @ingroup ServicesTracking
 */
class ilTrObjectUsersPropsTableGUI extends ilTable2GUI
{
	
	/**
	* Constructor
	*/
	function __construct($a_parent_obj, $a_parent_cmd, $a_table_id, $a_obj_id)
	{
		global $ilCtrl, $lng, $ilAccess, $lng, $rbacsystem;
		
		$this->setId($a_table_id);
		$this->obj_id = $a_obj_id;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
//		$this->setTitle($this->lng->txt("users"));
		
		$this->addColumn("", "", "1", true);	// checkbox column
		$this->addColumn($this->lng->txt("login"), "login");
		
		foreach ($this->getSelectedColumns() as $c)
		{
			$l = $c;
			if (in_array($l, array("last_access", "first_access", "read_count", "spent_seconds")))
			{
				$l = "trac_".$l;
			}
			$this->addColumn($this->lng->txt($l), $c);
		}

		$this->setExternalSorting(true);
		$this->setExternalSegmentation(true);
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($this->parent_obj, "applyFilter"));
		$this->setRowTemplate("tpl.object_users_props_row.html", "Services/Tracking");
		//$this->disable("footer");
		$this->setEnableTitle(true);
		$this->initFilter();
		$this->setFilterCommand("applyFilter");
		$this->setDefaultOrderField("login");
		$this->setDefaultOrderDirection("asc");

		$this->setSelectAllCheckbox("id[]");
		//$this->addMultiCommand("deleteUsers", $lng->txt("delete"));
		//$this->addCommandButton("addUser", $lng->txt("usr_add"));
		
		$this->getItems();
	}
	
	/**
	 * Get selectable columns
	 *
	 * @param
	 * @return
	 */
	function getSelectableColumns()
	{
		global $lng;
		
		include_once("./Services/User/classes/class.ilUserProfile.php");
		$up = new ilUserProfile();
		$up->skipGroup("preferences");
		$up->skipGroup("settings");
		$ufs = $up->getStandardFields();

		// default fields
		$cols = array();
		$cols["firstname"] = array(
			"txt" => $lng->txt("firstname"),
			"default" => true);
		$cols["lastname"] = array(
			"txt" => $lng->txt("lastname"),
			"default" => true);

		$cols["first_access"] = array(
			"txt" => $lng->txt("trac_first_access"),
			"default" => true);
		$cols["last_access"] = array(
			"txt" => $lng->txt("trac_last_access"),
			"default" => true);
		$cols["read_count"] = array(
			"txt" => $lng->txt("trac_read_count"),
			"default" => true);
		$cols["spent_seconds"] = array(
			"txt" => $lng->txt("trac_spent_seconds"),
			"default" => true);

		$cols["email"] = array(
			"txt" => $lng->txt("email"),
			"default" => false);
		
		// other user profile fields
		foreach ($ufs as $f => $fd)
		{
			if (!isset($cols[$f]) && !$fd["lists_hide"])
			{
				$cols[$f] = array(
					"txt" => $lng->txt($f),
					"default" => false);
			}
		}
		
		// fields that are always shown
		unset($cols["username"]);
		
		return $cols;
	}
	
	/**
	* Get user items
	*/
	function getItems()
	{
		global $lng;

		$this->determineOffsetAndOrder();
		
		include_once("./Services/Tracking/classes/class.ilTrQuery.php");
		
		$additional_fields = $this->getSelectedColumns();

		$tr_data = ilTrQuery::getDataForObject(
			$this->obj_id,
			ilUtil::stripSlashes($this->getOrderField()),
			ilUtil::stripSlashes($this->getOrderDirection()),
			ilUtil::stripSlashes($this->getOffset()),
			ilUtil::stripSlashes($this->getLimit()),
			$this->filter,
			$additional_fields
			);
			
		if (count($tr_data["set"]) == 0 && $this->getOffset() > 0)
		{
			$this->resetOffset();
			$tr_data = ilTrQuery::getDataForObject(
				$this->obj_id,
				ilUtil::stripSlashes($this->getOrderField()),
				ilUtil::stripSlashes($this->getOrderDirection()),
				ilUtil::stripSlashes($this->getOffset()),
				ilUtil::stripSlashes($this->getLimit()),
				$this->filter,
				$additional_fields
				);
		}

		$this->setMaxCount($tr_data["cnt"]);
		$this->setData($tr_data["set"]);
	}
	
	
	/**
	* Init filter
	*/
	function initFilter()
	{
		global $lng, $rbacreview, $ilUser;
		
		// title/description
		/* include_once("./Services/Form/classes/class.ilTextInputGUI.php");
		$ti = new ilTextInputGUI($lng->txt("login")."/".$lng->txt("email")."/".$lng->txt("name"), "query");
		$ti->setMaxLength(64);
		$ti->setSize(20);
		$ti->setSubmitFormOnEnter(true);
		$this->addFilterItem($ti);
		$ti->readFromSession();
		$this->filter["query"] = $ti->getValue(); */
		
	}
	
	/**
	* Fill table row
	*/
	protected function fillRow($data)
	{
		global $ilCtrl, $lng;

		foreach ($this->getSelectedColumns() as $c)
		{
			if (in_array($c, array("email", "firstname", "lastname")))
			{
				$this->tpl->setCurrentBlock($c);
				$this->tpl->setVariable("VAL_".strtoupper($c), $data[$c]);
			}
			else	// all other fields
			{
				$this->tpl->setCurrentBlock("user_field");
				$val = (trim($data[$c]) == "")
					? " "
					: $data[$c];
					
				if ($data[$c] != "")
				{
					switch ($c)
					{
						case "gender":
							$val = $lng->txt("gender_".$data[$c]);
							break;
					}
				}
				$this->tpl->setVariable("VAL_UF", $val);
			}
			
			$this->tpl->parseCurrentBlock();
		}
		
		$this->tpl->setCurrentBlock("checkb");
		$this->tpl->setVariable("ID", $data["usr_id"]);
		$this->tpl->parseCurrentBlock();
		
		$this->tpl->setVariable("VAL_LOGIN", $data["login"]);
		
		//$ilCtrl->setParameterByClass("ilobjusergui", "obj_id", $data["usr_id"]);
		//$this->tpl->setVariable("HREF_LOGIN",
		//	$ilCtrl->getLinkTargetByClass("ilobjusergui", "view"));
		//$ilCtrl->setParameterByClass("ilobjusergui", "obj_id", "");
	}

}
?>