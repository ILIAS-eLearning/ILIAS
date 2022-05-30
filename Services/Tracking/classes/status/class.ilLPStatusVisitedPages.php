<?php declare(strict_types=0);

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author     Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version    $Id$
 * @ingroup    ServicesTracking
 */
class ilLPStatusVisitedPages extends ilLPStatus
{
    public static function _getInProgress(int $a_obj_id) : array
    {
        $users = ilChangeEvent::lookupUsersInProgress($a_obj_id);
        $users = array_diff(
            $users,
            ilLPStatusWrapper::_getCompleted($a_obj_id)
        );
        return $users;
    }

    public static function _getCompleted(int $a_obj_id) : array
    {
        $users = array();

        $all_page_ids = self::getLMPages($a_obj_id);
        foreach (self::getVisitedPages(
            $a_obj_id
        ) as $user_id => $user_page_ids) {
            if (!(bool) sizeof(array_diff($all_page_ids, $user_page_ids))) {
                $users[] = $user_id;
            }
        }

        return $users;
    }

    public function determineStatus(
        int $a_obj_id,
        int $a_usr_id,
        object $a_obj = null
    ) : int {
        $status = self::LP_STATUS_NOT_ATTEMPTED_NUM;
        switch (ilObject::_lookupType($a_obj_id)) {
            case 'lm':
                if (ilChangeEvent::hasAccessed($a_obj_id, $a_usr_id)) {
                    $status = self::LP_STATUS_IN_PROGRESS_NUM;

                    if (self::hasVisitedAllPages($a_obj_id, $a_usr_id)) {
                        $status = self::LP_STATUS_COMPLETED_NUM;
                    }
                }
                break;
        }

        return $status;
    }

    public function determinePercentage(
        int $a_obj_id,
        int $a_usr_id,
        ?object $a_obj = null
    ) : int {
        $all_page_ids = sizeof(self::getLMPages($a_obj_id));
        if (!$all_page_ids) {
            return 0;
        }
        $user_page_ids = sizeof(self::getVisitedPages($a_obj_id, $a_usr_id));
        return (int) floor($user_page_ids / $all_page_ids * 100);
    }

    protected static function hasVisitedAllPages(
        int $a_obj_id,
        int $a_user_id
    ) : bool {
        $all_page_ids = self::getLMPages($a_obj_id);
        if (!sizeof($all_page_ids)) {
            return false;
        }
        $user_page_ids = self::getVisitedPages($a_obj_id, $a_user_id);
        return !(bool) array_diff($all_page_ids, $user_page_ids);
    }

    protected static function getLMPages(int $a_obj_id) : array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $res = array();

        $set = $ilDB->query(
            "SELECT lm_data.obj_id" .
            " FROM lm_data" .
            " JOIN lm_tree ON (lm_tree.child = lm_data.obj_id)" .
            " WHERE lm_tree.lm_id = " . $ilDB->quote($a_obj_id, "integer") .
            " AND lm_data.type = " . $ilDB->quote("pg", "text")
        );
        while ($row = $ilDB->fetchAssoc($set)) {
            // only active pages (time-based activation not supported)
            if (ilPageObject::_lookupActive($row["obj_id"], "lm")) {
                $res[] = (int) $row["obj_id"];
            }
        }
        return $res;
    }

    protected static function getVisitedPages(
        int $a_obj_id,
        ?int $a_user_id = null
    ) : array {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $res = array();

        $all_page_ids = self::getLMPages($a_obj_id);
        if (!sizeof($all_page_ids)) {
            return $res;
        }

        $sql = "SELECT obj_id, usr_id" .
            " FROM lm_read_event" .
            " WHERE " . $ilDB->in("obj_id", $all_page_ids, "", "integer");

        if ($a_user_id) {
            $sql .= " AND usr_id = " . $ilDB->quote($a_user_id, "integer");
        }

        $set = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($set)) {
            $res[(int) $row["usr_id"]][] = (int) $row["obj_id"];
        }

        if ($a_user_id) {
            $res = $res[$a_user_id] ?? [];
        }

        return $res;
    }
}
