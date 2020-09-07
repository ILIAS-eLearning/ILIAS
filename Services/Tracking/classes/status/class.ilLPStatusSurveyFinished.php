<?php

// patch-begin svy_lp

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Tracking/classes/class.ilLPStatus.php';

/**
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
*
* @version $Id: class.ilLPStatusSurveyFinished.php 23880 2010-05-14 15:01:56Z jluetzen $
*
* @ingroup	ServicesTracking
*
*/
class ilLPStatusSurveyFinished extends ilLPStatus
{
    public static function _getInProgress($a_obj_id)
    {
        return self::getParticipants($a_obj_id);
    }
    
    public static function _getCompleted($a_obj_id)
    {
        return self::getParticipants($a_obj_id, true);
    }

    /**
     * Determine status
     *
     * @param	integer		object id
     * @param	integer		user id
     * @param	object		object (optional depends on object type)
     * @return	integer		status
     */
    public function determineStatus($a_obj_id, $a_user_id, $a_obj = null)
    {
        $survey_id = self::getSurveyId($a_obj_id);
        if (!$survey_id) {
            return;
        }
        
        $status = ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
        
        include_once './Modules/Survey/classes/class.ilObjSurveyAccess.php';
        if (ilObjSurveyAccess::_isSurveyParticipant($a_user_id, $survey_id)) {
            $status = ilLPStatus::LP_STATUS_IN_PROGRESS_NUM;
            
            if (ilObjSurveyAccess::_lookupFinished($a_obj_id, $a_user_id)) {
                $status = ilLPStatus::LP_STATUS_COMPLETED_NUM;
            }
        }

        return $status;
    }
        
    protected static function getSurveyId($a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $set = $ilDB->query("SELECT survey_id FROM svy_svy" .
            " WHERE obj_fi = " . $ilDB->quote($a_obj_id));
        $row = $ilDB->fetchAssoc($set);
        return (int) $row["survey_id"];
    }
    
    public static function getParticipants($a_obj_id, $a_only_finished = false)
    {
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
            $res[] = $row["user_fi"];
        }
        
        return $res;
    }
}

// patch-end svy_lp
