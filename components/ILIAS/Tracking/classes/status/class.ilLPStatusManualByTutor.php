<?php

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

declare(strict_types=0);

/**
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ilCtrl_Calls
 * @ingroup ServicesTracking
 */
class ilLPStatusManualByTutor extends ilLPStatus
{
    /**
     * get not attempted
     */
    public static function _getNotAttempted(int $a_obj_id): array
    {
        $users = array();

        $members = self::getMembers($a_obj_id);
        if ($members) {
            // diff in progress and completed (use stored result in LPStatusWrapper)
            $users = array_diff(
                $members,
                ilLPStatusWrapper::_getInProgress($a_obj_id)
            );
            $users = array_diff(
                $users,
                ilLPStatusWrapper::_getCompleted($a_obj_id)
            );
        }

        return $users;
    }

    /**
     * get in progress
     * @access public
     * @param int object id
     * @return array int Array of user ids
     */
    public static function _getInProgress(int $a_obj_id): array
    {
        $users = ilChangeEvent::lookupUsersInProgress($a_obj_id);

        // Exclude all users with status completed.
        $users = array_diff(
            $users,
            ilLPStatusWrapper::_getCompleted($a_obj_id)
        );

        if ($users) {
            // Exclude all non members
            $users = array_intersect(self::getMembers($a_obj_id), $users);
        }

        return $users;
    }

    public static function _getCompleted(int $a_obj_id): array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $usr_ids = array();

        $query = "SELECT DISTINCT(usr_id) user_id FROM ut_lp_marks " .
            "WHERE obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " " .
            "AND completed = '1' ";

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $usr_ids[] = (int) $row->user_id;
        }

        if ($usr_ids) {
            // Exclude all non members
            $usr_ids = array_intersect(self::getMembers($a_obj_id), $usr_ids);
        }
        return $usr_ids;
    }

    /**
     * Determine status
     */
    public function determineStatus(
        int $a_obj_id,
        int $a_usr_id,
        object $a_obj = null
    ): int {
        global $DIC;

        $ilObjDataCache = $DIC['ilObjDataCache'];

        $status = self::LP_STATUS_NOT_ATTEMPTED_NUM;
        switch ($this->ilObjDataCache->lookupType($a_obj_id)) {
            case "crs":
            case "grp":
                // completed?
                $set = $this->db->query(
                    $q = "SELECT usr_id FROM ut_lp_marks " .
                        "WHERE obj_id = " . $this->db->quote(
                            $a_obj_id,
                            'integer'
                        ) . " " .
                        "AND usr_id = " . $this->db->quote(
                            $a_usr_id,
                            'integer'
                        ) . " " .
                        "AND completed = '1' "
                );
                if ($rec = $this->db->fetchAssoc($set)) {
                    $status = self::LP_STATUS_COMPLETED_NUM;
                } else {
                    if (ilChangeEvent::hasAccessed($a_obj_id, $a_usr_id)) {
                        $status = self::LP_STATUS_IN_PROGRESS_NUM;
                    }
                }
                break;
        }
        return $status;
    }

    public function refreshStatus(int $a_obj_id, ?array $a_users = null): void
    {
        parent::refreshStatus($a_obj_id, $a_users);

        if (ilObject::_lookupType($a_obj_id) !== 'crs') {
            return;
        }

        $course_gui = new ilObjCourseGUI('', $a_obj_id, false);

        $in_progress = ilLPStatusWrapper::_getInProgress($a_obj_id);
        $completed = ilLPStatusWrapper::_getCompleted($a_obj_id);
        $failed = ilLPStatusWrapper::_getFailed($a_obj_id);
        $not_attempted = ilLPStatusWrapper::_getNotAttempted($a_obj_id);
        $all_active_users = array_unique(
            array_merge($in_progress, $completed, $failed, $not_attempted)
        );

        foreach ($all_active_users as $usr_id) {
            $course_gui->updateLPFromStatus(
                $usr_id,
                ilParticipants::_hasPassed($a_obj_id, $usr_id)
            );
        }
    }

    /**
     * Get members for object
     */
    protected static function getMembers(int $a_obj_id): array
    {
        global $DIC;

        $ilObjDataCache = $DIC['ilObjDataCache'];

        switch ($ilObjDataCache->lookupType($a_obj_id)) {
            case 'crs':
            case 'grp':
                return ilParticipants::getInstanceByObjId(
                    $a_obj_id
                )->getMembers();
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
        return array();
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
