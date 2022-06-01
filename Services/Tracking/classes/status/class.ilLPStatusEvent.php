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
class ilLPStatusEvent extends ilLPStatus
{
    public static function _getNotAttempted(int $a_obj_id) : array
    {
        $status_info = ilLPStatusWrapper::_getStatusInfo($a_obj_id);

        $users = array();

        $members = self::getMembers($status_info['crs_id'], true);
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

    public static function _getInProgress(int $a_obj_id) : array
    {
        $status_info = ilLPStatusWrapper::_getStatusInfo($a_obj_id);

        // If registration is disabled in_progress is not available
        if (!$status_info['registration']) {
            return array();
        }
        // If event has occured in_progress is impossible
        if ($status_info['starting_time'] < time()) {
            return array();
        }

        // Otherwise all users who registered will get the status in progress
        return $status_info['registered_users'] ?: array();
    }

    public static function _getCompleted(int $a_obj_id) : array
    {
        $status_info = ilLPStatusWrapper::_getStatusInfo($a_obj_id);
        return $status_info['participated_users'] ?: array();
    }

    public static function _getStatusInfo(int $a_obj_id) : array
    {
        $tree = $GLOBALS['DIC']->repositoryTree();

        $references = ilObject::_getAllReferences($a_obj_id);
        $ref_id = end($references);

        $member_ref_id = null;
        if ($id = $tree->checkForParentType($ref_id, 'grp')) {
            $member_ref_id = $id;
        } elseif ($id = $tree->checkForParentType($ref_id, 'crs')) {
            $member_ref_id = $id;
        }

        $status_info = array();
        $status_info['crs_id'] = ilObject::_lookupObjId($member_ref_id);
        $status_info['registration'] = ilObjSession::_lookupRegistrationEnabled(
            $a_obj_id
        );
        $status_info['title'] = ilObject::_lookupTitle($a_obj_id);
        $status_info['description'] = ilObject::_lookupDescription($a_obj_id);

        $time_info = ilSessionAppointment::_lookupAppointment($a_obj_id);
        $status_info['starting_time'] = $time_info['start'];
        $status_info['ending_time'] = $time_info['end'];
        $status_info['fullday'] = $time_info['fullday'];

        $status_info['registered_users'] = ilEventParticipants::_getRegistered(
            $a_obj_id
        );
        $status_info['participated_users'] = ilEventParticipants::_getParticipated(
            $a_obj_id
        );

        return $status_info;
    }

    public function determineStatus(
        int $a_obj_id,
        int $a_usr_id,
        object $a_obj = null
    ) : int {
        global $DIC;

        $ilObjDataCache = $DIC['ilObjDataCache'];

        $status = self::LP_STATUS_NOT_ATTEMPTED_NUM;
        switch ($this->ilObjDataCache->lookupType($a_obj_id)) {
            case 'sess':

                $time_info = ilSessionAppointment::_lookupAppointment(
                    $a_obj_id
                );
                $registration = ilObjSession::_lookupRegistrationEnabled(
                    $a_obj_id
                );

                // If registration is disabled in_progress is not available
                // If event has occured in_progress is impossible
                if ($registration && $time_info['start'] >= time()) {
                    // is user registered -> in progress
                    if (ilEventParticipants::_isRegistered(
                        $a_usr_id,
                        $a_obj_id
                    )) {
                        $status = self::LP_STATUS_IN_PROGRESS_NUM;
                    }
                }
                if (ilEventParticipants::_hasParticipated(
                    $a_usr_id,
                    $a_obj_id
                )) {
                    $status = self::LP_STATUS_COMPLETED_NUM;
                }
                break;
        }
        return $status;
    }

    /**
     * Get members for object
     */
    protected static function getMembers(
        int $a_obj_id,
        bool $a_is_crs_id = false
    ) : array {
        if (!$a_is_crs_id) {
            $tree = $GLOBALS['DIC']->repositoryTree();
            $references = ilObject::_getAllReferences($a_obj_id);
            $ref_id = end($references);

            $member_ref_id = null;
            if ($id = $tree->checkForParentType($ref_id, 'grp')) {
                $member_ref_id = $id;
            } elseif ($id = $tree->checkForParentType($ref_id, 'crs')) {
                $member_ref_id = $id;
            } else {
                return [];
            }
            $member_obj_id = ilObject::_lookupObjId($member_ref_id);
        } else {
            $member_obj_id = $a_obj_id;
        }

        $member_obj = ilParticipants::getInstanceByObjId($member_obj_id);
        return $member_obj->getMembers();
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
        return array();
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
