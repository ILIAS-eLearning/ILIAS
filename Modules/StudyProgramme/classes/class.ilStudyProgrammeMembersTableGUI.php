<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("Services/Table/classes/class.ilTable2GUI.php");
require_once("Modules/StudyProgramme/classes/class.ilStudyProgrammeUserProgress.php");
require_once("Modules/StudyProgramme/classes/model/class.ilStudyProgrammeProgress.php");
require_once("Modules/StudyProgramme/classes/model/class.ilStudyProgrammeAssignment.php");
require_once("Modules/StudyProgramme/classes/class.ilObjStudyProgramme.php");
require_once("Modules/StudyProgramme/classes/class.ilStudyProgrammeUserProgress.php");
require_once("Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");

/**
 * Class ilObjStudyProgrammeMembersTableGUI
 *
 * @author: Richard Klees <richard.klees@concepts-and-training.de>
 *
 */

class ilStudyProgrammeMembersTableGUI extends ilTable2GUI {
	protected $prg_obj_id;
	protected $prg_ref_id;
	
	public function __construct($a_prg_obj_id, $a_prg_ref_id, $a_parent_obj, $a_parent_cmd="", $a_template_context="") {
		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);

		$this->prg_obj_id = $a_prg_obj_id;
		$this->prg_ref_id = $a_prg_ref_id;

		global $DIC;
		$ilCtrl = $DIC['ilCtrl'];
		$lng = $DIC['lng'];
		$ilDB = $DIC['ilDB'];
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
		$this->db = $ilDB;

		$this->setEnableTitle(true);
		$this->setTopCommands(false);
		$this->setEnableHeader(true);
		// TODO: switch this to internal sorting/segmentation
		$this->setExternalSorting(true);
		$this->setExternalSegmentation(true);
		$this->setRowTemplate("tpl.members_table_row.html", "Modules/StudyProgramme");
		
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, "view"));


		$columns = array( "name" 				=> array("name")
						, "login" 				=> array("login")
						, "prg_status" 			=> array("status")
						, "prg_completion_by"	=> array(null)
						, "prg_points_required" => array("points")
						, "prg_points_current"  => array("points_current")
						, "prg_custom_plan"		=> array("custom_plan")
						, "prg_belongs_to"		=> array("belongs_to")
						, "actions"				=> array(null)
						);
		foreach ($columns as $lng_var => $params) {
			$this->addColumn($lng->txt($lng_var), $params[0]);
		}
		
		$this->determineLimit();
		$this->determineOffsetAndOrder();
		$oder = $this->getOrderField();
		$dir = $this->getOrderDirection();

		$members_list = $this->fetchData($a_prg_obj_id, $this->getLimit(), $this->getOffset(), $this->getOrderField(), $this->getOrderDirection());
		$this->setMaxCount($this->countFetchData($a_prg_obj_id));
		$this->setData($members_list);
	}

	protected function fillRow($a_set) {
		$this->tpl->setVariable("FIRSTNAME", $a_set["firstname"]);
		$this->tpl->setVariable("LASTNAME", $a_set["lastname"]);
		$this->tpl->setVariable("LOGIN", $a_set["login"]);
		$this->tpl->setVariable("STATUS", ilStudyProgrammeUserProgress::statusToRepr($a_set["status"]));
		$this->tpl->setVariable("COMPLETION_BY", $a_set["completion_by"]);
		$this->tpl->setVariable("POINTS_REQUIRED", $a_set["points"]);

		$this->tpl->setVariable("POINTS_CURRENT", $a_set["points_current"]);
		$this->tpl->setVariable("CUSTOM_PLAN", $a_set["last_change_by"] 
												? $this->lng->txt("yes")
												: $this->lng->txt("no"));
		$this->tpl->setVariable("BELONGS_TO", $a_set["belongs_to"]);
		$this->tpl->setVariable("ACTIONS", $this->buildActionDropDown( $a_set["actions"]
																	 , $a_set["prgrs_id"]
																	 , $a_set["assignment_id"]));
	}
	
	protected function buildActionDropDown($a_actions, $a_prgrs_id, $a_ass_id) {
		$l = new ilAdvancedSelectionListGUI();
		foreach($a_actions as $action) {
			$target = $this->getLinkTargetForAction($action, $a_prgrs_id, $a_ass_id);
			$l->addItem($this->lng->txt("prg_$action"), $action, $target);
		}
		return $l->getHTML();
	}
	
	protected function getLinkTargetForAction($a_action, $a_prgrs_id, $a_ass_id) {
		return $this->getParentObject()->getLinkTargetForAction($a_action, $a_prgrs_id, $a_ass_id);
	}

	protected function fetchData($a_prg_id, $limit = null, $offset = null, $order_coloumn = null, $order_direction = null) {
		// TODO: Reimplement this in terms of ActiveRecord when innerjoin
		// supports the required rename functionality
		$query = "SELECT prgrs.id prgrs_id"
				   ."     , pcp.firstname"
				   ."     , pcp.lastname"
				   ."     , pcp.login"
				   ."     , prgrs.points"
				   ."     , IF(prgrs.status = ".ilStudyProgrammeProgress::STATUS_ACCREDITED.", prgrs.points, prgrs.points_cur) points_current"
				   ."     , prgrs.last_change_by"
				   ."     , prgrs.status"
				   ."     , blngs.title belongs_to"
				   ."     , cmpl_usr.login accredited_by"
				   ."     , cmpl_obj.title completion_by"
				   ."     , prgrs.assignment_id assignment_id"
				   ."     , ass.root_prg_id root_prg_id"
				   // for sorting
				   ."     , CONCAT(pcp.firstname, pcp.lastname) name"
				   ."     , IF(prgrs.last_change_by IS NULL, 0, 1) custom_plan"
				   ;
		
		$query .= $this->getFrom();
		$query .= $this->getWhere($a_prg_id);

		if($order_coloumn !== null) {
			$query .= " ORDER BY $order_coloumn";

			if($order_direction !== null) {
				$query .= " $order_direction";
			}
		}


		if($limit !== null) {
			$query .= " LIMIT ".$this->db->quote($limit, "integer");
		}

		if($offset !== null) {
			$query .= " OFFSET ".$this->db->quote($offset, "integer");
		}
		$res = $this->db->query($query);
	
		$members_list = array();
		while($rec = $this->db->fetchAssoc($res)) {
			$rec["actions"] = ilStudyProgrammeUserProgress::getPossibleActions(
										$a_prg_id, $rec["root_prg_id"], $rec["status"]);

			if ($rec["status"] == ilStudyProgrammeProgress::STATUS_COMPLETED) {
				// If the status completed and there is a non-null completion_by field
				// in the set, this means the completion was achieved by some leaf in
				// the program tree.
				if (!$rec["completion_by"]) {
					require_once("Modules/StudyProgramme/classes/class.ilStudyProgrammeUserProgress.php");
					$prgrs = ilStudyProgrammeUserProgress::getInstanceForAssignment( $this->prg_obj_id
																					  , $rec["assignment_id"]);
					$rec["completion_by"] = implode(", ", $prgrs->getNamesOfCompletedOrAccreditedChildren());
				}
			}
			else if($rec["status"] == ilStudyProgrammeProgress::STATUS_ACCREDITED) {
				$rec["completion_by"] = $rec["accredited_by"];
			}

			$members_list[] = $rec;
		}
		return $members_list;
	}

	protected function countFetchData($a_prg_id) {
		// TODO: Reimplement this in terms of ActiveRecord when innerjoin
		// supports the required rename functionality
		$query = "SELECT count(prgrs.id) as cnt";
		$query .= $this->getFrom();
		$query .= $this->getWhere($a_prg_id);

		$res = $this->db->query($query);
		$rec = $this->db->fetchAssoc($res);

		return $rec["cnt"];
	}

	protected function getFrom() {
		return "  FROM ".ilStudyProgrammeProgress::returnDbTableName()." prgrs"
				."  JOIN usr_data pcp ON pcp.usr_id = prgrs.usr_id"
				."  JOIN ".ilStudyProgrammeAssignment::returnDbTableName()." ass"
						 ." ON ass.id = prgrs.assignment_id"
				."  JOIN object_data blngs ON blngs.obj_id = ass.root_prg_id"
				."  LEFT JOIN usr_data cmpl_usr ON cmpl_usr.usr_id = prgrs.completion_by"
				."  LEFT JOIN object_data cmpl_obj ON cmpl_obj.obj_id = prgrs.completion_by";
	}

	protected function getWhere($a_prg_id) {
		return " WHERE prgrs.prg_id = ".$this->db->quote($a_prg_id, "integer");
	}
}

?>