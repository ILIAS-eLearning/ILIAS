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
		$this->setId("sp_member_list");
		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);

		$this->prg_obj_id = $a_prg_obj_id;
		$this->prg_ref_id = $a_prg_ref_id;
		$this->prg_has_lp_children = $a_parent_obj->getStudyProgramme()->hasLPChildren();

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
		$this->setShowRowsSelector(false);

		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, "view"));


		if($this->prg_has_lp_children) {
			$columns = $this->getColumnsLPChildren();
		} else {
			$columns = $this->getColumnsChildren();
		}

		foreach ($this->getSelectedColumns() as $column) {
			$columns[$column] = array($column);
		}

		$columns["action"] = array(null);

		foreach ($columns as $lng_var => $params) {
			$this->addColumn($this->lng->txt($lng_var), $params[0]);
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

		if(!$this->prg_has_lp_children) {
			$this->tpl->setCurrentBlock("points_current");
			$this->tpl->setVariable("POINTS_CURRENT", $a_set["points_current"]);
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setVariable("CUSTOM_PLAN", $a_set["last_change_by"] 
												? $this->lng->txt("yes")
												: $this->lng->txt("no"));
		$this->tpl->setVariable("BELONGS_TO", $a_set["belongs_to"]);
		$this->tpl->setVariable("ACTIONS", $this->buildActionDropDown( $a_set["actions"]
																	 , $a_set["prgrs_id"]
																	 , $a_set["assignment_id"]));

		foreach ($this->getSelectedColumns() as $column) {
			switch($column) {
				case "prg_assign_date":
					$this->tpl->setCurrentBlock("assign_date");
					$this->tpl->setVariable("ASSIGN_DATE", $a_set["assign_date"]);
					$this->tpl->parseCurrentBlock("assign_date");
					break;
				case "prg_assigned_by":
					$this->tpl->setCurrentBlock("assigned_by");
					$this->tpl->setVariable("ASSIGNED_BY", $a_set["assigned_by"]);
					$this->tpl->parseCurrentBlock("assigned_by");
					break;
			}
		}
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
				   //the following is a replacement for:
				   //IF(prgrs.status = ".ilStudyProgrammeProgress::STATUS_ACCREDITED.",prgrs.points,prgrs.points_cur)
				   //dirty hack to make it work with oracle :/ 1-|x-a|/max(|x-a|,1) = id_a(x)
				   ."     , prgrs.points_cur*"
				       ."ABS(prgrs.status - ".ilStudyProgrammeProgress::STATUS_ACCREDITED.")"
				           ."/(GREATEST(ABS(prgrs.status - ".ilStudyProgrammeProgress::STATUS_ACCREDITED."),1))"
				   ."     + prgrs.points*"
				       ."(1 -ABS(prgrs.status - ".ilStudyProgrammeProgress::STATUS_ACCREDITED.")"
				           ."/(GREATEST(ABS(prgrs.status - ".ilStudyProgrammeProgress::STATUS_ACCREDITED."),1))) points_current"
				   ."     , prgrs.last_change_by"
				   ."     , prgrs.status"
				   ."     , blngs.title belongs_to"
				   ."     , cmpl_usr.login accredited_by"
				   ."     , cmpl_obj.title completion_by"
				   ."     , prgrs.assignment_id assignment_id"
				   ."     , ass.root_prg_id root_prg_id"
				   ."     , ass.last_change assign_date"
				   ."     , ass_usr.login assigned_by"
				   // for sorting
				   ."     , CONCAT(pcp.firstname, pcp.lastname) name"
				   ."     , (prgrs.last_change_by IS NOT NULL) custom_plan"
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
			$this->db->setLimit($limit, $offset !== null ? $offset : 0);
		}
		$res = $this->db->query($query);

		$members_list = array();
		while($rec = $this->db->fetchAssoc($res)) {
			$rec["actions"] = ilStudyProgrammeUserProgress::getPossibleActions(
										$a_prg_id, $rec["root_prg_id"], $rec["status"]);
			$rec['points_current'] = number_format($rec['points_current']);
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
				."  LEFT JOIN usr_data ass_usr ON ass_usr.usr_id = ass.last_change_by"
				."  LEFT JOIN object_data cmpl_obj ON cmpl_obj.obj_id = prgrs.completion_by";
	}

	protected function getWhere($a_prg_id) {
		return " WHERE prgrs.prg_id = ".$this->db->quote($a_prg_id, "integer");
	}

	/**
	 * Get selectable columns
	 *
	 * @return array[] 	$cols
	 */
	function getSelectableColumns() {
		// default fields
		$cols = array();

		$cols["prg_assign_date"] = array(
				"txt" => $this->lng->txt("prg_assign_date"));

		$cols["prg_assigned_by"] = array(
				"txt" => $this->lng->txt("prg_assigned_by"));

		return $cols;
	}

	protected function getColumnsChildren() {
		return array( "name" 				=> array("name")
						, "login" 				=> array("login")
						, "prg_status" 			=> array("status")
						, "prg_completion_by"	=> array(null)
						, "prg_points_required" => array("points")
						, "prg_points_current"  => array("points_current")
						, "prg_custom_plan"		=> array("custom_plan")
						, "prg_belongs_to"		=> array("belongs_to")
						);
	}

	protected function getColumnsLPChildren() {
		return array( "name" 				=> array("name")
						, "login" 				=> array("login")
						, "prg_status" 			=> array("status")
						, "prg_completion_by"	=> array(null)
						, "prg_points_reachable" => array("points")
						, "prg_custom_plan"		=> array("custom_plan")
						, "prg_belongs_to"		=> array("belongs_to")
						);
	}
}

?>