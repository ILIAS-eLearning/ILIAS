<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Tracking class, things may be put somewhere else in the future.
 *
 * Learning progress:
 * - lm: ilLPStatusManual (status ok), ilLPStatusVisits (status ok, percentage missing), ilLPStatusTypicalLearningTime (status ok)
 * - dbk: ilLPStatusManual (status ok)
 * - htlm: ilLPStatusManual (status ok)
 * - crs: ilLPStatusManualByTutor (status ok), ilLPStatusObjectives, ilLPStatusCollection
 * - grp: ilLPStatusManualByTutor, ilLPStatusCollection
 * - fold: ilLPStatusCollection
 * - session: ilLPStatusEvent
 * - exercise: ilLPStatusExerciseReturned
 * - scorm: ilLPStatusSCORM, ilLPStatusSCORMPackage
 * - tst: ilLPStatusTestFinished, ilLPStatusTestPassed
 *
 * Added determine Status to:
 * - ilLPStatusManual
 * - ilLPStatusVisits
 * - ilLPStatusTypicalLearningTime
 * - ilLPStatusManualByTutor
 *
 * Updating the status:
 * - ilLPStatusWrapper::_updateStatus($a_obj_id, $a_user_id); added to:
 * -- ilLearningProgress->_tracProgress()
 * -- ilInfoScreenGUI->saveProgress()
 * -- ilLPListOfObjectsGUI->updateUser()
 * -- ilCourseObjectiveResult->reset()
 * -- ilCourseObjectiveResult->__updatePassed()
 *
 * - ilLPStatusWrapper::_setDirty($a_ojb_id); aufgenommen in:
 * -- ilCourseObjective->add()
 * -- ilCourseObjective->delete()
 * -- ilCourseObjective->deleteAll()
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesTracking
 */
class ilTracking2
{
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

//@todo: insert at least records that are in read_event but not in ut_lp_marks

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
				ilTracking2::writeStatus($a_obj_id, $rec["usr_id"], LP_STATUS_NOT_ATTEMPTED_NUM);
			}
			if (in_array($rec["usr_id"], $in_progress))
			{
				ilTracking2::writeStatus($a_obj_id, $rec["usr_id"], LP_STATUS_IN_PROGRESS_NUM);
			}
			if (in_array($rec["usr_id"], $completed))
			{
				ilTracking2::writeStatus($a_obj_id, $rec["usr_id"], LP_STATUS_COMPLETED_NUM);
			}
			if (in_array($rec["usr_id"], $failed))
			{
				ilTracking2::writeStatus($a_obj_id, $rec["usr_id"], LP_STATUS_FAILED_NUM);
			}
		}
	}

	/**
	 * Write status for user and object
	 *
	 * @param
	 * @return
	 */
	static function writeStatus($a_obj_id, $a_user_id, $a_status)
	{
		global $ilDB;

		$set = $ilDB->query("SELECT usr_id FROM ut_lp_marks WHERE ".
			" obj_id = ".$ilDB->quote($a_obj_id, "integer")." AND ".
			" usr_id = ".$ilDB->quote($a_user_id, "integer")
			);
		if ($rec  = $ilDB->fetchAssoc($set))
		{
			$ilDB->manipulate("UPDATE ut_lp_marks SET ".
				" status = ".$ilDB->quote($a_status, "integer").",".
				" status_changed = ".$ilDB->now().",".
				" status_dirty = ".$ilDB->quote(0, "integer").
				" WHERE usr_id = ".$ilDB->quote($a_user_id, "integer").
				" AND obj_id = ".$ilDB->quote($a_obj_id, "integer").
				" AND status <> ".$ilDB->quote($a_status, "integer")
				);
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