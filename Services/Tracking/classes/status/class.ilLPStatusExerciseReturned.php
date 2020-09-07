<?php
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
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
*
* @package ilias-tracking
*
*/

include_once 'Services/Tracking/classes/class.ilLPStatus.php';
include_once 'Services/Tracking/classes/class.ilLearningProgress.php';

class ilLPStatusExerciseReturned extends ilLPStatus
{
    public function __construct($a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        parent::__construct($a_obj_id);
        $this->db = $ilDB;
    }

    public static function _getNotAttempted($a_obj_id)
    {
        $users = array();
        
        $members = self::getMembers($a_obj_id);
        if ($members) {
            $users = array_diff($members, ilLPStatusWrapper::_getInProgress($a_obj_id));
            $users = array_diff($users, ilLPStatusWrapper::_getCompleted($a_obj_id));
            $users = array_diff($users, ilLPStatusWrapper::_getFailed($a_obj_id));
        }
        
        return $users;
    }

    public static function _getInProgress($a_obj_id)
    {
        include_once './Modules/Exercise/classes/class.ilExerciseMembers.php';
        include_once './Services/Tracking/classes/class.ilChangeEvent.php';
        $users = ilExerciseMembers::_getReturned($a_obj_id);
        $all = ilChangeEvent::lookupUsersInProgress($a_obj_id);
        $users = $users + $all;

        $users = array_diff($users, ilLPStatusWrapper::_getCompleted($a_obj_id));
        $users = array_diff($users, ilLPStatusWrapper::_getFailed($a_obj_id));
        
        if ($users) {
            // Exclude all non members
            $users = array_intersect(self::getMembers($a_obj_id), (array) $users);
        }

        return $users;
    }

    public static function _getCompleted($a_obj_id)
    {
        include_once './Modules/Exercise/classes/class.ilExerciseMembers.php';
        $ret = ilExerciseMembers::_getPassedUsers($a_obj_id);
        return $ret ? $ret : array();
    }

    public static function _getFailed($a_obj_id)
    {
        include_once './Modules/Exercise/classes/class.ilExerciseMembers.php';
        $failed = ilExerciseMembers::_getFailedUsers($a_obj_id);
        return $failed ? $failed : array();
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
        
        $status = self::LP_STATUS_NOT_ATTEMPTED_NUM;
        switch ($ilObjDataCache->lookupType($a_obj_id)) {
            case 'exc':
                include_once './Services/Tracking/classes/class.ilChangeEvent.php';
                include_once './Modules/Exercise/classes/class.ilExerciseMembers.php';
                if (ilChangeEvent::hasAccessed($a_obj_id, $a_user_id) ||
                    ilExerciseMembers::_hasReturned($a_obj_id, $a_user_id)) {
                    $status = self::LP_STATUS_IN_PROGRESS_NUM;
                }
                $ex_stat = ilExerciseMembers::_lookupStatus($a_obj_id, $a_user_id);
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
     * @param int $a_obj_id
     * @return array
     */
    protected static function getMembers($a_obj_id)
    {
        include_once './Modules/Exercise/classes/class.ilExerciseMembers.php';
        return ilExerciseMembers::_getMembers($a_obj_id);
    }

    /**
     * Get completed users for object
     *
     * @param int $a_obj_id
     * @param array $a_user_ids
     * @return array
     */
    public static function _lookupCompletedForObject($a_obj_id, $a_user_ids = null)
    {
        if (!$a_user_ids) {
            $a_user_ids = self::getMembers($a_obj_id);
            if (!$a_user_ids) {
                return array();
            }
        }
        return self::_lookupStatusForObject($a_obj_id, self::LP_STATUS_COMPLETED_NUM, $a_user_ids);
    }
    
    /**
     * Get failed users for object
     *
     * @param int $a_obj_id
     * @param array $a_user_ids
     * @return array
     */
    public static function _lookupFailedForObject($a_obj_id, $a_user_ids = null)
    {
        if (!$a_user_ids) {
            $a_user_ids = self::getMembers($a_obj_id);
            if (!$a_user_ids) {
                return array();
            }
        }
        return self::_lookupStatusForObject($a_obj_id, self::LP_STATUS_FAILED_NUM, $a_user_ids);
    }
    
    /**
     * Get in progress users for object
     *
     * @param int $a_obj_id
     * @param array $a_user_ids
     * @return array
     */
    public static function _lookupInProgressForObject($a_obj_id, $a_user_ids = null)
    {
        if (!$a_user_ids) {
            $a_user_ids = self::getMembers($a_obj_id);
            if (!$a_user_ids) {
                return array();
            }
        }
        return self::_lookupStatusForObject($a_obj_id, self::LP_STATUS_IN_PROGRESS_NUM, $a_user_ids);
    }
}
