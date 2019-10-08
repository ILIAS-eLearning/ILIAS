<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

/**
 * Event listener for study programs. Has the following tasks:
 *
 *  * Remove all assignments of a user on all study programms when the
 *    user is removed.
 *
 * @author  Richard Klees <richard.klees@concepts-and-training.de>
 *
 */

class ilStudyProgrammeAppEventListener {

	public static function handleEvent($a_component, $a_event, $a_parameter)
	{
		switch ($a_component) {
			case "Services/User":
				switch ($a_event){
					case "deleteUser": 
						self::onServiceUserDeleteUser($a_parameter);
						break;
				}
				break;
			case "Services/Tracking":
				switch($a_event) {
					case "updateStatus":
						self::onServiceTrackingUpdateStatus($a_parameter);
						break;
				}
				break;
			case "Services/Tree":
				switch($a_event) {
					case "insertNode":
						self::onServiceTreeInsertNode($a_parameter);
						break;
					case "moveTree":
						self::onServiceTreeMoveTree($a_parameter);
						break;
				}
				break;
			case "Services/Object":
				switch ($a_event) {
					case "delete":
					case "toTrash":
						self::onServiceObjectDeleteOrToTrash($a_parameter);
						break;
				}
				break;
			case "Services/ContainerReference":
				switch ($a_event) {
					case "deleteReference":
						self::onServiceObjectDeleteOrToTrash($a_parameter);
						break;
				}
				break;
			default:
				throw new ilException("ilStudyProgrammeAppEventListener::handleEvent: "
									 ."Won't handle events of '$a_component'.");
		}
	}

	private static function onServiceUserDeleteUser($a_parameter) {
		require_once("./Modules/StudyProgramme/classes/class.ilStudyProgrammeUserAssignment.php");
		$assignments = ilStudyProgrammeUserAssignment::getInstancesOfUser($a_parameter["usr_id"]);
		foreach ($assignments as $ass) {
			$ass->deassign();
		}
	}

	private static function onServiceTrackingUpdateStatus($a_par) {
		require_once("./Services/Tracking/classes/class.ilLPStatus.php");
		if ($a_par["status"] != ilLPStatus::LP_STATUS_COMPLETED_NUM) {
			return;
		}

		require_once("./Modules/StudyProgramme/classes/class.ilObjStudyProgramme.php");
		ilObjStudyProgramme::setProgressesCompletedFor($a_par["obj_id"], $a_par["usr_id"]);
	}

	private static function onServiceTreeInsertNode($a_parameter) {
		$node_ref_id = $a_parameter["node_id"];
		$parent_ref_id = $a_parameter["parent_id"];

		$node_type = ilObject::_lookupType($node_ref_id, true);
		$parent_type = ilObject::_lookupType($parent_ref_id, true);

		if ($node_type == "crsr" && $parent_type == "prg") {
			self::adjustProgrammeLPMode($parent_ref_id);
		}
		if ($node_type == "prg" && $parent_type == "prg") {
			self::addMissingProgresses($parent_ref_id);
		}
	}

	private static function onServiceTreeMoveTree($a_parameter) {
		$node_ref_id = $a_parameter["source_id"];
		$new_parent_ref_id = $a_parameter["target_id"];
		$old_parent_ref_id = $a_parameter["old_parent_id"];

		$node_type = ilObject::_lookupType($node_ref_id, true);
		$new_parent_type = ilObject::_lookupType($new_parent_ref_id, true);
		$old_parent_type = ilObject::_lookupType($old_parent_ref_id, true);
		
		if ($node_type != "crsr" || ($new_parent_type != "prg" && $old_parent_type != "prg")) {
			return;
		}

		if ($new_parent_type == "prg") {
			self::adjustProgrammeLPMode($new_parent_ref_id);
		}
		else if ($old_parent_type == "prg") {
			self::adjustProgrammeLPMode($old_parent_ref_id);
		}
	}

	private static function onServiceObjectDeleteOrToTrash($a_parameter) {
		$node_ref_id = $a_parameter["ref_id"];
		$old_parent_ref_id = $a_parameter["old_parent_ref_id"];
		
		$node_type = $a_parameter["type"];
		$old_parent_type = ilObject::_lookupType($old_parent_ref_id, true);

		if ($old_parent_type != "prg") {
			return;
		}

		self::adjustProgrammeLPMode($old_parent_ref_id);
	}

	private static function getStudyProgramme($a_ref_id) {
		require_once("Modules/StudyProgramme/classes/class.ilObjStudyProgramme.php");
		return ilObjStudyProgramme::getInstanceByRefId($a_ref_id);
	}

	private static function adjustProgrammeLPMode($a_ref_id) {
		$obj = self::getStudyProgramme($a_ref_id);
		$obj->adjustLPMode();
	}

	private static function addMissingProgresses($a_ref_id) {
		$obj = self::getStudyProgramme($a_ref_id);
		$obj->addMissingProgresses();
	}
}