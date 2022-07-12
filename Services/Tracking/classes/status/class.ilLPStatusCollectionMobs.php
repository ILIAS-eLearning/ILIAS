<?php declare(strict_types=0);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @package ServicesTracking
 */
class ilLPStatusCollectionMobs extends ilLPStatus
{
    public static function _getInProgress(int $a_obj_id) : array
    {
        $users = array();

        $status_info = ilLPStatusWrapper::_getStatusInfo($a_obj_id);
        if (isset($status_info["user_status"]["in_progress"])) {
            $users = $status_info["user_status"]["in_progress"];
        }
        return $users;
    }

    public static function _getCompleted(int $a_obj_id) : array
    {
        $users = array();

        $status_info = ilLPStatusWrapper::_getStatusInfo($a_obj_id);
        if (isset($status_info["user_status"]["completed"])) {
            $users = $status_info["user_status"]["completed"];
        }

        return $users;
    }

    public static function _getStatusInfo(int $a_obj_id) : array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $res = array();

        $coll_items = self::getCollectionItems($a_obj_id, true);

        $res["items"] = array_keys($coll_items);
        if (sizeof($res["items"])) {
            // titles
            foreach ($coll_items as $mob_id => $item) {
                $res["item_titles"][$mob_id] = $item["title"];
            }

            // status per item
            foreach ($res["items"] as $mob_id) {
                $res["completed"][$mob_id] = array();
                $res["in_progress"][$mob_id] = array();
            }

            $set = $ilDB->query(
                "SELECT obj_id, usr_id FROM read_event" .
                " WHERE " . $ilDB->in("obj_id", $res["items"], "", "integer")
            );
            while ($row = $ilDB->fetchAssoc($set)) {
                $res["completed"][(int) $row["obj_id"]][] = (int) $row["usr_id"];
            }

            // status per user
            $tmp = array();
            foreach ($res["items"] as $mob_id) {
                foreach ($res["completed"][$mob_id] as $user_id) {
                    $tmp[$user_id][] = (int) $mob_id;
                }
            }
            foreach ($tmp as $user_id => $completed_items) {
                if (sizeof($completed_items) == sizeof($res["items"])) {
                    $res["user_status"]["completed"][] = (int) $user_id;
                } else {
                    $res["user_status"]["in_progress"][] = (int) $user_id;
                }
            }
        }

        $users = ilChangeEvent::lookupUsersInProgress($a_obj_id);
        foreach ($users as $user_id) {
            if ((!isset($res["user_status"]["in_progress"]) || !in_array(
                $user_id,
                $res["user_status"]["in_progress"]
            )) &&
                (!isset($res["user_status"]["completed"]) || !in_array(
                    $user_id,
                    $res["user_status"]["completed"]
                ))) {
                $res["user_status"]["in_progress"][] = (int) $user_id;
            }
        }

        return $res;
    }

    protected static function getCollectionItems(
        $a_obj_id,
        $a_include_titles = false
    ) {
        $res = array();

        $olp = ilObjectLP::getInstance($a_obj_id);
        $collection = $olp->getCollectionInstance();
        if ($collection) {
            $possible = $collection->getPossibleItems();

            // there could be invalid items in the selection
            $valid = array_intersect(
                $collection->getItems(),
                array_keys($possible)
            );

            if ($a_include_titles) {
                foreach ($valid as $item_id) {
                    $res[$item_id] = $possible[$item_id];
                }
            } else {
                $res = $valid;
            }
        }
        return $res;
    }

    public function determineStatus(
        int $a_obj_id,
        int $a_usr_id,
        object $a_obj = null
    ) : int {
        $status = self::LP_STATUS_NOT_ATTEMPTED_NUM;
        if (ilChangeEvent::hasAccessed($a_obj_id, $a_usr_id)) {
            $status = self::LP_STATUS_IN_PROGRESS_NUM;
        }

        // an empty collection is always not attempted
        $items = self::getCollectionItems($a_obj_id);
        if (sizeof($items)) {
            // process mob status for user

            $found = array();

            $set = $this->db->query(
                "SELECT obj_id FROM read_event" .
                " WHERE usr_id = " . $this->db->quote($a_usr_id, "integer") .
                " AND " . $this->db->in("obj_id", $items, false, "integer")
            );
            while ($row = $this->db->fetchAssoc($set)) {
                $found[] = (int) $row["obj_id"];
            }

            if (sizeof($found)) {
                $status = self::LP_STATUS_IN_PROGRESS_NUM;

                if (sizeof($found) == sizeof($items)) {
                    $status = self::LP_STATUS_COMPLETED_NUM;
                }
            }
        }
        return $status;
    }
}
