<?php

declare(strict_types=0);

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author     Stefan Meyer <meyer@leifos.com>
 * @ingroup    ServicesTracking
 */
class ilLPStatusVisits extends ilLPStatus
{
    public static function _getInProgress(int $a_obj_id): array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $status_info = ilLPStatusWrapper::_getStatusInfo($a_obj_id);
        $required_visits = $status_info['visits'];

        $all = ilChangeEvent::_lookupReadEvents($a_obj_id);
        $user_ids = [];
        foreach ($all as $event) {
            if ($event['read_count'] < $required_visits) {
                $user_ids[] = (int) $event['usr_id'];
            }
        }
        return $user_ids;
    }

    public static function _getCompleted(int $a_obj_id): array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $status_info = ilLPStatusWrapper::_getStatusInfo($a_obj_id);
        $required_visits = $status_info['visits'];

        $all = ilChangeEvent::_lookupReadEvents($a_obj_id);
        $user_ids = [];
        foreach ($all as $event) {
            if ($event['read_count'] >= $required_visits) {
                $user_ids[] = (int) $event['usr_id'];
            }
        }
        return $user_ids;
    }

    public static function _getStatusInfo(int $a_obj_id): array
    {
        $status_info['visits'] = ilLPObjSettings::_lookupVisits($a_obj_id);
        return $status_info;
    }

    public function determineStatus(
        int $a_obj_id,
        int $a_usr_id,
        object $a_obj = null
    ): int {
        global $DIC;

        $ilObjDataCache = $DIC['ilObjDataCache'];
        $ilDB = $DIC['ilDB'];

        $status = self::LP_STATUS_NOT_ATTEMPTED_NUM;
        switch ($this->ilObjDataCache->lookupType($a_obj_id)) {
            case 'lm':
                if (ilChangeEvent::hasAccessed($a_obj_id, $a_usr_id)) {
                    $status = self::LP_STATUS_IN_PROGRESS_NUM;

                    // completed?
                    $status_info = ilLPStatusWrapper::_getStatusInfo($a_obj_id);
                    $required_visits = $status_info['visits'];

                    $re = ilChangeEvent::_lookupReadEvents(
                        $a_obj_id,
                        $a_usr_id
                    );
                    if ($re[0]['read_count'] >= $required_visits) {
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
    ): int {
        $reqv = ilLPObjSettings::_lookupVisits($a_obj_id);

        $re = ilChangeEvent::_lookupReadEvents($a_obj_id, $a_usr_id);
        $rc = (int) $re[0]["read_count"];

        if ($reqv > 0) {
            $per = min(100, 100 / $reqv * $rc);
        } else {
            $per = 100;
        }

        return $per;
    }
}
