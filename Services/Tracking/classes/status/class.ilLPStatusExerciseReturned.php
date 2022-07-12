<?php declare(strict_types=0);
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
class ilLPStatusExerciseReturned extends ilLPStatus
{
    public static function _getNotAttempted(int $a_obj_id) : array
    {
        $users = array();

        $members = self::getMembers($a_obj_id);
        if ($members) {
            $users = array_diff(
                $members,
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

    public static function _getInProgress(int $a_obj_id) : array
    {
        $users = ilExerciseMembers::_getReturned($a_obj_id);
        $all = ilChangeEvent::lookupUsersInProgress($a_obj_id);
        $users = $users + $all;

        $users = array_diff(
            $users,
            ilLPStatusWrapper::_getCompleted($a_obj_id)
        );
        $users = array_diff($users, ilLPStatusWrapper::_getFailed($a_obj_id));

        if ($users) {
            // Exclude all non members
            $users = array_intersect(self::getMembers($a_obj_id), $users);
        }

        return $users;
    }

    public static function _getCompleted(int $a_obj_id) : array
    {
        return ilExerciseMembers::_getPassedUsers($a_obj_id);
    }

    public static function _getFailed(int $a_obj_id) : array
    {
        return ilExerciseMembers::_getFailedUsers($a_obj_id);
    }

    /**
     * Determine status
     */
    public function determineStatus(
        int $a_obj_id,
        int $a_usr_id,
        object $a_obj = null
    ) : int {
        global $DIC;

        $ilObjDataCache = $DIC['ilObjDataCache'];

        $status = self::LP_STATUS_NOT_ATTEMPTED_NUM;
        switch ($this->ilObjDataCache->lookupType($a_obj_id)) {
            case 'exc':
                if (ilChangeEvent::hasAccessed($a_obj_id, $a_usr_id) ||
                    ilExerciseMembers::_hasReturned($a_obj_id, $a_usr_id)) {
                    $status = self::LP_STATUS_IN_PROGRESS_NUM;
                }
                $ex_stat = ilExerciseMembers::_lookupStatus(
                    $a_obj_id,
                    $a_usr_id
                );
                if ($ex_stat == "passed") {
                    $status = self::LP_STATUS_COMPLETED_NUM;
                }
                if ($ex_stat == "failed") {
                    $status = self::LP_STATUS_FAILED_NUM;
                }
                break;
        }
        return $status;
    }

    /**
     * Get members for object
     */
    protected static function getMembers(int $a_obj_id)
    {
        return ilExerciseMembers::_getMembers($a_obj_id);
    }

    /**
     * Get completed users for object
     */
    public static function _lookupCompletedForObject(
        int $a_obj_id,
        ?array $a_user_ids = null
    ) : array {
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
    ) : array {
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
    ) : array {
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
