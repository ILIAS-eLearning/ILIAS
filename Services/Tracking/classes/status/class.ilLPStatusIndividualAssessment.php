<?php


require_once 'Services/Tracking/classes/class.ilLPStatus.php';
require_once 'Modules/IndividualAssessment/classes/LearningProgress/class.ilIndividualAssessmentLPInterface.php';
require_once 'Modules/IndividualAssessment/classes/Members/class.ilIndividualAssessmentMembers.php';

class ilLPStatusIndividualAssessment extends ilLPStatus
{
    public static function _getNotAttempted($a_obj_id)
    {
        return ilIndividualAssessmentLPInterface::getMembersHavingStatusIn(
            $a_obj_id,
            ilIndividualAssessmentMembers::LP_NOT_ATTEMPTED
        );
    }

    public static function _getCountNotAttempted($a_obj_id)
    {
        return count(self::_getNotAttempted($a_obj_id));
    }

    public static function _getCountInProgress($a_obj_id)
    {
        return count(self::_getInProgress($a_obj_id));
    }

    public static function _getInProgress($a_obj_id)
    {
        return ilIndividualAssessmentLPInterface::getMembersHavingStatusIn(
            $a_obj_id,
            ilIndividualAssessmentMembers::LP_IN_PROGRESS
        );
    }

    public static function _getCountCompleted($a_obj_id)
    {
        return count(self::_getCompleted($a_obj_id));
    }

    public static function _getCompleted($a_obj_id)
    {
        return ilIndividualAssessmentLPInterface::getMembersHavingStatusIn(
            $a_obj_id,
            ilIndividualAssessmentMembers::LP_COMPLETED
        );
    }

    public static function _getCountFailed()
    {
        return count(self::_getFailed($a_obj_id));
    }

    public static function _getFailed($a_obj_id)
    {
        return ilIndividualAssessmentLPInterface::getMembersHavingStatusIn(
            $a_obj_id,
            ilIndividualAssessmentMembers::LP_FAILED
        );
    }


    public function determineStatus($a_obj_id, $a_user_id, $a_obj = null)
    {
        switch ((string) ilIndividualAssessmentLPInterface::determineStatusOfMember($a_obj_id, $a_user_id)) {
            case (string) ilIndividualAssessmentMembers::LP_NOT_ATTEMPTED:
                return self::LP_STATUS_NOT_ATTEMPTED_NUM;
            case (string) ilIndividualAssessmentMembers::LP_IN_PROGRESS:
                return self::LP_STATUS_IN_PROGRESS_NUM;
            case (string) ilIndividualAssessmentMembers::LP_FAILED:
                return self::LP_STATUS_FAILED_NUM;
            case (string) ilIndividualAssessmentMembers::LP_COMPLETED:
                return self::LP_STATUS_COMPLETED_NUM;
            default:
                return self::LP_STATUS_NOT_ATTEMPTED_NUM;
        }
    }
}
