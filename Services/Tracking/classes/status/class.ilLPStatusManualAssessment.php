<?php


require_once 'Services/Tracking/classes/class.ilLPStatus.php';
require_once 'Modules/ManualAssessment/classes/LearningProgress/class.ilManualAssessmentLPInterface.php';
require_once 'Modules/ManualAssessment/classes/Members/class.ilManualAssessmentMembers.php';

class ilLPStatusManualAssessment extends ilLPStatus {

	static function _getCountInProgress($a_obj_id) {
		return count(self::_getInProgress($a_obj_id));
	}

	static function _getInProgress($a_obj_id) {
		return ilManualAssessmentLPInterface::getMembersHavingStatusIn($a_obj_id, 
			ilManualAssessmentMembers::LP_IN_PROGRESS);
	}

	static function _getCountCompleted($a_obj_id) {
		return count(self::_getCompleted($a_obj_id));
	}

	static function _getCompleted($a_obj_id) {
		return ilManualAssessmentLPInterface::getMembersHavingStatusIn($a_obj_id, 
			ilManualAssessmentMembers::LP_COMPLETED);
	}

	static function _getCountFailed() {
		return count(self::_getFailed($a_obj_id));
	}

	static function _getFailed($a_obj_id) {
		return ilManualAssessmentLPInterface::getMembersHavingStatusIn($a_obj_id, 
			ilManualAssessmentMembers::LP_FAILED);
	}

	function determineStatus($a_obj_id, $a_user_id, $a_obj = null) {
		switch ((string)ilManualAssessmentLPInterface::determineStatusOfMember($a_obj_id,$a_user_id)) {
			case (string)ilManualAssessmentMembers::LP_IN_PROGRESS:
				return self::LP_STATUS_IN_PROGRESS_NUM;
			case (string)ilManualAssessmentMembers::LP_FAILED: 
				return self::LP_STATUS_FAILED_NUM;
			case (string)ilManualAssessmentMembers::LP_COMPLETED:			
				return self::LP_STATUS_COMPLETED_NUM;
			default:
				return self::LP_STATUS_NOT_ATTEMPTED_NUM;
		}
	}
}