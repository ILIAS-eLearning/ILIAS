<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Tracking/classes/class.ilLPStatus.php';
include_once './Services/MetaData/classes/class.ilMDEducational.php'; // #15556

/**
 * @author Stefan Meyer <meyer@leifos.com>
 *
 * @version $Id$
 *
 * @ingroup	ServicesTracking
 *
 */
class ilLPStatusTypicalLearningTime extends ilLPStatus
{
    public function __construct($a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        parent::__construct($a_obj_id);
        $this->db = $ilDB;
    }

    public static function _getInProgress($a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $status_info = ilLPStatusWrapper::_getStatusInfo($a_obj_id);
        $tlt = $status_info['tlt'];

        include_once './Services/Tracking/classes/class.ilChangeEvent.php';
        $all = ilChangeEvent::_lookupReadEvents($a_obj_id);

        foreach ($all as $event) {
            if ($event['spent_seconds'] < $tlt) {
                $user_ids[] = $event['usr_id'];
            }
        }
        return $user_ids ? $user_ids : array();
    }

    public static function _getCompleted($a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $status_info = ilLPStatusWrapper::_getStatusInfo($a_obj_id);
        $tlt = $status_info['tlt'];

        // TODO: move to status info
        include_once './Services/Tracking/classes/class.ilChangeEvent.php';
        $all = ilChangeEvent::_lookupReadEvents($a_obj_id);

        foreach ($all as $event) {
            if ($event['spent_seconds'] >= $tlt) {
                $user_ids[] = $event['usr_id'];
            }
        }
        return $user_ids ? $user_ids : array();
    }

    public static function _getStatusInfo($a_obj_id)
    {
        $status_info['tlt'] = ilMDEducational::_getTypicalLearningTimeSeconds($a_obj_id);

        return $status_info;
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
        global $DIC;

        $ilObjDataCache = $DIC['ilObjDataCache'];
        $ilDB = $DIC['ilDB'];
        
        $status = self::LP_STATUS_NOT_ATTEMPTED_NUM;
        switch ($ilObjDataCache->lookupType($a_obj_id)) {
            case 'lm':
                if (ilChangeEvent::hasAccessed($a_obj_id, $a_user_id)) {
                    $status = self::LP_STATUS_IN_PROGRESS_NUM;
                    
                    // completed?
                    $status_info = ilLPStatusWrapper::_getStatusInfo($a_obj_id);
                    $tlt = $status_info['tlt'];

                    include_once './Services/Tracking/classes/class.ilChangeEvent.php';
                    $re = ilChangeEvent::_lookupReadEvents($a_obj_id, $a_user_id);
                    if ($re[0]['spent_seconds'] >= $tlt) {
                        $status = self::LP_STATUS_COMPLETED_NUM;
                    }
                }
                break;
        }
        return $status;
    }

    /**
     * Determine percentage
     *
     * @param	integer		object id
     * @param	integer		user id
     * @param	object		object (optional depends on object type)
     * @return	integer		percentage
     */
    public function determinePercentage($a_obj_id, $a_user_id, $a_obj = null)
    {
        $tlt = (int) ilMDEducational::_getTypicalLearningTimeSeconds($a_obj_id);
        $re = ilChangeEvent::_lookupReadEvents($a_obj_id, $a_user_id);
        $spent = (int) $re[0]["spent_seconds"];

        if ($tlt > 0) {
            $per = min(100, 100 / $tlt * $spent);
        } else {
            $per = 100;
        }

        return $per;
    }
}
