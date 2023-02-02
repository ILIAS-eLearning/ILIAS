<?php

declare(strict_types=0);

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
                    if (($re[0]['read_count'] ?? 0) >= $required_visits) {
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
        $rc = (int) ($re[0]["read_count"] ?? 0);

        if ($reqv > 0 && $rc) {
            $per = min(100, 100 / $reqv * $rc);
        } else {
            $per = 100;
        }
        return $per;
    }
}
