<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");
include_once("./Modules/Exercise/classes/class.ilExAssignment.php");

/**
* Assignments table
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesExercise
*/
class ilAssignmentsTableGUI extends ilTable2GUI
{
	
	/**
	* Constructor
	*/
	function __construct($a_parent_obj, $a_parent_cmd, $a_exc)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;
		
		$this->exc = $a_exc;
		$this->setId("excass".$a_exc->getId());
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
	
		$this->setTitle($lng->txt("exc_assignments"));
		$this->setTopCommands(true);
		
		// if you add pagination and disable the unlimited setting:
		// fix saving of ordering of single pages!
		$this->setLimit(9999);
		
		$this->addColumn("", "", "1", true);
		$this->addColumn($this->lng->txt("title"), "title");
		$this->addColumn($this->lng->txt("type"), "type");
		$this->addColumn($this->lng->txt("exc_presentation_order"), "order_val");
		$this->addColumn($this->lng->txt("exc_start_time"), "start_time");
		$this->addColumn($this->lng->txt("exc_deadline"), "deadline");
		$this->addColumn($this->lng->txt("exc_mandatory"), "mandatory");
		$this->addColumn($this->lng->txt("exc_instruction"), "", "40%");
		$this->addColumn($this->lng->txt("actions"));
		
		$this->setDefaultOrderField("val_order");
		$this->setDefaultOrderDirection("asc");
		
		//$this->setDefaultOrderField("name");
		//$this->setDefaultOrderDirection("asc");
		
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.exc_assignments_row.html", "Modules/Exercise");
		//$this->disable("footer");
		$this->setEnableTitle(true);
		$this->setSelectAllCheckbox("id");

		$this->addMultiCommand("confirmAssignmentsDeletion", $lng->txt("delete"));
		
		$this->addCommandButton("orderAssignmentsByDeadline", $lng->txt("exc_order_by_deadline"));
		$this->addCommandButton("saveAssignmentOrder", $lng->txt("exc_save_order"));
		//$this->addCommandButton("addAssignment", $lng->txt("exc_add_assignment"));
		
		$types_map = array(
			ilExAssignment::TYPE_UPLOAD => $lng->txt("exc_type_upload"),
			ilExAssignment::TYPE_UPLOAD_TEAM => $lng->txt("exc_type_upload_team"),
			ilExAssignment::TYPE_BLOG => $lng->txt("exc_type_blog"),
			ilExAssignment::TYPE_PORTFOLIO => $lng->txt("exc_type_portfolio")
			);
		
		include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
		$data = ilExAssignment::getAssignmentDataOfExercise($this->exc->getId());
		foreach($data as $idx => $row)
		{
			$data[$idx]["type"] = $types_map[$row["type"]];
		}
		
		$this->setData($data);
	}
	
	/**
	* Fill table row
	*/
	protected function fillRow($d)
	{
		global $lng, $ilCtrl;

		$this->tpl->setVariable("ID", $d["id"]);
		if ($d["deadline"] > 0)
		{
			$this->tpl->setVariable("TXT_DEADLINE",
				ilDatePresentation::formatDate(new ilDateTime($d["deadline"],IL_CAL_UNIX)));
		}
		if ($d["start_time"] > 0)
		{
			$this->tpl->setVariable("TXT_START_TIME",
				ilDatePresentation::formatDate(new ilDateTime($d["start_time"],IL_CAL_UNIX)));
		}
		$this->tpl->setVariable("TXT_INSTRUCTIONS",
			ilUtil::shortenText($d["instruction"], 200, true));
		
		if ($d["mandatory"])
		{
			$this->tpl->setVariable("TXT_MANDATORY", $lng->txt("yes"));
		}
		else
		{
			$this->tpl->setVariable("TXT_MANDATORY", $lng->txt("no"));
		}
		
		$this->tpl->setVariable("TXT_TITLE", $d["title"]);
		$this->tpl->setVariable("TXT_TYPE", $d["type"]);
		$this->tpl->setVariable("ORDER_VAL", $d["order_val"]);
		
		$this->tpl->setVariable("TXT_EDIT", $lng->txt("edit"));
		$ilCtrl->setParameter($this->parent_obj, "ass_id", $d["id"]);
		$this->tpl->setVariable("CMD_EDIT",
			$ilCtrl->getLinkTarget($this->parent_obj, "editAssignment"));
	}

}
?>