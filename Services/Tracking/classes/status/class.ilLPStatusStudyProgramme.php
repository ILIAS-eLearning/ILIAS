<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* @author Richard Klees <richard.klees@concepts-and-training.de>
*
* @version $Id: class.ilLPStatusCollectionManual.php 40252 2013-03-01 12:21:49Z jluetzen $
*
* @package ilias-tracking
*
*/

include_once './Services/Tracking/classes/class.ilLPStatus.php';

class ilLPStatusStudyProgramme extends ilLPStatus
{
	static function _getCountInProgress($a_obj_id) {
		return count($this->_getInProgress($a_obj_id));
	}
	
	static function _getInProgress($a_obj_id)
	{
		require_once("Modules/StudyProgramme/classes/class.ilObjStudyProgramme.php");
		$prg = new ilObjStudyProgramme($a_obj_id, false);
		return $prg->getIdsOfUsersWithNotCompletedAndRelevantProgress();
	}
	
	static function _getCountCompleted($a_obj_id) {
		return count($this->_getCompleted($a_obj_id));
	}
	
	static function _getCompleted($a_obj_id)
	{		
		require_once("Modules/StudyProgramme/classes/class.ilObjStudyProgramme.php");
		$prg = new ilObjStudyProgramme($a_obj_id, false);
		return $prg->getIdsOfUsersWithCompletedProgress();
	}
	
	function determineStatus($a_obj_id, $a_user_id, $a_obj = null)
	{
		require_once("Modules/StudyProgramme/classes/class.ilObjStudyProgramme.php");
		$prg = new ilObjStudyProgramme($a_obj_id, false);
		$progresses = $prg->getProgressesOf($a_user_id);
		
		$successful = false;
		$relevant = false;
		
		foreach ($progresses as $progress) {
			if ($progress->isSuccessful()) {
				$successful = true;
				break;
			}
			if ($progress->isRelevant()) {
				$relevant = true;
			}
		}
		
		if ($successful) {
			return ilLPStatus::LP_STATUS_COMPLETED_NUM;
		}
		if ($relevant) {
			return ilLPStatus::LP_STATUS_IN_PROGRESS_NUM;
		}
		return ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
	}
}

?>