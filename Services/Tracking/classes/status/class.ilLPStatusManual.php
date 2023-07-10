<?php

declare(strict_types=0);
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author     Stefan Meyer <meyer@leifos.com>
 * @ingroup    ServicesTracking
 */
class ilLPStatusManual extends ilLPStatus
{
    public static function _getInProgress(int $a_obj_id): array
    {
        $users = ilChangeEvent::lookupUsersInProgress($a_obj_id);

        // Exclude all users with status completed.
        return array_diff($users, ilLPStatusWrapper::_getCompleted($a_obj_id));
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
        $ilDB = $DIC['ilDB'];

        $status = self::LP_STATUS_NOT_ATTEMPTED_NUM;
        switch ($this->ilObjDataCache->lookupType($a_obj_id)) {
            case 'lm':
            case 'copa':
            case 'htlm':
                if (ilChangeEvent::hasAccessed($a_obj_id, $a_usr_id)) {
                    $status = self::LP_STATUS_IN_PROGRESS_NUM;

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
                    }
                }
                break;
        }
        return $status;
    }

    /**
     * Get failed users for object
     * @param int $a_obj_id
     * @param array|null $a_user_ids
     * @return array
     */
    public static function _lookupFailedForObject(
        int $a_obj_id,
        ?array $a_user_ids = null
    ): array {
        return array();
    }
}
