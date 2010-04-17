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
	 * - lm: ilLPStatusManual (st nr, p, t, m ok), ilLPStatusVisits (st, nr, p, t, m ok),
	 *       ilLPStatusTypicalLearningTime (st, nr, p, t, m ok)
	 * - dbk: ilLPStatusManual (st, nr, p, t, m ok)
	 * - htlm: ilLPStatusManual (st, nr, p, t, m ok) (but mark handling different than lm/dbk)
	 * - crs: ilLPStatusManualByTutor (status ok), ilLPStatusObjectives (status ok), ilLPStatusCollection
	 * - grp: ilLPStatusManualByTutor, ilLPStatusCollection
	 * - fold: ilLPStatusCollection
	 * - session: ilLPStatusEvent (status ok)
	 * - exercise: ilLPStatusExerciseReturned (status ok)
	 * - scorm: ilLPStatusSCORM (status ok), ilLPStatusSCORMPackage (status ok)
	 * - tst: ilLPStatusTestFinished (), ilLPStatusTestPassed (status ok)
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
	 *
	 */

	/**
	 * Write status
	 *
	 * @param
	 * @return
	 */
	function _updateStatus($a_obj_id, $a_usr_id, $a_obj = null)
	{
		$status = $this->determineStatus($a_obj_id, $a_usr_id, $a_obj);
		$percentage = $this->determinePercentage($a_obj_id, $a_usr_id, $a_obj);
		ilLPStatus::writeStatus($a_obj_id, $a_usr_id, $status, $percentage);
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
	 * This function checks whether the status for a given number of users is dirty and must be
	 * recalculated. "Missing" records are not inserted! 
	 *
	 * @param
	 * @return
	 */
	static function checkStatusForObject($a_obj_id)
	{
		global $ilDB;

//@todo: there maybe the need to add extra handling for sessions here, since the
// "in progress" status is time dependent here. On the other hand, if they registered
// to the session, they already accessed the course and should have a "in progress"
// anyway. But the status on the session itself may not be correct.

		$set = $ilDB->query("SELECT usr_id FROM ut_lp_marks WHERE ".
			" obj_id = ".$ilDB->quote($a_obj_id, "integer")." AND ".
			" status_dirty = ".$ilDB->quote(1, "integer")
			);
		$first = true;
		while ($rec = $ilDB->fetchAssoc($set))
		{
			if ($first)
			{
				include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
				$not_attempted = ilLPStatusWrapper::_getNotAttempted($a_obj_id);
				$in_progress = ilLPStatusWrapper::_getInProgress($a_obj_id);
				$completed = ilLPStatusWrapper::_getCompleted($a_obj_id);
				$failed = ilLPStatusWrapper::_getFailed($a_obj_id);
				$first = false;
			}
			if (in_array($rec["usr_id"], $not_attempted))
			{
				ilLPStatus::writeStatus($a_obj_id, $rec["usr_id"], LP_STATUS_NOT_ATTEMPTED_NUM);
			}
			if (in_array($rec["usr_id"], $in_progress))
			{
				ilLPStatus::writeStatus($a_obj_id, $rec["usr_id"], LP_STATUS_IN_PROGRESS_NUM);
			}
			if (in_array($rec["usr_id"], $completed))
			{
				ilLPStatus::writeStatus($a_obj_id, $rec["usr_id"], LP_STATUS_COMPLETED_NUM);
			}
			if (in_array($rec["usr_id"], $failed))
			{
				ilLPStatus::writeStatus($a_obj_id, $rec["usr_id"], LP_STATUS_FAILED_NUM);
			}
		}
	}
	
	/**
	 * Refresh status
	 *
	 * @param
	 * @return
	 */
	function refreshStatus($a_obj_id)
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

		$set = $ilDB->query("SELECT usr_id FROM ut_lp_marks WHERE ".
			" obj_id = ".$ilDB->quote($a_obj_id, "integer")." AND ".
			" usr_id = ".$ilDB->quote($a_user_id, "integer")
			);
		
		$update_parents = false;
		if ($rec  = $ilDB->fetchAssoc($set))
		{
			$ret = $ilDB->manipulate("UPDATE ut_lp_marks SET ".
				" status = ".$ilDB->quote($a_status, "integer").",".
				" status_changed = ".$ilDB->now().",".
				" status_dirty = ".$ilDB->quote(0, "integer").
				" WHERE usr_id = ".$ilDB->quote($a_user_id, "integer").
				" AND obj_id = ".$ilDB->quote($a_obj_id, "integer").
				" AND status <> ".$ilDB->quote($a_status, "integer")
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
				include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
				ilLPStatusWrapper::_updateStatus($rec["obj_id"], $a_user_id);
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
		$update_parents = false;
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
				include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
				ilLPStatusWrapper::_updateStatus($rec["obj_id"], $a_user_id);
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

}	
?>