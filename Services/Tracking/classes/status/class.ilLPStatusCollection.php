<?php

declare(strict_types=0);
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

/**
 * @author  Stefan Meyer <meyer@leifos.com>
 * @package ilias-tracking
 */
class ilLPStatusCollection extends ilLPStatus
{
    private ilTree $tree;

    public function __construct(int $a_obj_id)
    {
        global $DIC;

        parent::__construct($a_obj_id);
        $this->tree = $DIC->repositoryTree();
    }

    public static function _getNotAttempted(int $a_obj_id): array
    {
        $users = array();

        $members = self::getMembers($a_obj_id);
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

    public static function _getInProgress(int $a_obj_id): array
    {
        $users = ilChangeEvent::lookupUsersInProgress($a_obj_id);

        $olp = ilObjectLP::getInstance($a_obj_id);
        $collection = $olp->getCollectionInstance();
        if ($collection) {
            foreach ($collection->getItems() as $item_id) {
                $item_id = ilObject::_lookupObjId($item_id);

                // merge arrays of users with status 'in progress'
                $users = array_unique(
                    array_merge(
                        $users,
                        ilLPStatusWrapper::_getInProgress($item_id)
                    )
                );
                $users = array_unique(
                    array_merge(
                        $users,
                        ilLPStatusWrapper::_getCompleted($item_id)
                    )
                );
            }
        }

        // Exclude all users with status completed.
        $users = array_diff(
            $users,
            ilLPStatusWrapper::_getCompleted($a_obj_id)
        );
        // Exclude all users with status failed.
        $users = array_diff($users, ilLPStatusWrapper::_getFailed($a_obj_id));

        if ($users) {
            // Exclude all non members
            $users = array_intersect(self::getMembers($a_obj_id), $users);
        }

        return $users;
    }

    /**
     * Get completed users
     * New handling for optional grouped assignments.
     */
    public static function _getCompleted(int $a_obj_id): array
    {
        global $DIC;

        $ilObjDataCache = $DIC['ilObjDataCache'];

        $olp = ilObjectLP::getInstance($a_obj_id);
        $collection = $olp->getCollectionInstance();
        $grouped_items = [];
        if ($collection) {
            $grouped_items = $collection->getGroupedItemsForLPStatus();
        }
        if (!count($grouped_items)) {
            // #11513 - empty collections cannot be completed
            return array();
        } else {
            // New handling for optional assignments
            $counter = 0;
            $users = array();
            foreach ($grouped_items as $grouping_id => $grouping) {
                $isGrouping = $grouping_id ? true : false;
                $grouping_completed = array();
                $grouping_completed_users_num = array();
                foreach ((array) $grouping['items'] as $item) {
                    $item_id = $ilObjDataCache->lookupObjId((int) $item);
                    $tmp_users = ilLPStatusWrapper::_getCompleted($item_id);
                    if ($isGrouping) {
                        // Iterated through all grouped items and count the number of fullfiled items
                        foreach ($tmp_users as $tmp_user_id) {
                            $grouping_completed_users_num[$tmp_user_id] =
                                ($grouping_completed_users_num[$tmp_user_id] ?? 0) + 1;
                        }
                    } elseif (!$counter++) {
                        $users = $tmp_users;
                    } else {
                        $users = array_intersect($users, $tmp_users);
                    }
                }
                if ($isGrouping) {
                    // Iterate through all "grouping_completed_users_num"
                    // All users with completed items greater equal than "num_obligatory" are completed
                    foreach ($grouping_completed_users_num as $tmp_user_id => $grouping_num_completed) {
                        if ($grouping_num_completed >= $grouping['num_obligatory']) {
                            $grouping_completed[] = $tmp_user_id;
                        }
                    }

                    // build intersection of users
                    if (!$counter++) {
                        $users = $grouping_completed;
                    } else {
                        $users = array_intersect($users, $grouping_completed);
                    }
                }
            }
        }

        $users = array_diff($users, ilLPStatusWrapper::_getFailed($a_obj_id));

        if ($users) {
            // Exclude all non members
            $users = array_intersect(self::getMembers($a_obj_id), $users);
        }

        return (array) $users;
    }

    public static function _getFailed(int $a_obj_id): array
    {
        global $DIC;

        $ilObjDataCache = $DIC['ilObjDataCache'];

        $users = array();

        $olp = ilObjectLP::getInstance($a_obj_id);
        $collection = $olp->getCollectionInstance();
        if ($collection) {
            foreach ($collection->getGroupedItemsForLPStatus(
            ) as $grouping_id => $grouping) {
                $isGrouping = $grouping_id ? true : false;

                $gr_failed = array();
                $gr_failed_users_num = array();
                $counter = 0;
                foreach ((array) $grouping['items'] as $item) {
                    $item_id = $ilObjDataCache->lookupObjId((int) $item);
                    $tmp_users = ilLPStatusWrapper::_getFailed($item_id);

                    if ($isGrouping) {
                        foreach ($tmp_users as $tmp_user_id) {
                            ++$gr_failed_users_num[$tmp_user_id];
                        }
                    } else {
                        // One item failed is sufficient for status failed.
                        $gr_failed = array_merge($gr_failed, $tmp_users);
                    }
                    $counter++;
                }
                if ($isGrouping) {
                    $allowed_failed = count(
                        $grouping['items']
                    ) - $grouping['num_obligatory'];
                    // Itereate over all failed users and check whether the allowd_failed value exceeded
                    foreach ($gr_failed_users_num as $tmp_user_id => $num_failed) {
                        if ($num_failed > $allowed_failed) {
                            $gr_failed[] = $tmp_user_id;
                        }
                    }
                }
                $users = array_unique(array_merge($users, $gr_failed));
            }
        }

        if ($users) {
            // Exclude all non members
            $users = array_intersect(self::getMembers($a_obj_id), $users);
        }

        return array_unique($users);
    }

    public static function _getStatusInfo(int $a_obj_id): array
    {
        $status_info = array();

        $olp = ilObjectLP::getInstance($a_obj_id);
        $collection = $olp->getCollectionInstance();
        if ($collection) {
            $status_info['collections'] = $collection->getItems();
            $status_info['num_collections'] = count(
                $status_info['collections']
            );
        }

        return $status_info;
    }

    public static function _getTypicalLearningTime(int $a_obj_id): int
    {
        global $DIC;

        $ilObjDataCache = $DIC['ilObjDataCache'];

        if ($ilObjDataCache->lookupType($a_obj_id) == 'sahs') {
            return parent::_getTypicalLearningTime($a_obj_id);
        }

        $tlt = 0;
        $status_info = ilLPStatusWrapper::_getStatusInfo($a_obj_id);
        foreach ($status_info['collections'] as $item) {
            $tlt += ilLPStatusWrapper::_getTypicalLearningTime(
                $ilObjDataCache->lookupObjId((int) $item)
            );
        }
        return $tlt;
    }

    public function determineStatus(
        int $a_obj_id,
        int $a_usr_id,
        object $a_obj = null
    ): int {
        global $DIC;

        $ilObjDataCache = $DIC['ilObjDataCache'];

        $status['completed'] = true;
        $status['failed'] = false;
        $status['in_progress'] = false;

        switch ($this->ilObjDataCache->lookupType($a_obj_id)) {
            case "crs":
            case "fold":
            case "grp":
            case "lso":
                if (ilChangeEvent::hasAccessed($a_obj_id, $a_usr_id)) {
                    $status['in_progress'] = true;
                }

                $olp = ilObjectLP::getInstance($a_obj_id);
                $collection = $olp->getCollectionInstance();
                $grouped_items = [];
                if ($collection) {
                    $grouped_items = $collection->getGroupedItemsForLPStatus();
                }
                if (!count($grouped_items)) {
                    // #11513 - empty collections cannot be completed
                    $status['completed'] = false;
                } else {
                    foreach ($grouped_items as $grouping_id => $grouping) {
                        $isGrouping = $grouping_id ? true : false;
                        $status = self::determineGroupingStatus(
                            $status,
                            $grouping,
                            $a_usr_id,
                            $isGrouping
                        );
                    }
                }

                if ($status['completed']) {
                    if (!$this->isMember((int) $a_obj_id, (int) $a_user_id)) {
                        return self::LP_STATUS_IN_PROGRESS_NUM;
                    }

                    return self::LP_STATUS_COMPLETED_NUM;
                }

                if ($status['failed']) {
                    return self::LP_STATUS_FAILED_NUM;
                }

                if ($status['in_progress']) {
                    return self::LP_STATUS_IN_PROGRESS_NUM;
                }
                break;
        }
        return self::LP_STATUS_NOT_ATTEMPTED_NUM;
    }

    /**
     * Determine grouping status
     */
    public static function determineGroupingStatus(
        array $status,
        array $gr_info,
        int $user_id,
        bool $is_grouping
    ): array {
        global $DIC;

        $ilObjDataCache = $DIC['ilObjDataCache'];

        $items = $gr_info['items'];
        if ($is_grouping) {
            $max_allowed_failed = count($items) - $gr_info['num_obligatory'];
            $required_completed = $gr_info['num_obligatory'];
        } else {
            $max_allowed_failed = 0;
            $required_completed = count($items);
        }

        // Required for grouping with a number of obligatory items
        $num_failed = 0;
        $num_completed = 0;

        foreach ($items as $item_id) {
            $item_id = $ilObjDataCache->lookupObjId((int) $item_id);
            $gr_status = ilLPStatusWrapper::_determineStatus(
                $item_id,
                $user_id
            );

            if ($gr_status == self::LP_STATUS_FAILED_NUM) {
                if (++$num_failed > $max_allowed_failed) {
                    $status['failed'] = true;
                    $status['completed'] = false;
                    return $status;
                }
            }
            if ($gr_status == self::LP_STATUS_COMPLETED_NUM) {
                if (++$num_completed >= $required_completed) {
                    return $status;
                }
            }
        }
        // Not completed since returned above
        $status['completed'] = false;
        return $status;
    }

    /**
     * @param int $objId
     * @param int $usrId
     * @return bool
     */
    protected function isMember(int $objId, int $usrId): bool
    {
        switch ($this->ilObjDataCache->lookupType($objId)) {
            case 'crs':
                $participants = ilCourseParticipant::_getInstanceByObjId($objId, $usrId);
                return $participants->isMember();

            case 'grp':
                $participants = ilGroupParticipants::_getInstanceByObjId($objId);
                return $participants->isMember($usrId);

            case 'fold':
                $folderRefIds = ilObject::_getAllReferences($objId);
                $folderRefId = current($folderRefIds);
                if ($crsRefId = $this->tree->checkForParentType($folderRefId, 'crs')) {
                    $participants = ilCourseParticipant::_getInstanceByObjId(ilObject::_lookupObjId($crsRefId), $usrId);
                    return $participants->isMember();
                }

                if ($grpRefId = $this->tree->checkForParentType($folderRefId, 'grp')) {
                    $participants = ilGroupParticipants::_getInstanceByObjId(ilObject::_lookupObjId($grpRefId));
                    return $participants->isMember($usrId);
                }
                break;

            case 'lso':
                $participants = ilLearningSequenceParticipants::_getInstanceByObjId($objId);
                return $participants->isMember($objId);
        }

        return true;
    }

    /**
     * Get members for object
     * @param int $a_obj_id
     * @return int[]
     */
    protected static function getMembers(int $a_obj_id): array
    {
        global $DIC;

        $ilObjDataCache = $DIC['ilObjDataCache'];
        $tree = $DIC['tree'];

        switch ($ilObjDataCache->lookupType($a_obj_id)) {
            case 'crs':
                $member_obj = ilCourseParticipants::_getInstanceByObjId(
                    $a_obj_id
                );
                return $member_obj->getMembers();

            case 'grp':
                $member_obj = ilGroupParticipants::_getInstanceByObjId(
                    $a_obj_id
                );
                return $member_obj->getMembers();

            case 'fold':
                $folder_ref_ids = ilObject::_getAllReferences($a_obj_id);
                $folder_ref_id = current($folder_ref_ids);
                if ($crs_id = $tree->checkForParentType(
                    $folder_ref_id,
                    'crs'
                )) {
                    $member_obj = ilCourseParticipants::_getInstanceByObjId(
                        ilObject::_lookupObjId($crs_id)
                    );
                    return $member_obj->getMembers();
                }
                break;

            case 'lso':
                $member_obj = ilLearningSequenceParticipants::_getInstanceByObjId(
                    $a_obj_id
                );
                return $member_obj->getMembers();
                break;
        }

        return array();
    }

    /**
     * Get completed users for object
     */
    public static function _lookupCompletedForObject(
        int $a_obj_id,
        ?array $a_user_ids = null
    ): array {
        if (!$a_user_ids) {
            $a_user_ids = self::getMembers($a_obj_id);
            if (!$a_user_ids) {
                return array();
            }
        }
        return self::_lookupStatusForObject(
            $a_obj_id,
            self::LP_STATUS_COMPLETED_NUM,
            $a_user_ids
        );
    }

    /**
     * Get failed users for object
     */
    public static function _lookupFailedForObject(
        int $a_obj_id,
        ?array $a_user_ids = null
    ): array {
        if (!$a_user_ids) {
            $a_user_ids = self::getMembers($a_obj_id);
            if (!$a_user_ids) {
                return array();
            }
        }
        return self::_lookupStatusForObject(
            $a_obj_id,
            self::LP_STATUS_FAILED_NUM,
            $a_user_ids
        );
    }

    /**
     * Get in progress users for object
     */
    public static function _lookupInProgressForObject(
        int $a_obj_id,
        ?array $a_user_ids = null
    ): array {
        if (!$a_user_ids) {
            $a_user_ids = self::getMembers($a_obj_id);
            if (!$a_user_ids) {
                return array();
            }
        }
        return self::_lookupStatusForObject(
            $a_obj_id,
            self::LP_STATUS_IN_PROGRESS_NUM,
            $a_user_ids
        );
    }
}
