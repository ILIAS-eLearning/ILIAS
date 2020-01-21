<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Tracking/classes/class.ilLPStatus.php';

/**
 * @author Stefan Meyer <meyer@leifos.com>
 *
 * @version $Id$
 *
 * @package ilias-tracking
 *
 */
class ilLPStatusSCORM extends ilLPStatus
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
        $status_info = ilLPStatusWrapper::_getStatusInfo($a_obj_id);
        $users = array();
        foreach ($status_info['in_progress'] as $in_progress) {
            $users = array_merge($users, $in_progress);
        }
        $users = array_unique($users);
        $users = array_diff($users, ilLPStatusWrapper::_getCompleted($a_obj_id));
        $users = array_diff($users, ilLPStatusWrapper::_getFailed($a_obj_id));

        return $users;
    }

    public static function _getCompleted($a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        include_once './Modules/ScormAicc/classes/SCORM/class.ilObjSCORMTracking.php';

        $status_info = ilLPStatusWrapper::_getStatusInfo($a_obj_id);

        $items = $status_info['scos'];

        $counter = 0;
        $users = array();
        foreach ($items as $sco_id) {
            $tmp_users = $status_info['completed'][$sco_id];

            if (!$counter++) {
                $users = $tmp_users;
            } else {
                $users = array_intersect($users, $tmp_users);
            }
        }

        $users = array_diff($users, ilLPStatusWrapper::_getFailed($a_obj_id));
        return $users;
    }

    public static function _getFailed($a_obj_id)
    {
        $status_info = ilLPStatusWrapper::_getStatusInfo($a_obj_id);

        if (!count($status_info['scos'])) {
            return array();
        }
        $users = array();
        foreach ($status_info['scos'] as $sco_id) {
            /* #17913 - max attempts were removed in 5.1
            // max attempts vs. failed
            if(sizeof($status_info['in_progress'][$sco_id]))
            {
                foreach($status_info['in_progress'][$sco_id] as $user_id)
                {
                    if(!in_array($user_id, $status_info['failed'][$sco_id]))
                    {
                        switch($status_info["subtype"])
                        {
                            case 'hacp':
                            case 'aicc':
                            case 'scorm':
                                include_once './Modules/ScormAicc/classes/SCORM/class.ilObjSCORMTracking.php';
                                $has_max_attempts = ilObjSCORMTracking::_hasMaxAttempts($a_obj_id, $user_id);
                                break;

                            case 'scorm2004':
                                include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Tracking.php");
                                $has_max_attempts = ilSCORM2004Tracking::_hasMaxAttempts($a_obj_id, $user_id);
                                break;
                        }

                        if($has_max_attempts)
                        {
                            $status_info['failed'][$sco_id][] = $user_id;
                        }
                    }
                }
            }
            */
            
            $users = array_merge($users, (array) $status_info['failed'][$sco_id]);
        }
        return array_unique($users);
    }

    public static function _getNotAttempted($a_obj_id)
    {
        $users = array();

        $members = ilObjectLP::getInstance($a_obj_id)->getMembers();
        if ($members) {
            // diff in progress and completed (use stored result in LPStatusWrapper)
            $users = array_diff((array) $members, ilLPStatusWrapper::_getInProgress($a_obj_id));
            $users = array_diff((array) $users, ilLPStatusWrapper::_getCompleted($a_obj_id));
            $users = array_diff((array) $users, ilLPStatusWrapper::_getFailed($a_obj_id));
        }

        return $users;
    }

    
    public static function _getStatusInfo($a_obj_id)
    {
        // Which sco's determine the status
        include_once './Services/Object/classes/class.ilObjectLP.php';
        $olp = ilObjectLP::getInstance($a_obj_id);
        $collection = $olp->getCollectionInstance();
        if ($collection) {
            $status_info['scos'] = $collection->getItems();
        } else {
            $status_info['scos'] = array();
        }
        $status_info['num_scos'] = count($status_info['scos']);

        // Get subtype
        include_once './Modules/ScormAicc/classes/class.ilObjSAHSLearningModule.php';
        $status_info['subtype'] = ilObjSAHSLearningModule::_lookupSubType($a_obj_id);
        
        switch ($status_info['subtype']) {
            case 'hacp':
            case 'aicc':
                include_once './Modules/ScormAicc/classes/SCORM/class.ilObjSCORMTracking.php';
                $status_info['num_completed'] = ilObjSCORMTracking::_getCountCompletedPerUser($status_info['scos'], $a_obj_id);

                include_once './Modules/ScormAicc/classes/class.ilObjAICCLearningModule.php';
                foreach (ilObjAICCLearningModule::_getTrackingItems($a_obj_id) as $item) {
                    if (in_array($item['obj_id'], $status_info['scos'])) {
                        $status_info['scos_title']["$item[obj_id]"] = $item['title'];
                    }
                }
                $info = ilObjSCORMTracking::_getProgressInfo($status_info['scos'], $a_obj_id);
                break;

            case 'scorm':
                include_once './Modules/ScormAicc/classes/SCORM/class.ilObjSCORMTracking.php';
                $status_info['num_completed'] = ilObjSCORMTracking::_getCountCompletedPerUser($status_info['scos'], $a_obj_id);

                include_once './Modules/ScormAicc/classes/SCORM/class.ilSCORMItem.php';
                foreach ($status_info['scos'] as $sco_id) {
                    $status_info['scos_title'][$sco_id] = ilSCORMItem::_lookupTitle($sco_id);
                }
                $info = ilObjSCORMTracking::_getProgressInfo($status_info['scos'], $a_obj_id);
                break;
                
            case "scorm2004":
                include_once './Modules/Scorm2004/classes/class.ilSCORM2004Tracking.php';
                $status_info['num_completed'] = ilSCORM2004Tracking::_getCountCompletedPerUser($status_info['scos'], $a_obj_id, true);
                include_once './Modules/Scorm2004/classes/class.ilObjSCORM2004LearningModule.php';
                foreach ($status_info['scos'] as $sco_id) {
                    $status_info['scos_title'][$sco_id] = ilObjSCORM2004LearningModule::_lookupItemTitle($sco_id);
                }

                $info = ilSCORM2004Tracking::_getItemProgressInfo($status_info['scos'], $a_obj_id, true);
                break;
        }

        $status_info['completed'] = array();
        $status_info['failed'] = array();
        $status_info['in_progress'] = array();
        foreach ($status_info['scos'] as $sco_id) {
            $status_info['completed'][$sco_id] = $info['completed'][$sco_id] ? $info['completed'][$sco_id] : array();
            $status_info['failed'][$sco_id] = $info['failed'][$sco_id] ? $info['failed'][$sco_id] : array();
            $status_info['in_progress'][$sco_id] = $info['in_progress'][$sco_id] ? $info['in_progress'][$sco_id] : array();
        }
        //var_dump($status_info["completed"]);
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
        $ilLog = $DIC['ilLog'];
        
        $status = self::LP_STATUS_NOT_ATTEMPTED_NUM;

        // if the user has accessed the scorm object
        // the status is at least "in progress"
        include_once("./Services/Tracking/classes/class.ilChangeEvent.php");
        if (ilChangeEvent::hasAccessed($a_obj_id, $a_user_id)) {
            $status = self::LP_STATUS_IN_PROGRESS_NUM;
        }
        //$ilLog->write("-".$status."-");
        
        // Which sco's determine the status
        include_once './Services/Object/classes/class.ilObjectLP.php';
        $olp = ilObjectLP::getInstance($a_obj_id);
        $collection = $olp->getCollectionInstance();
        if ($collection) {
            $scos = $collection->getItems();
            if (sizeof($scos)) { // #15462 (#11513 - empty collections cannot be completed)
                include_once './Modules/ScormAicc/classes/class.ilObjSAHSLearningModule.php';
                $subtype = ilObjSAHSLearningModule::_lookupSubType($a_obj_id);
                switch ($subtype) {
                    case 'hacp':
                    case 'aicc':
                    case 'scorm':
                        include_once("./Modules/ScormAicc/classes/SCORM/class.ilObjSCORMTracking.php");
                        $scorm_status = ilObjSCORMTracking::_getCollectionStatus($scos, $a_obj_id, $a_user_id);
                        break;

                    case 'scorm2004':
                        include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Tracking.php");
                        $scorm_status = ilSCORM2004Tracking::_getCollectionStatus($scos, $a_obj_id, $a_user_id);
                        break;
                }
                
                switch ($scorm_status) {
                    case "in_progress":
                        $status = self::LP_STATUS_IN_PROGRESS_NUM;
                        break;
                    case "completed":
                        $status = self::LP_STATUS_COMPLETED_NUM;
                        break;
                    case "failed":
                        $status = self::LP_STATUS_FAILED_NUM;
                        break;
                }
            }
        }
        
        //$ilLog->write("-".$status."-");
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
        // Which sco's determine the status
        include_once './Services/Object/classes/class.ilObjectLP.php';
        $olp = ilObjectLP::getInstance($a_obj_id);
        $collection = $olp->getCollectionInstance();
        if ($collection) {
            $scos = $collection->getItems();
            $reqscos = count($scos);
        
            include_once './Modules/ScormAicc/classes/class.ilObjSAHSLearningModule.php';
            $subtype = ilObjSAHSLearningModule::_lookupSubType($a_obj_id);
            if ($subtype != "scorm2004") {
                include_once("./Modules/ScormAicc/classes/SCORM/class.ilObjSCORMTracking.php");
                $compl = ilObjSCORMTracking::_countCompleted($scos, $a_obj_id, $a_user_id);
            } else {
                include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Tracking.php");
                $compl = ilSCORM2004Tracking::_countCompleted($scos, $a_obj_id, $a_user_id, true);
            }
        }

        if ($reqscos > 0) {
            $per = min(100, 100 / $reqscos * $compl);
        } else {
            $per = 100;
        }

        return $per;
    }

    public function refreshStatus($a_obj_id, $a_users = null)
    {
        parent::refreshStatus($a_obj_id, $a_users);
        
        // this is restricted to SCOs in the current collection
        include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
        $in_progress = ilLPStatusWrapper::_getInProgress($a_obj_id);
        $completed = ilLPStatusWrapper::_getCompleted($a_obj_id);
        $failed = ilLPStatusWrapper::_getFailed($a_obj_id);
        $all_active_users = array_unique(array_merge($in_progress, $completed, $failed));
        
        // get all tracked users regardless of SCOs
        include_once './Modules/ScormAicc/classes/class.ilObjSAHSLearningModule.php';
        $subtype = ilObjSAHSLearningModule::_lookupSubType($a_obj_id);
        if ($subtype != "scorm2004") {
            include_once("./Modules/ScormAicc/classes/SCORM/class.ilObjSCORMTracking.php");
            $all_tracked_users = ilObjSCORMTracking::_getTrackedUsers($a_obj_id);
        } else {
            include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Tracking.php");
            $all_tracked_users = ilSCORM2004Tracking::_getTrackedUsers($a_obj_id);
        }
        
        $not_attempted_users = array_diff($all_tracked_users, $all_active_users);
        unset($all_tracked_users);
        unset($all_active_users);
        
        // reset all users which have no data for the current SCOs
        if ($not_attempted_users) {
            foreach ($not_attempted_users as $usr_id) {
                // this will update any (parent) collections if necessary
                ilLPStatus::writeStatus($a_obj_id, $usr_id, self::LP_STATUS_NOT_ATTEMPTED_NUM, 0);
            }
        }
    }
}
