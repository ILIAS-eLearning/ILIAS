<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

/**
 * Event listener for study programs. Has the following tasks:
 *
 *  * Remove all assignments of a user on all study programms when the
 *    user is removed.
 *
 *  * Add/Remove courses to/trom study programms, if upper category is under surveillance
 *
 * @author  Richard Klees <richard.klees@concepts-and-training.de>
 *
 */

class ilStudyProgrammeAppEventListener {

	public static function handleEvent($a_component, $a_event, $a_parameter)
	{

global $DIC;
$DIC->logger()->root()->log($a_component.' - '.$a_event);

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

			case "Modules/Course":
				switch ($a_event) {
					case "addParticipant":
						self::addMemberToProgrammes('crs', $a_parameter);
						break;
					case "deleteParticipant":
						self::removeMemberFromProgrammes('crs', $a_parameter);
						break;
				}
				break;
			case "Modules/Group":
				switch ($a_event) {
					case "addParticipant":
						self::addMemberToProgrammes('grp', $a_parameter);
						break;
					case "deleteParticipant":
						self::removeMemberFromProgrammes('grp', $a_parameter);
						break;
				}
				break;
			case "Services/AccessControl":
				switch ($a_event) {
					case "assignUser":
						self::addMemberToProgrammes('rol', $a_parameter);
						break;
					case "deassignUser":
						self::removeMemberFromProgrammes('rol', $a_parameter);
						break;
				}
				break;
			case "Modules/OrgUnit":
				switch ($a_event) {
					case "assignUserToPosition":
						self::addMemberToProgrammes('orgu', $a_parameter);
						break;
					case "deassignUserFromPosition":
					//case "delete":
						self::removeMemberFromProgrammes('orgu', $a_parameter);
						break;
				}
				break;

			default:
				throw new ilException("ilStudyProgrammeAppEventListener::handleEvent: "
									 ."Won't handle events of '$a_component'.");
		}
	}

	private static function onServiceUserDeleteUser($a_parameter) {
		$assignments = ilStudyProgrammeDIC::dic()['ilStudyProgrammeUserAssignmentDB']->getInstancesOfUser((int)$a_parameter["usr_id"]);
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
		if ($node_type == "crs" && $parent_type == "cat") {
			self::addCrsToProgrammes($node_ref_id, $parent_ref_id);
		}
	}

	private static function onServiceTreeMoveTree($a_parameter) {
		$node_ref_id = $a_parameter["source_id"];
		$new_parent_ref_id = $a_parameter["target_id"];
		$old_parent_ref_id = $a_parameter["old_parent_id"];

		$node_type = ilObject::_lookupType($node_ref_id, true);
		$new_parent_type = ilObject::_lookupType($new_parent_ref_id, true);
		$old_parent_type = ilObject::_lookupType($old_parent_ref_id, true);

		if (! in_array($node_type, ["crsr","crs"])
			|| (
				($new_parent_type != "prg" && $old_parent_type != "prg")
				&&
				$old_parent_type != "cat"
			)
		) {
			return;
		}

		if ($node_type === 'crs') {
			self::removeCrsFromProgrammes($node_ref_id, $old_parent_ref_id);
			if($new_parent_type === 'cat') {
				self::addCrsToProgrammes($node_ref_id, $new_parent_ref_id);
			}
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

		if ($old_parent_type !== "prg") {
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

	private static function addCrsToProgrammes(int $crs_ref_id, int $cat_ref_id)
	{
		ilObjStudyProgramme::addCrsToProgrammes($crs_ref_id, $cat_ref_id);
	}

	private static function removeCrsFromProgrammes(int $crs_ref_id, int $cat_ref_id)
	{
		ilObjStudyProgramme::removeCrsFromProgrammes($crs_ref_id, $cat_ref_id);
	}

	private static function addMemberToProgrammes(string $src_type, array $params)
	{
		global $DIC;
		$DIC->logger()->root()->dump($params);

		$obj_id = $params['obj_id'];
		$usr_id = $params['usr_id'];

		if(in_array($src_type, ['grp', 'rol'])) {
			$rol_id = $params['role_id'];
		}

		//ilObjStudyProgramme::addMemberToProgrammes($src_type, $src_id, $usr_id);
	}

	private static function removeMemberFromProgrammes(string $src_type, array $params)
	{
		global $DIC;
		$DIC->logger()->root()->dump($params);

		$obj_id = $params['obj_id'];
		$usr_id = $params['usr_id'];
		if(in_array($src_type, ['rol'])) {
			$rol_id = $params['role_id'];
		}

		//ilObjStudyProgramme::removeMemberFromProgrammes($src_type, $src_id, $usr_id);
	}

}
