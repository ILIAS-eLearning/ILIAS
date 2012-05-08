<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

define('LP_STATUS_NOT_ATTEMPTED','trac_no_attempted');
define('LP_STATUS_IN_PROGRESS','trac_in_progress');
define('LP_STATUS_COMPLETED','trac_completed');
define('LP_STATUS_FAILED','trac_failed');

define('LP_STATUS_NOT_ATTEMPTED_NUM', 0);
define('LP_STATUS_IN_PROGRESS_NUM', 1);
define('LP_STATUS_COMPLETED_NUM', 2);
define('LP_STATUS_FAILED_NUM', 3);

// Stati for events
define('LP_STATUS_REGISTERED','trac_registered');
define('LP_STATUS_NOT_REGISTERED','trac_not_registered');
define('LP_STATUS_PARTICIPATED','trac_participated');
define('LP_STATUS_NOT_PARTICIPATED','trac_not_participated');

/**
 * Abstract class ilLPStatus for all learning progress modes
 * E.g  ilLPStatusManual, ilLPStatusObjectives ...
 *
 * @author Stefan Meyer <meyer@leifos.com>
 *
 * @version $Id$
 *
 * @ingroup ServicesTracking
 *
 */
class ilLPStatus
{
	var $obj_id = null;

	var $db = null;

	function ilLPStatus($a_obj_id)
	{
		global $ilDB;

		$this->obj_id = $a_obj_id;
		$this->db =& $ilDB;
	}

	function _getCountNotAttempted($a_obj_id)
	{
		return 0;
	}

	function _getNotAttempted($a_obj_id)
	{
		return array();
	}
	
	function _getCountInProgress($a_obj_id)
	{
		return 0;
	}
	function _getInProgress($a_obj_id)
	{
		return array();
	}

	function _getCountCompleted($a_obj_id)
	{
		return 0;
	}
	function _getCompleted($a_obj_id)
	{
		return array();
	}
	function _getFailed($a_obj_id)
	{
		return array();
	}
	function _getCountFailed()
	{
		return 0;
	}
	function _getStatusInfo($a_obj_id)
	{
		return array();
	}
	function _getTypicalLearningTime($a_obj_id)
	{
		include_once 'Services/MetaData/classes/class.ilMDEducational.php';
		return ilMDEducational::_getTypicalLearningTimeSeconds($a_obj_id);
	}


	/**
	 * New status handling (st: status, nr: accesses, p: percentage, t: time spent, m: mark)
	 *
	 * Learning progress:
	 * - lm: ilLPStatusManual (st, nr, t, ok, p-, m-), ilLPStatusVisits (st, nr, p, t, ok, m-),
	 *       ilLPStatusTypicalLearningTime (st, nr, p, t, ok, m-)
	 * - dbk: ilLPStatusManual (st, nr, t ok, p-, m-)
	 * - htlm: ilLPStatusManual (st, nr, t, m ok, p-) (but mark handling different than lm/dbk)
	 * - crs: ilLPStatusManualByTutor (st ok), ilLPStatusObjectives (st ok), ilLPStatusCollection
	 * - grp: ilLPStatusManualByTutor, ilLPStatusCollection
	 * - fold: ilLPStatusCollection
	 * - session: ilLPStatusEvent (st ok, nr and t only for infoscreen, comment and mark are not saved in learning progress table!)
	 * - exercise: ilLPStatusExerciseReturned (st, nr, m ok, t-, p-)
	 * - scorm: ilLPStatusSCORM (st, nr, p, t, m ok), ilLPStatusSCORMPackage (st, nr, t, m ok, p-)
	 * - tst: ilLPStatusTestFinished (st, nr, t, p ok, mark not synced),
	 *        ilLPStatusTestPassed (st, nr, t ok, p-, mark not synced)
	 *
	 * Added determine Status to:
	 * - ilLPStatusManual
	 * - ilLPStatusVisits
	 * - ilLPStatusTypicalLearningTime
	 * - ilLPStatusManualByTutor
	 * - ilLPStatusObjectives
	 * - ilLPStatusCollection
	 * - ilLPStatusEvent
	 * - ilLPStatusExerciseReturned
	 * - ilLPStatusSCORMPackage
	 * - ilLPStatusTestFinished
	 * - ilLPStatusTestPassed
	 *
	 * Updating the status:
	 * - ilLPStatus::setInProgressIfNotAttempted($a_obj_id, $a_user_id) added to:
	 * -- ilLearningProgress->_tracProgress()
	 * -- ilTestSession->saveToDb()
	 *
	 * - ilChangeEvent::_recordReadEvent() added to:
	 * -- ilObjSessionGUI->infoScreen()
	 *
	 * - ilLearningProgress->_tracProgress() added to:
	 * --
	 *
	 * - ilLPStatusWrapper::_updateStatus($a_obj_id, $a_user_id); added to:
	 * -- ilInfoScreenGUI->saveProgress()
	 * -- ilLMPresentation->ilPage()
	 * -- ilLPListOfObjectsGUI->updateUser()
	 * -- ilCourseObjectiveResult->reset()
	 * -- ilCourseObjectiveResult->__updatePassed()
	 * -- ilEventParticipants->updateUser()
	 * -- ilEventParticipants->_updateParticipation()
	 * -- ilEventParticipants->_register()
	 * -- ilEventParticipants->_unregister()
	 * -- ilExerciseMembers->assignMember()
	 * -- ilExerciseMembers->deassignMember()
	 * -- ilExerciseMembers->ilClone()
	 * -- ilExerciseMembers->writeStatus()
	 * -- ilExerciseMembers->writeReturned()
	 * -- ilSCORM13Player->writeGObjective()
	 * -- ilObjSCORM2004LearningModule->deleteTrackingDataOfUsers()
	 * -- ilObjSCORM2004LearningModule->importSuccess()
	 * -- ilObjSCORM2004LearningModuleGUI->confirmedDeleteTracking()
	 * -- ilSCORM13Player->removeCMIData()
	 * -- ilSCORM13Player->setCMIData()
	 * -- ilObjSCORMLearningModule->importSuccess()
	 * -- ilObjSCORMLearningModule->importRaw()
	 * -- ilObjSCORMLearningModuleGUI->confirmedDelete()
	 * -- ilObjSCORMLearningModuleGUI->decreaseAttempt()
	 * -- ilObjSCORMTracking->store()
	 * -- ilObjSCORMTracking-> _insertTrackData()
	 * -- ilSCORMPresentationGUI->increaseAttempt()
	 * -- ilSCORMPresentationGUI->save_module_version()
	 * -- ilTestScoringGUI->setPointsManual()
	 * -- ilTestSession->increaseTestPass()
	 * -- ilTestSession->saveToDb()
	 *
	 * - ilLPStatusWrapper::_refreshStatus($a_ojb_id); aufgenommen in:
	 * -- ilCourseObjective->add()
	 * -- ilCourseObjective->delete()
	 * -- ilCourseObjective->deleteAll()
	 * -- ilExerciseMembers->delete()
	 * -- ilSCORM13Package->removeCMIData()
	 * -- ilAICCCourse->delete()
	 * -- ilAICCUnit->delete()
	 * -- ilObjAICCLearningModule->delete()
	 * -- ilSCORMItem->delete()
	 * -- ilLPStatusWrapper->update()
	 * -- ilLPListOfSettingsGUI->assign()
	 * -- ilLPListOfSettingsGUI->deassign()
	 * -- ilLPListOfSettingsGUI->groupMaterials()
	 * -- ilLPListOfSettingsGUI->releaseMaterials()
	 * -- ilObjTestGUI->confirmDeleteAllUserResultsObject @TODO move to ilObjTest but this can ba called for each single question
	 * -- ilConditionHandlerInterface->updateCondition()
	 *
	 * - external time/access values for read events
	 *   ilChangeEvent::_recordReadEvent($a_obj_id, $a_user_id, false, $attempts, $time);
	 * -- ilObjSCORMTracking->_syncReadEvent in ilObjSCORMTracking->store() (add to refresh)
	 * -- ilSCORM2004Tracking->_syncReadEvent in ilSCORM13Player->setCMIData()
	 */

	/**
	 * Write status
	 *
	 * @param
	 * @return
	 */
	function _updateStatus($a_obj_id, $a_usr_id, $a_obj = null, $a_percentage = false, $a_no_raise = false)
	{
//global $ilLog;
//$ilLog->write("ilLPStatus-_updateStatus-");

		$status = $this->determineStatus($a_obj_id, $a_usr_id, $a_obj);
		$percentage = $this->determinePercentage($a_obj_id, $a_usr_id, $a_obj);
		ilLPStatus::writeStatus($a_obj_id, $a_usr_id, $status, $percentage);
		
		if(!$a_no_raise)
		{
			global $ilAppEventHandler;
			$ilAppEventHandler->raise("Services/Tracking", "updateStatus", array(
				"obj_id" => $a_obj_id,
				"usr_id" => $a_usr_id,
				"status" => $status,
				"percentage" => $percentage
				));
		}
	}
	
	/**
	 * Determine percentage
	 *
	 * @param
	 * @return
	 */
	function determinePercentage($a_obj_id, $a_usr_id, $a_obj = null)
	{
		return false;
	}

	/**
	 * Determine status
	 *
	 * @param
	 * @return
	 */
	function determineStatus($a_obj_id, $a_usr_id, $a_obj = null)
	{
		return false;
	}
	
		
	/**
	 * This function checks whether the status for a given number of users is dirty and must be
	 * recalculated. "Missing" records are not inserted! 
	 *
	 * @param
	 * @return
	 */
	static function checkStatusForObject($a_obj_id, $a_users = false)
	{
		global $ilDB;

//@todo: there maybe the need to add extra handling for sessions here, since the
// "in progress" status is time dependent here. On the other hand, if they registered
// to the session, they already accessed the course and should have a "in progress"
// anyway. But the status on the session itself may not be correct.

		$sql = "SELECT usr_id FROM ut_lp_marks WHERE ".
			" obj_id = ".$ilDB->quote($a_obj_id, "integer")." AND ".
			" status_dirty = ".$ilDB->quote(1, "integer");
		if(is_array($a_users) && count($a_users) > 0)
		{
			$sql .= " AND ".$ilDB->in("usr_id", $a_users, false, "integer");	
		}			
		$set = $ilDB->query($sql);
		$dirty = false;
		if ($rec = $ilDB->fetchAssoc($set))
		{
			$dirty = true;
		}

		// check if any records are missing
		$missing = false;
		if (!$dirty && is_array($a_users) && count($a_users) > 0)
		{
			$set = $ilDB->query("SELECT count(usr_id) cnt FROM ut_lp_marks WHERE ".
				" obj_id = ".$ilDB->quote($a_obj_id, "integer")." AND ".
				$ilDB->in("usr_id", $a_users, false, "integer"));
			$r = $ilDB->fetchAssoc($set);
			if ($r["cnt"] < count($a_users))
			{
				$missing = true;
			}
		}

		// refresh status, if records are dirty or missing
		if ($dirty || $missing)
		{
			$class = ilLPStatusFactory::_getClassById($a_obj_id);
			$trac_obj = new $class($a_obj_id);
			$trac_obj->refreshStatus($a_obj_id, $a_users);
		}
	}
	
	/**
	 * Refresh status
	 *
	 * @param
	 * @return
	 */
	function refreshStatus($a_obj_id, $a_users = null)
	{
		include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
		$not_attempted = ilLPStatusWrapper::_getNotAttempted($a_obj_id);
		foreach ($not_attempted as $user_id)
		{
			$percentage = $this->determinePercentage($a_obj_id, $user_id);
			ilLPStatus::writeStatus($a_obj_id, $user_id, LP_STATUS_NOT_ATTEMPTED_NUM, $percentage, true);
		}
		$in_progress = ilLPStatusWrapper::_getInProgress($a_obj_id);
		foreach ($in_progress as $user_id)
		{
			$percentage = $this->determinePercentage($a_obj_id, $user_id);
			ilLPStatus::writeStatus($a_obj_id, $user_id, LP_STATUS_IN_PROGRESS_NUM, $percentage, true);
		}
		$completed = ilLPStatusWrapper::_getCompleted($a_obj_id);
		foreach ($completed as $user_id)
		{
			$percentage = $this->determinePercentage($a_obj_id, $user_id);
			ilLPStatus::writeStatus($a_obj_id, $user_id, LP_STATUS_COMPLETED_NUM, $percentage, true);
		}
		$failed = ilLPStatusWrapper::_getFailed($a_obj_id);
		foreach ($failed as $user_id)
		{
			$percentage = $this->determinePercentage($a_obj_id, $user_id);
			ilLPStatus::writeStatus($a_obj_id, $user_id, LP_STATUS_FAILED_NUM, $percentage, true);
		}
		if($a_users)
		{		
			$missing_users = array_diff($a_users, $not_attempted+$in_progress+$completed+$failed);			
			if($missing_users)
			{
				foreach ($missing_users as $user_id)
				{		
					ilLPStatusWrapper::_updateStatus($a_obj_id, $user_id);
				}
			}
		}
	}

	/**
	 * Write status for user and object
	 *
	 * @param
	 * @return
	 */
	static function writeStatus($a_obj_id, $a_user_id, $a_status, $a_percentage = false, $a_force_per = false)
	{
		global $ilDB;
				
		$update_collections = false;

		// get status in DB
		$set = $ilDB->query("SELECT usr_id,status,status_dirty FROM ut_lp_marks WHERE ".
			" obj_id = ".$ilDB->quote($a_obj_id, "integer")." AND ".
			" usr_id = ".$ilDB->quote($a_user_id, "integer")
			);
		$rec = $ilDB->fetchAssoc($set);	
		
		// update
		if ($rec)
		{
			// status has changed: update
			if ($rec["status"] != $a_status)
			{
				$ret = $ilDB->manipulate("UPDATE ut_lp_marks SET ".
					" status = ".$ilDB->quote($a_status, "integer").",".
					" status_changed = ".$ilDB->now().",".
					" status_dirty = ".$ilDB->quote(0, "integer").
					" WHERE usr_id = ".$ilDB->quote($a_user_id, "integer").
					" AND obj_id = ".$ilDB->quote($a_obj_id, "integer")
					);
				if ($ret != 0)
				{
					$update_collections = true;
				}
			}
			// status has not changed: reset dirty flag
			else if ($rec["status_dirty"])
			{
				$ilDB->manipulate("UPDATE ut_lp_marks SET ".
					" status_dirty = ".$ilDB->quote(0, "integer").
					" WHERE usr_id = ".$ilDB->quote($a_user_id, "integer").
					" AND obj_id = ".$ilDB->quote($a_obj_id, "integer")
					);
			}
		}
		// insert
		else
		{
			$ilDB->manipulate("INSERT INTO ut_lp_marks ".
				"(status, status_changed, usr_id, obj_id, status_dirty) VALUES (".
				$ilDB->quote($a_status, "integer").",".
				$ilDB->now().",".
				$ilDB->quote($a_user_id, "integer").",".
				$ilDB->quote($a_obj_id, "integer").",".
				$ilDB->quote(0, "integer").
				")");
			$update_collections = true;
		}

		// update percentage
		if ($a_percentage !== false || $a_force_per)
		{
			$a_percentage = max(0, (int) $a_percentage);
			$a_percentage = min(100, $a_percentage);
			$ret = $ilDB->manipulate("UPDATE ut_lp_marks SET ".
				" percentage = ".$ilDB->quote($a_percentage, "integer").
				" WHERE usr_id = ".$ilDB->quote($a_user_id, "integer").
				" AND obj_id = ".$ilDB->quote($a_obj_id, "integer")
				);
		}

		// update collections
		if ($update_collections)
		{
			$set = $ilDB->query("SELECT ut_lp_collections.obj_id obj_id FROM ".
				"object_reference JOIN ut_lp_collections ON ".
				"(object_reference.obj_id = ".$ilDB->quote($a_obj_id, "integer").
				" AND object_reference.ref_id = ut_lp_collections.item_id)");
			while ($rec = $ilDB->fetchAssoc($set))
			{
				if (in_array(ilObject::_lookupType($rec["obj_id"]), array("crs", "grp", "fold")))
				{
					include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
					ilLPStatusWrapper::_updateStatus($rec["obj_id"], $a_user_id);
				}
			}
		}
	}
	
	/**
	 * This function shoudl be clalled for normal "read events".
	 * The "in progress" status is only written,
	 * if current status is "NOT ATTEMPTED"
	 */
	static function setInProgressIfNotAttempted($a_obj_id, $a_user_id)
	{
		global $ilDB;

		$set = $ilDB->query("SELECT usr_id FROM ut_lp_marks WHERE ".
			" obj_id = ".$ilDB->quote($a_obj_id, "integer")." AND ".
			" usr_id = ".$ilDB->quote($a_user_id, "integer")
			);

		$update_collections = false;
		if ($rec  = $ilDB->fetchAssoc($set))
		{
			$ret = $ilDB->manipulate("UPDATE ut_lp_marks SET ".
				" status = ".$ilDB->quote(LP_STATUS_IN_PROGRESS_NUM, "integer").",".
				" status_changed = ".$ilDB->now().",".
				" status_dirty = ".$ilDB->quote(0, "integer").
				" WHERE usr_id = ".$ilDB->quote($a_user_id, "integer").
				" AND obj_id = ".$ilDB->quote($a_obj_id, "integer").
				" AND status = ".$ilDB->quote(LP_STATUS_NOT_ATTEMPTED_NUM, "integer")
				);
			if ($ret != 0)
			{
				$update_collections = true;
			}
		}
		else
		{
			$ilDB->manipulate("INSERT INTO ut_lp_marks ".
				"(status, status_changed, usr_id, obj_id, status_dirty) VALUES (".
				$ilDB->quote(LP_STATUS_IN_PROGRESS_NUM, "integer").",".
				$ilDB->now().",".
				$ilDB->quote($a_user_id, "integer").",".
				$ilDB->quote($a_obj_id, "integer").",".
				$ilDB->quote(0, "integer").
				")");
			$update_collections = true;
		}

		// update collections
		if ($update_collections)
		{
			$set = $ilDB->query("SELECT ut_lp_collections.obj_id obj_id FROM ".
				"object_reference JOIN ut_lp_collections ON ".
				"(object_reference.obj_id = ".$ilDB->quote($a_obj_id, "integer").
				" AND object_reference.ref_id = ut_lp_collections.item_id)");
			while ($rec = $ilDB->fetchAssoc($set))
			{
				if (in_array(ilObject::_lookupType($rec["obj_id"]), array("crs", "grp", "fold")))
				{
					include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
					ilLPStatusWrapper::_updateStatus($rec["obj_id"], $a_user_id);
				}
			}
		}
	}
	
	/**
	 * Sets all status to dirty. For testing puproses.
	 *
	 * @param
	 * @return
	 */
	static function setAllDirty()
	{
		global $ilDB;
	
		$ilDB->manipulate("UPDATE ut_lp_marks SET ".
			" status_dirty = ".$ilDB->quote(1, "integer")
			);
		
	}

	/**
	 * Sets status of an object to dirty.
	 *
	 * @param	integer		object id
	 * @return
	 */
	static function setDirty($a_obj_id)
	{
		global $ilDB;
	
		$ilDB->manipulate("UPDATE ut_lp_marks SET ".
			" status_dirty = ".$ilDB->quote(1, "integer").
			" WHERE obj_id = ".$ilDB->quote($a_obj_id, "integer")
			);
	}
	
	/**
	 * Lookup status
	 *
	 * @param int $a_obj_id object id
	 * @param int $a_user_id user id
	 */
	function _lookupStatus($a_obj_id, $a_user_id)
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT status FROM ut_lp_marks WHERE ".
			" status_dirty = ".$ilDB->quote(0, "integer").
			" AND usr_id = ".$ilDB->quote($a_user_id, "integer").
			" AND obj_id = ".$ilDB->quote($a_obj_id, "integer")
			);
		if ($rec = $ilDB->fetchAssoc($set))
		{
			return $rec["status"];
		}
		else
		{
			include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
			ilLPStatusWrapper::_updateStatus($a_obj_id, $a_user_id); 
			$set = $ilDB->query("SELECT status FROM ut_lp_marks WHERE ".
				" status_dirty = ".$ilDB->quote(0, "integer").
				" AND usr_id = ".$ilDB->quote($a_user_id, "integer").
				" AND obj_id = ".$ilDB->quote($a_obj_id, "integer")
				);
			if ($rec = $ilDB->fetchAssoc($set))
			{
				return $rec["status"];
			}
		}
	}
		
	/**
	 * Lookup status changed
	 *
	 * @param int $a_obj_id object id
	 * @param int $a_user_id user id
	 */
	function _lookupStatusChanged($a_obj_id, $a_user_id)
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT status_changed FROM ut_lp_marks WHERE ".
			" status_dirty = ".$ilDB->quote(0, "integer").
			" AND usr_id = ".$ilDB->quote($a_user_id, "integer").
			" AND obj_id = ".$ilDB->quote($a_obj_id, "integer")
			);
		if ($rec = $ilDB->fetchAssoc($set))
		{
			return $rec["status_changed"];
		}
		else
		{
			include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
			ilLPStatusWrapper::_updateStatus($a_obj_id, $a_user_id); 
			$set = $ilDB->query("SELECT status_changed FROM ut_lp_marks WHERE ".
				" status_dirty = ".$ilDB->quote(0, "integer").
				" AND usr_id = ".$ilDB->quote($a_user_id, "integer").
				" AND obj_id = ".$ilDB->quote($a_obj_id, "integer")
				);
			if ($rec = $ilDB->fetchAssoc($set))
			{
				return $rec["status_changed"];
			}
		}
	}
	
	/**
	 * Get users with given status for object
	 * 
	 * @param int $a_obj_id
	 * @param int $a_status
	 * @param array $a_user_ids
	 * @return array 
	 */
	protected static function _lookupStatusForObject($a_obj_id, $a_status, $a_user_ids = null)
	{
		global $ilDB;
		
		$sql = "SELECT usr_id, status, status_dirty FROM ut_lp_marks".
			" WHERE obj_id = ".$ilDB->quote($a_obj_id, "integer").
			" AND status = ".$ilDB->quote($a_status, "integer");
		if($a_user_ids)
		{
			$sql .= " AND ".$ilDB->in("usr_id", $a_user_ids, "", "integer");
		}				
		
		$set = $ilDB->query($sql);
		$res = array();
		while($rec = $ilDB->fetchAssoc($set))
		{			
			if($res["status_dirty"])
			{
				// update status and check again
				if(self::_lookupStatus($a_obj_id, $rec["usr_id"]) != $a_status)
				{
					continue;
				}
			}	
			$res[] = $rec["usr_id"];
		}
		
		return $res;
	}			
	
	/**
	 * Get completed users for object
	 * 
	 * @param int $a_obj_id
	 * @param array $a_user_ids
	 * @return array 
	 */
	public static function _lookupCompletedForObject($a_obj_id, $a_user_ids = null)
	{
		return self::_lookupStatusForObject($a_obj_id, LP_STATUS_COMPLETED_NUM, $a_user_ids);
	}
	
	/**
	 * Get failed users for object
	 * 
	 * @param int $a_obj_id
	 * @param array $a_user_ids
	 * @return array 
	 */
	public static function _lookupFailedForObject($a_obj_id, $a_user_ids = null)
	{
		return self::_lookupStatusForObject($a_obj_id, LP_STATUS_FAILED_NUM, $a_user_ids);
	}
	
	/**
	 * Get in progress users for object
	 * 
	 * @param int $a_obj_id
	 * @param array $a_user_ids
	 * @return array 
	 */
	public static function _lookupInProgressForObject($a_obj_id, $a_user_ids = null)
	{
		return self::_lookupStatusForObject($a_obj_id, LP_STATUS_IN_PROGRESS_NUM, $a_user_ids);
	}
}	
?>