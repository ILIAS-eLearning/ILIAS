<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * Build table list for objects of given user
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ilCtrl_Calls ilTrUserObjectsPropsTableGUI: ilFormPropertyDispatchGUI
 * @ingroup ServicesTracking
 */
class ilTrUserObjectsPropsTableGUI extends ilTable2GUI
{
	/**
	* Constructor
	*/
	function __construct($a_parent_obj, $a_parent_cmd, $a_table_id, $a_user_id)
	{
		global $ilCtrl, $lng, $ilAccess, $lng, $rbacsystem;
		
		$this->setId($a_table_id);
		$this->user_id = $a_user_id;

		/*
		$this->type = ilObject::_lookupType($a_obj_id);
		
		include_once("./Services/Tracking/classes/class.ilLPStatusFactory.php");
		$this->status_class = ilLPStatusFactory::_getClassById($a_obj_id);
		*/

		parent::__construct($a_parent_obj, $a_parent_cmd);
		
		$this->addColumn($this->lng->txt("title"), "title");
		
		foreach ($this->getSelectedColumns() as $c)
		{
			$l = $c;
			if (in_array($l, array("last_access", "first_access", "read_count", "spent_seconds", "mark", "status", "percentage")))
			{
				$l = "trac_".$l;
			}
			if ($l == "u_comment")
			{
				$l = "trac_comment";
			}
			$this->addColumn($this->lng->txt($l), $c);
		}

		$this->setExternalSorting(true);
		$this->setExternalSegmentation(true);
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($this->parent_obj, "applyFilter"));
		$this->setRowTemplate("tpl.user_objects_props_row.html", "Services/Tracking");
		//$this->disable("footer");
		$this->setEnableTitle(true);
		$this->initFilter();
		$this->setFilterCommand("applyFilterObjects");
		$this->setResetCommand("resetFilterObjects");
		$this->setDefaultOrderField("title");
		$this->setDefaultOrderDirection("asc");
		
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
	
		// default fields
		$cols = array();
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
		$cols["percentage"] = array(
			"txt" => $lng->txt("trac_percentage"),
			"default" => true);
		$cols["status"] = array(
			"txt" => $lng->txt("trac_status"),
			"default" => true);
		$cols["mark"] = array(
			"txt" => $lng->txt("trac_mark"),
			"default" => true);
		$cols["u_comment"] = array(
			"txt" => $lng->txt("trac_comment"),
			"default" => false);
		
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

		$tr_data = ilTrQuery::getDataForUser(
			$this->user_id,
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
			$tr_data = ilTrQuery::getDataForUser(
				$this->user_id,
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
			$this->tpl->setCurrentBlock("user_field");
			$val = (trim($data[$c]) == "")
				? " "
				: $data[$c];

			if ($data[$c] != "")
			{
				switch ($c)
				{
					case "first_access":
						$val = ilDatePresentation::formatDate(new ilDateTime($data[$c],IL_CAL_DATETIME));
						break;

					case "last_access":
						$val = ilDatePresentation::formatDate(new ilDateTime($data[$c],IL_CAL_UNIX));
						break;

					case "status":
						include_once("./Services/Tracking/classes/class.ilLearningProgressBaseGUI.php");
						$path = ilLearningProgressBaseGUI::_getImagePathForStatus($data[$c]);
						$text = ilLearningProgressBaseGUI::_getStatusText($data[$c]);
						$val = ilUtil::img($path, $text);
						break;

					case "spent_seconds":
						include_once("./classes/class.ilFormat.php");
						$val = ilFormat::_secondsToString($data[$c]);
						break;

					case "percentage":
						$val = $data[$c]."%";
						break;

				}
			}
			if ($c == "mark" && in_array($this->type, array("lm", "dbk")))
			{
				$val = "-";
			}
			if ($c == "spent_seconds" && in_array($this->type, array("exc")))
			{
				$val = "-";
			}
			if ($c == "percentage" &&
				(in_array(strtolower($this->status_class),
						  array("illpstatusmanual", "illpstatusscormpackage", "illpstatustestfinished")) ||
				$this->type == "exc"))
			{
				$val = "-";
			}

			$this->tpl->setVariable("VAL_UF", $val);
			$this->tpl->parseCurrentBlock();
		}

		$type = ilObject::_lookupType($data["obj_id"]);
		$title = ilObject::_lookupTitle($data["obj_id"]);
		if($title == "")
		{
			// sessions have no title
			if($type == "sess")
			{
				include_once "modules/Session/classes/class.ilObjSession.php";
				$sess = new ilObjSession($data["obj_id"], false);
				$title = $sess->getFirstAppointment()->appointmentToString();
			}
			if($title == "")
			{
				$title = "--".$lng->txt("none")."--";
			}
		}
		$this->tpl->setVariable("ICON", ilUtil::getTypeIconPath($type, $data["obj_id"], "small"));
		$this->tpl->setVariable("VAL_TITLE", $title);
	}

}
?>