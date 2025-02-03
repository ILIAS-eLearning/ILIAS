<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=0);

class ilLPStatusIndividualAssessment extends ilLPStatus
{
    public static function _getNotAttempted(int $a_obj_id): array
    {
        return ilIndividualAssessmentLPInterface::getMembersHavingStatusIn(
            $a_obj_id,
            ilIndividualAssessmentMembers::LP_NOT_ATTEMPTED
        );
    }

    public static function _getCountNotAttempted(int $a_obj_id): int
    {
        return count(self::_getNotAttempted($a_obj_id));
    }

    public static function _getCountInProgress(int $a_obj_id): int
    {
        return count(self::_getInProgress($a_obj_id));
    }

    public static function _getInProgress(int $a_obj_id): array
    {
        return ilIndividualAssessmentLPInterface::getMembersHavingStatusIn(
            $a_obj_id,
            ilIndividualAssessmentMembers::LP_IN_PROGRESS
        );
    }

    public static function _getCountCompleted(int $a_obj_id): int
    {
        return count(self::_getCompleted($a_obj_id));
    }

    public static function _getCompleted(int $a_obj_id): array
    {
        return ilIndividualAssessmentLPInterface::getMembersHavingStatusIn(
            $a_obj_id,
            ilIndividualAssessmentMembers::LP_COMPLETED
        );
    }

    public static function _getCountFailed(int $a_obj_id): int
    {
        return count(self::_getFailed($a_obj_id));
    }

    public static function _getFailed(int $a_obj_id): array
    {
        return ilIndividualAssessmentLPInterface::getMembersHavingStatusIn(
            $a_obj_id,
            ilIndividualAssessmentMembers::LP_FAILED
        );
    }

    public function determineStatus(
        int $a_obj_id,
        int $a_usr_id,
        ?object $a_obj = null
    ): int {
        switch ((string) ilIndividualAssessmentLPInterface::determineStatusOfMember(
            $a_obj_id,
            $a_usr_id
        )) {
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
