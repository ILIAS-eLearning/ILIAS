<?php declare(strict_types=0);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @package ServicesTracking
 */
class ilLPStatusContentVisited extends ilLPStatus
{
    /**
     * @inheritdoc
     */
    public static function _getCompleted(int $a_obj_id) : array
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
    public function determineStatus(
        int $a_obj_id,
        int $a_usr_id,
        object $a_obj = null
    ) : int {
        /**
         * @var $ilObjDataCache ilObjectDataCache
         */
        global $DIC;

        $ilObjDataCache = $DIC['ilObjDataCache'];

        $status = self::LP_STATUS_NOT_ATTEMPTED_NUM;

        switch ($this->ilObjDataCache->lookupType($a_obj_id)) {
            case 'file':
            case 'copa':
            case 'htlm':
                if (\ilChangeEvent::hasAccessed($a_obj_id, $a_usr_id)) {
                    $status = self::LP_STATUS_COMPLETED_NUM;
                }
                break;
        }

        return $status;
    }
}
