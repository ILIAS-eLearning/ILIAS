<?php

declare(strict_types=0);

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Stefan Meyer <meyer@leifos.com>
 * @package ilias-tracking
 */
class ilLPStatusSCORM extends ilLPStatus
{
    public static function _getInProgress(int $a_obj_id): array
    {
        $status_info = ilLPStatusWrapper::_getStatusInfo($a_obj_id);
        $users = array();
        foreach ($status_info['in_progress'] as $in_progress) {
            $users = array_merge($users, $in_progress);
        }
        $users = array_unique($users);
        $users = array_diff(
            $users,
            ilLPStatusWrapper::_getCompleted($a_obj_id)
        );
        $users = array_diff($users, ilLPStatusWrapper::_getFailed($a_obj_id));

        return $users;
    }

    public static function _getCompleted(int $a_obj_id): array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

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

    public static function _getFailed(int $a_obj_id): array
    {
        $status_info = ilLPStatusWrapper::_getStatusInfo($a_obj_id);

        if (!count($status_info['scos'])) {
            return array();
        }
        $users = array();
        foreach ($status_info['scos'] as $sco_id) {
            $users = array_merge(
                $users,
                (array) $status_info['failed'][$sco_id]
            );
        }
        return array_unique($users);
    }

    public static function _getNotAttempted(int $a_obj_id): array
    {
        $users = array();

        $members = ilObjectLP::getInstance($a_obj_id)->getMembers();
        if ($members) {
            // diff in progress and completed (use stored result in LPStatusWrapper)
            $users = array_diff(
                (array) $members,
                ilLPStatusWrapper::_getInProgress($a_obj_id)
            );
            $users = array_diff(
                $users,
                ilLPStatusWrapper::_getCompleted($a_obj_id)
            );
            $users = array_diff(
                $users,
                ilLPStatusWrapper::_getFailed($a_obj_id)
            );
        }

        return $users;
    }

    public static function _getStatusInfo(int $a_obj_id): array
    {
        // Which sco's determine the status
        $olp = ilObjectLP::getInstance($a_obj_id);
        $collection = $olp->getCollectionInstance();
        if ($collection) {
            $status_info['scos'] = $collection->getItems();
        } else {
            $status_info['scos'] = array();
        }
        $status_info['num_scos'] = count($status_info['scos']);

        // Get subtype
        $status_info['subtype'] = ilObjSAHSLearningModule::_lookupSubType(
            $a_obj_id
        );
        $info = [];
        switch ($status_info['subtype']) {
            case 'hacp':
            case 'aicc':
                $status_info['num_completed'] = ilObjSCORMTracking::_getCountCompletedPerUser(
                    $status_info['scos'],
                    $a_obj_id
                );

                foreach (ilObjAICCLearningModule::_getTrackingItems(
                    $a_obj_id
                ) as $item) {
                    if (in_array($item['obj_id'], $status_info['scos'])) {
                        $status_info['scos_title']["$item[obj_id]"] = $item['title'];
                    }
                }
                $info = ilObjSCORMTracking::_getProgressInfo(
                    $status_info['scos'],
                    $a_obj_id
                );
                break;

            case 'scorm':
                $status_info['num_completed'] = ilObjSCORMTracking::_getCountCompletedPerUser(
                    $status_info['scos'],
                    $a_obj_id
                );

                foreach ($status_info['scos'] as $sco_id) {
                    $status_info['scos_title'][$sco_id] = ilSCORMItem::_lookupTitle(
                        $sco_id
                    );
                }
                $info = ilObjSCORMTracking::_getProgressInfo(
                    $status_info['scos'],
                    $a_obj_id
                );
                break;

            case "scorm2004":
                $status_info['num_completed'] = ilSCORM2004Tracking::_getCountCompletedPerUser(
                    $status_info['scos'],
                    $a_obj_id,
                    true
                );
                foreach ($status_info['scos'] as $sco_id) {
                    $status_info['scos_title'][$sco_id] = ilObjSCORM2004LearningModule::_lookupItemTitle(
                        $sco_id
                    );
                }

                $info = ilSCORM2004Tracking::_getItemProgressInfo(
                    $status_info['scos'],
                    $a_obj_id,
                    true
                );
                break;
        }

        $status_info['completed'] = array();
        $status_info['failed'] = array();
        $status_info['in_progress'] = array();
        foreach ($status_info['scos'] as $sco_id) {
            $status_info['completed'][$sco_id] = $info['completed'][$sco_id] ?? array();
            $status_info['failed'][$sco_id] = $info['failed'][$sco_id] ?? array();
            $status_info['in_progress'][$sco_id] = $info['in_progress'][$sco_id] ?? array();
        }
        //var_dump($status_info["completed"]);
        return $status_info;
    }

    public function determineStatus(
        int $a_obj_id,
        int $a_usr_id,
        object $a_obj = null
    ): int {
        global $DIC;

        $status = self::LP_STATUS_NOT_ATTEMPTED_NUM;

        // if the user has accessed the scorm object
        // the status is at least "in progress"
        if (ilChangeEvent::hasAccessed($a_obj_id, $a_usr_id)) {
            $status = self::LP_STATUS_IN_PROGRESS_NUM;
        }
        // Which sco's determine the status
        $olp = ilObjectLP::getInstance($a_obj_id);
        $collection = $olp->getCollectionInstance();
        if ($collection) {
            $scos = $collection->getItems();
            if (sizeof(
                $scos
            )) { // #15462 (#11513 - empty collections cannot be completed)
                $subtype = ilObjSAHSLearningModule::_lookupSubType($a_obj_id);
                $scorm_status = '';
                switch ($subtype) {
                    case 'hacp':
                    case 'aicc':
                    case 'scorm':
                        $scorm_status = ilObjSCORMTracking::_getCollectionStatus(
                            $scos,
                            $a_obj_id,
                            $a_usr_id
                        );
                        break;

                    case 'scorm2004':
                        $scorm_status = ilSCORM2004Tracking::_getCollectionStatus(
                            $scos,
                            $a_obj_id,
                            $a_usr_id
                        );
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

    public function determinePercentage(
        int $a_obj_id,
        int $a_usr_id,
        ?object $a_obj = null
    ): int {
        // Which sco's determine the status
        $olp = ilObjectLP::getInstance($a_obj_id);
        $collection = $olp->getCollectionInstance();
        $reqscos = 0;
        $compl = 0;
        if ($collection) {
            $scos = $collection->getItems();
            $reqscos = count($scos);

            $subtype = ilObjSAHSLearningModule::_lookupSubType($a_obj_id);
            if ($subtype != "scorm2004") {
                $compl = ilObjSCORMTracking::_countCompleted(
                    $scos,
                    $a_obj_id,
                    $a_usr_id
                );
            } else {
                $compl = ilSCORM2004Tracking::_countCompleted(
                    $scos,
                    $a_obj_id,
                    $a_usr_id,
                    true
                );
            }
        }

        if ($reqscos > 0) {
            $per = min(100, 100 / $reqscos * $compl);
        } else {
            $per = 100;
        }

        return $per;
    }

    public function refreshStatus(int $a_obj_id, ?array $a_users = null): void
    {
        parent::refreshStatus($a_obj_id, $a_users);

        // this is restricted to SCOs in the current collection
        $in_progress = ilLPStatusWrapper::_getInProgress($a_obj_id);
        $completed = ilLPStatusWrapper::_getCompleted($a_obj_id);
        $failed = ilLPStatusWrapper::_getFailed($a_obj_id);
        $all_active_users = array_unique(
            array_merge($in_progress, $completed, $failed)
        );

        // get all tracked users regardless of SCOs
        $subtype = ilObjSAHSLearningModule::_lookupSubType($a_obj_id);
        if ($subtype != "scorm2004") {
            $all_tracked_users = ilObjSCORMTracking::_getTrackedUsers(
                $a_obj_id
            );
        } else {
            $all_tracked_users = ilSCORM2004Tracking::_getTrackedUsers(
                $a_obj_id
            );
        }

        $not_attempted_users = array_diff(
            $all_tracked_users,
            $all_active_users
        );
        unset($all_tracked_users);
        unset($all_active_users);

        // reset all users which have no data for the current SCOs
        if ($not_attempted_users) {
            foreach ($not_attempted_users as $usr_id) {
                // this will update any (parent) collections if necessary
                ilLPStatus::writeStatus(
                    $a_obj_id,
                    $usr_id,
                    self::LP_STATUS_NOT_ATTEMPTED_NUM
                );
            }
        }
    }
}
