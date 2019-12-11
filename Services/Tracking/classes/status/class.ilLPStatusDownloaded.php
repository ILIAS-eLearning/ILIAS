<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */


require_once 'Services/Tracking/classes/class.ilLPStatus.php';
require_once 'Services/Tracking/classes/class.ilLearningProgress.php';

/**
 * @author Michael Jansen <mjansen@databay.de>
 * @package ServicesTracking
 */
class ilLPStatusDownloaded extends ilLPStatus
{
    /**
     * @param int $a_obj_id
     */
    public function __construct($a_obj_id)
    {
        global $ilDB;

        parent::__construct($a_obj_id);
        $this->db = $ilDB;
    }

    public static function _getCompleted($a_obj_id)
    {
        $usr_ids = array();
        require_once 'Services/Tracking/classes/class.ilChangeEvent.php';
        $all_read_events = ilChangeEvent::_lookupReadEvents($a_obj_id);
        foreach ($all_read_events as $event) {
            $usr_ids[] = $event['usr_id'];
        }
        return $usr_ids;
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
        /**
         * @var $ilObjDataCache ilObjectDataCache
         */
        global $ilObjDataCache;

        $status = self::LP_STATUS_NOT_ATTEMPTED_NUM;
        switch ($ilObjDataCache->lookupType($a_obj_id)) {
            case 'file':
                include_once './Services/Tracking/classes/class.ilChangeEvent.php';
                if (ilChangeEvent::hasAccessed($a_obj_id, $a_user_id)) {
                    $status = self::LP_STATUS_COMPLETED_NUM;
                }
                break;
        }
        return $status;
    }
}
