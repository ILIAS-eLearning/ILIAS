<?php declare(strict_types=0);

// patch-begin svy_lp

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author     Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup    ServicesTracking
 */
class ilLPStatusSurveyFinished extends ilLPStatus
{
    public static function _getInProgress(int $a_obj_id) : array
    {
        return self::getParticipants($a_obj_id);
    }

    public static function _getCompleted(int $a_obj_id) : array
    {
        return self::getParticipants($a_obj_id, true);
    }

    public function determineStatus(
        int $a_obj_id,
        int $a_usr_id,
        object $a_obj = null
    ) : int {
        $survey_id = self::getSurveyId($a_obj_id);
        if (!$survey_id) {
            return ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
        }
        $status = ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;

        if (ilObjSurveyAccess::_isSurveyParticipant($a_usr_id, $survey_id)) {
            $status = ilLPStatus::LP_STATUS_IN_PROGRESS_NUM;

            if (ilObjSurveyAccess::_lookupFinished($a_obj_id, $a_usr_id)) {
                $status = ilLPStatus::LP_STATUS_COMPLETED_NUM;
            }
        }
        return $status;
    }

    protected static function getSurveyId(int $a_obj_id) : int
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $set = $ilDB->query(
            "SELECT survey_id FROM svy_svy" .
            " WHERE obj_fi = " . $ilDB->quote($a_obj_id)
        );
        $row = $ilDB->fetchAssoc($set);
        return (int) $row["survey_id"];
    }

    public static function getParticipants(
        int $a_obj_id,
        bool $a_only_finished = false
    ) : array {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $res = array();
        $survey_id = self::getSurveyId($a_obj_id);
        if (!$survey_id) {
            return $res;
        }

        $sql = "SELECT user_fi FROM svy_finished fin" .
            " WHERE fin.survey_fi = " . $ilDB->quote($survey_id, "integer");

        if ($a_only_finished) {
            $sql .= " AND fin.state = " . $ilDB->quote(1, "integer");
        }

        $set = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($set)) {
            $res[] = (int) $row["user_fi"];
        }
        return $res;
    }
}

// patch-end svy_lp
