<?php declare(strict_types=0);

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author     Stefan Meyer <meyer@leifos.com>
 * @ingroup    ServicesTracking
 */
class ilLPStatusTypicalLearningTime extends ilLPStatus
{
    public static function _getInProgress(int $a_obj_id) : array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $status_info = ilLPStatusWrapper::_getStatusInfo($a_obj_id);
        $tlt = $status_info['tlt'];

        $all = ilChangeEvent::_lookupReadEvents($a_obj_id);

        $user_ids = [];
        foreach ($all as $event) {
            if ($event['spent_seconds'] < $tlt) {
                $user_ids[] = (int) $event['usr_id'];
            }
        }
        return $user_ids;
    }

    public static function _getCompleted(int $a_obj_id) : array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $status_info = ilLPStatusWrapper::_getStatusInfo($a_obj_id);
        $tlt = $status_info['tlt'];
        // TODO: move to status info
        $all = ilChangeEvent::_lookupReadEvents($a_obj_id);

        $user_ids = [];
        foreach ($all as $event) {
            if ($event['spent_seconds'] >= $tlt) {
                $user_ids[] = (int) $event['usr_id'];
            }
        }
        return $user_ids;
    }

    public static function _getStatusInfo(int $a_obj_id) : array
    {
        $status_info['tlt'] = ilMDEducational::_getTypicalLearningTimeSeconds(
            $a_obj_id
        );
        return $status_info;
    }

    public function determineStatus(
        int $a_obj_id,
        int $a_usr_id,
        object $a_obj = null
    ) : int {
        $status = self::LP_STATUS_NOT_ATTEMPTED_NUM;
        switch ($this->ilObjDataCache->lookupType($a_obj_id)) {
            case 'lm':
                if (ilChangeEvent::hasAccessed($a_obj_id, $a_usr_id)) {
                    $status = self::LP_STATUS_IN_PROGRESS_NUM;

                    // completed?
                    $status_info = ilLPStatusWrapper::_getStatusInfo($a_obj_id);
                    $tlt = $status_info['tlt'];

                    $re = ilChangeEvent::_lookupReadEvents(
                        $a_obj_id,
                        $a_usr_id
                    );
                    if ($re[0]['spent_seconds'] >= $tlt) {
                        $status = self::LP_STATUS_COMPLETED_NUM;
                    }
                }
                break;
        }
        return $status;
    }

    public function determinePercentage(
        int $a_obj_id,
        int $a_usr_id,
        ?object $a_obj = null
    ) : int {
        $tlt = ilMDEducational::_getTypicalLearningTimeSeconds($a_obj_id);
        $re = ilChangeEvent::_lookupReadEvents($a_obj_id, $a_usr_id);
        $spent = (int) ($re[0]["spent_seconds"] ?? 0);

        if ($tlt > 0) {
            $per = min(100, 100 / $tlt * $spent);
        } else {
            $per = 100;
        }
        return $per;
    }
}
