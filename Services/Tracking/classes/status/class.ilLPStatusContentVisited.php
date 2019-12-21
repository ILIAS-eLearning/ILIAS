<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */


require_once 'Services/Tracking/classes/class.ilLPStatus.php';
require_once 'Services/Tracking/classes/class.ilLearningProgress.php';

/**
 * @author Michael Jansen <mjansen@databay.de>
 * @package ServicesTracking
 */
class ilLPStatusContentVisited extends ilLPStatus
{
    /**
     * @inheritdoc
     */
    public static function _getCompleted($a_obj_id)
    {
        $userIds = [];

        $allReadEvents = \ilChangeEvent::_lookupReadEvents($a_obj_id);
        foreach ($allReadEvents as $event) {
            $userIds[] = $event['usr_id'];
        }

        return $userIds;
    }

    /**
     * @inheritdoc
     */
    public function determineStatus($a_obj_id, $a_user_id, $a_obj = null)
    {
        /**
         * @var $ilObjDataCache ilObjectDataCache
         */
        global $DIC;

        $ilObjDataCache = $DIC['ilObjDataCache'];

        $status = self::LP_STATUS_NOT_ATTEMPTED_NUM;

        switch ($ilObjDataCache->lookupType($a_obj_id)) {
            case 'file':
            case 'copa':
                if (\ilChangeEvent::hasAccessed($a_obj_id, $a_user_id)) {
                    $status = self::LP_STATUS_COMPLETED_NUM;
                }
                break;
        }

        return $status;
    }
}
