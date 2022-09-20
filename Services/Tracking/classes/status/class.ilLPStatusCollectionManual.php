<?php

declare(strict_types=0);
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @package ilias-tracking
 */
class ilLPStatusCollectionManual extends ilLPStatus
{
    public static function _getInProgress(int $a_obj_id): array
    {
        $status_info = ilLPStatusWrapper::_getStatusInfo($a_obj_id);

        // find any completed item
        $users = array();
        if (isset($status_info['completed'])) {
            foreach ($status_info['completed'] as $in_progress) {
                $users = array_merge($users, $in_progress);
            }
            $users = array_unique($users);
        }
        // remove all users which have completed ALL items
        return array_diff($users, ilLPStatusWrapper::_getCompleted($a_obj_id));
    }

    public static function _getCompleted(int $a_obj_id): array
    {
        $status_info = ilLPStatusWrapper::_getStatusInfo($a_obj_id);

        $counter = 0;
        $users = array();
        foreach ($status_info['items'] as $item_id) {
            $tmp_users = $status_info['completed'][$item_id];

            if (!$counter++) {
                $users = $tmp_users;
            } else {
                $users = array_intersect($users, $tmp_users);
            }
        }
        return array_unique($users);
    }

    public static function _getStatusInfo(int $a_obj_id): array
    {
        $status_info = array();

        $olp = ilObjectLP::getInstance($a_obj_id);
        $collection = $olp->getCollectionInstance();
        if ($collection) {
            // @todo check if obj_id can be removed
            $status_info["items"] = $collection->getItems($a_obj_id);

            foreach ($status_info["items"] as $item_id) {
                $status_info["completed"][$item_id] = array();
            }

            $ref_ids = ilObject::_getAllReferences($a_obj_id);
            $ref_id = end($ref_ids);
            $possible_items = $collection->getPossibleItems($ref_id);
            $chapter_ids = array_intersect(
                array_keys($possible_items),
                $status_info["items"]
            );

            // fix order (adapt from possible items)
            $status_info["items"] = $chapter_ids;

            if ($chapter_ids) {
                $status = self::_getObjectStatus($a_obj_id);

                foreach ($chapter_ids as $item_id) {
                    $status_info["item_titles"][$item_id] = $possible_items[$item_id]["title"];

                    if (isset($status[$item_id])) {
                        foreach ($status[$item_id] as $user_id => $user_status) {
                            if ($user_status) {
                                $status_info["completed"][$item_id][] = $user_id;
                            }
                        }
                    }
                }
            }
        }
        return $status_info;
    }

    public function determineStatus(
        int $a_obj_id,
        int $a_usr_id,
        object $a_obj = null
    ): int {
        $info = self::_getStatusInfo($a_obj_id);

        if (isset($info["completed"])) {
            $completed = true;
            $in_progress = false;
            foreach ($info["completed"] as $user_ids) {
                // has completed at least 1 item
                if (in_array($a_usr_id, $user_ids)) {
                    $in_progress = true;
                } // must have completed all items to complete collection
                else {
                    $completed = false;
                }
            }
            if ($completed) {
                return self::LP_STATUS_COMPLETED_NUM;
            }
            if ($in_progress) {
                return self::LP_STATUS_IN_PROGRESS_NUM;
            }
        }

        return self::LP_STATUS_NOT_ATTEMPTED_NUM;
    }

    public static function _getObjectStatus(
        $a_obj_id,
        $a_user_id = null
    ): array {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $res = array();

        $sql = "SELECT subitem_id, completed, usr_id, last_change" .
            " FROM ut_lp_coll_manual" .
            " WHERE obj_id = " . $ilDB->quote($a_obj_id, "integer");
        if ($a_user_id) {
            $sql .= " AND usr_id = " . $ilDB->quote($a_user_id, "integer");
        }
        $set = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($set)) {
            if (!$a_user_id) {
                $res[(int) $row["subitem_id"]][(int) $row["usr_id"]] = (int) $row["completed"];
            } else {
                $res[(int) $row["subitem_id"]] = array((int) $row["completed"],
                                                       $row["last_change"]
                );
            }
        }
        return $res;
    }

    public static function _setObjectStatus(
        int $a_obj_id,
        int $a_user_id,
        array $a_completed = null
    ): void {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $now = time();

        if (!$a_completed) {
            $a_completed = array();
        }

        $olp = ilObjectLP::getInstance($a_obj_id);
        $collection = $olp->getCollectionInstance();
        if ($collection) {
            $existing = self::_getObjectStatus($a_obj_id, $a_user_id);

            foreach ($collection->getItems() as $item_id) {
                if (isset($existing[$item_id])) {
                    // value changed
                    if ((!$existing[$item_id][0] && in_array(
                        $item_id,
                        $a_completed
                    )) ||
                        ($existing[$item_id][0] && !in_array(
                            $item_id,
                            $a_completed
                        ))) {
                        $ilDB->manipulate(
                            "UPDATE ut_lp_coll_manual SET " .
                            " completed = " . $ilDB->quote(
                                in_array($item_id, $a_completed),
                                "integer"
                            ) .
                            " , last_change = " . $ilDB->quote(
                                $now,
                                "integer"
                            ) .
                            " WHERE obj_id = " . $ilDB->quote(
                                $a_obj_id,
                                "integer"
                            ) .
                            " AND usr_id = " . $ilDB->quote(
                                $a_user_id,
                                "integer"
                            ) .
                            " AND subitem_id = " . $ilDB->quote(
                                $item_id,
                                "integer"
                            )
                        );
                    }
                } elseif (in_array($item_id, $a_completed)) {
                    $ilDB->manipulate(
                        "INSERT INTO ut_lp_coll_manual" .
                        "(obj_id,usr_id,subitem_id,completed,last_change)" .
                        " VALUES (" . $ilDB->quote($a_obj_id, "integer") .
                        " , " . $ilDB->quote($a_user_id, "integer") .
                        " , " . $ilDB->quote($item_id, "integer") .
                        " , " . $ilDB->quote(1, "integer") .
                        " , " . $ilDB->quote($now, "integer") . ")"
                    );
                }
            }
        }

        ilLPStatusWrapper::_updateStatus($a_obj_id, $a_user_id);
    }
}
