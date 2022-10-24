<?php

declare(strict_types=0);

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

/**
 * Tracking query class. Put any complex queries into this class. Keep
 * tracking class small.
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesTracking
 */
class ilTrQuery
{
    public static function getObjectsStatusForUser(
        int $a_user_id,
        array $obj_refs
    ): array {
        global $DIC;

        $ilDB = $DIC->database();

        if (sizeof($obj_refs)) {
            $obj_ids = array_keys($obj_refs);
            self::refreshObjectsStatus($obj_ids, array($a_user_id));

            // prepare object view modes
            $view_modes = array();
            $query = "SELECT obj_id, view_mode FROM crs_settings" .
                " WHERE " . $ilDB->in("obj_id", $obj_ids, false, "integer");
            $set = $ilDB->query($query);
            while ($rec = $ilDB->fetchAssoc($set)) {
                $view_modes[(int) $rec["obj_id"]] = (int) $rec["view_mode"];
            }

            $sessions = self::getSessionData($a_user_id, $obj_ids);

            $query = "SELECT object_data.obj_id, title, CASE WHEN status IS NULL THEN " . ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM . " ELSE status END AS status," .
                " status_changed, percentage, read_count+childs_read_count AS read_count, spent_seconds+childs_spent_seconds AS spent_seconds," .
                " u_mode, type, visits, mark, u_comment" .
                " FROM object_data" .
                " LEFT JOIN ut_lp_settings ON (ut_lp_settings.obj_id = object_data.obj_id)" .
                " LEFT JOIN read_event ON (read_event.obj_id = object_data.obj_id AND read_event.usr_id = " . $ilDB->quote(
                    $a_user_id,
                    "integer"
                ) . ")" .
                " LEFT JOIN ut_lp_marks ON (ut_lp_marks.obj_id = object_data.obj_id AND ut_lp_marks.usr_id = " . $ilDB->quote(
                    $a_user_id,
                    "integer"
                ) . ")" .
                // " WHERE (u_mode IS NULL OR u_mode <> ".$ilDB->quote(ilLPObjSettings::LP_MODE_DEACTIVATED, "integer").")".
                " WHERE " . $ilDB->in(
                    "object_data.obj_id",
                    $obj_ids,
                    false,
                    "integer"
                ) .
                " ORDER BY title";
            $set = $ilDB->query($query);
            $result = array();
            while ($rec = $ilDB->fetchAssoc($set)) {
                $rec["comment"] = $rec["u_comment"];
                unset($rec["u_comment"]);

                $rec["ref_ids"] = $obj_refs[(int) $rec["obj_id"]];
                $rec["status"] = (int) $rec["status"];
                $rec["percentage"] = (int) $rec["percentage"];
                $rec["read_count"] = (int) $rec["read_count"];
                $rec["spent_seconds"] = (int) $rec["spent_seconds"];
                $rec["u_mode"] = (int) $rec["u_mode"];

                if ($rec["type"] == "sess") {
                    $session = $sessions[(int) $rec["obj_id"]];
                    $rec["title"] = $session["title"];
                    // $rec["status"] = (int)$session["status"];
                }

                // lp mode might not match object/course view mode
                if ($rec["type"] == "crs" && $view_modes[$rec["obj_id"]] == ilCourseConstants::IL_CRS_VIEW_OBJECTIVE) {
                    $rec["u_mode"] = ilLPObjSettings::LP_MODE_OBJECTIVES;
                } elseif (!$rec["u_mode"]) {
                    $olp = ilObjectLP::getInstance($rec["obj_id"]);
                    $rec["u_mode"] = $olp->getCurrentMode();
                }

                // can be default mode
                if (/*$rec["u_mode"] != ilLPObjSettings::LP_MODE_DEACTIVATE*/ true) {
                    $result[] = $rec;
                }
            }
            return $result;
        }
        return [];
    }

    public static function getObjectivesStatusForUser(
        int $a_user_id,
        int $a_obj_id,
        array $a_objective_ids
    ): array {
        global $DIC;

        $ilDB = $DIC->database();

        $lo_lp_status = ilLOUserResults::getObjectiveStatusForLP(
            $a_user_id,
            $a_obj_id,
            $a_objective_ids
        );

        $query = "SELECT crs_id, crs_objectives.objective_id AS obj_id, title," . $ilDB->quote(
            "lobj",
            "text"
        ) . " AS type" .
            " FROM crs_objectives" .
            " WHERE " . $ilDB->in(
                "crs_objectives.objective_id",
                $a_objective_ids,
                false,
                "integer"
            ) .
            " AND active = " . $ilDB->quote(1, "integer") .
            " ORDER BY position";
        $set = $ilDB->query($query);
        $result = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $rec['crs_id'] = (int) $rec['crs_id'];
            $rec['obj_id'] = (int) $rec['obj_id'];
            if (array_key_exists($rec["obj_id"], $lo_lp_status)) {
                $rec["status"] = $lo_lp_status[$rec["obj_id"]];
            } else {
                $rec["status"] = ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
            }
            $result[] = $rec;
        }

        return $result;
    }

    public static function getSCOsStatusForUser(
        int $a_user_id,
        int $a_parent_obj_id,
        array $a_sco_ids
    ): array {
        self::refreshObjectsStatus(array($a_parent_obj_id), array($a_user_id));

        // import score from tracking data
        $scores_raw = $scores = array();
        $subtype = ilObjSAHSLearningModule::_lookupSubType($a_parent_obj_id);
        switch ($subtype) {
            case 'hacp':
            case 'aicc':
            case 'scorm':
                $module = new ilObjSCORMLearningModule($a_parent_obj_id, false);
                $scores_raw = $module->getTrackingDataAgg($a_user_id);
                break;

            case 'scorm2004':
                $module = new ilObjSCORM2004LearningModule(
                    $a_parent_obj_id,
                    false
                );
                $scores_raw = $module->getTrackingDataAgg($a_user_id);
                break;
        }
        if ($scores_raw) {
            foreach ($scores_raw as $item) {
                $scores[$item["sco_id"]] = $item["score"];
            }
            unset($module);
            unset($scores_raw);
        }

        $status_info = ilLPStatusWrapper::_getStatusInfo($a_parent_obj_id);

        $items = array();
        foreach ($a_sco_ids as $sco_id) {
            // #9719 - can have in_progress AND failed/completed
            if (in_array($a_user_id, $status_info["failed"][$sco_id])) {
                $status = ilLPStatus::LP_STATUS_FAILED;
            } elseif (in_array(
                $a_user_id,
                $status_info["completed"][$sco_id]
            )) {
                $status = ilLPStatus::LP_STATUS_COMPLETED;
            } elseif (in_array(
                $a_user_id,
                $status_info["in_progress"][$sco_id]
            )) {
                $status = ilLPStatus::LP_STATUS_IN_PROGRESS;
            } else {
                $status = ilLPStatus::LP_STATUS_NOT_ATTEMPTED;
            }

            $items[$sco_id] = array(
                "title" => $status_info["scos_title"][$sco_id],
                "status" => (int) $status,
                "type" => "sahs",
                "score" => (int) $scores[$sco_id]
            );
        }
        return $items;
    }

    /**
     * Get subitems status
     */
    public static function getSubItemsStatusForUser(
        int $a_user_id,
        int $a_parent_obj_id,
        array $a_item_ids
    ): array {
        self::refreshObjectsStatus(array($a_parent_obj_id), array($a_user_id));

        switch (ilObject::_lookupType($a_parent_obj_id)) {
            case "lm":
            case "mcst":
                $olp = ilObjectLP::getInstance($a_parent_obj_id);
                $collection = $olp->getCollectionInstance();
                if ($collection) {
                    $ref_ids = ilObject::_getAllReferences($a_parent_obj_id);
                    $ref_id = end($ref_ids);
                    $item_data = $collection->getPossibleItems($ref_id);
                }
                break;

            default:
                return array();
        }

        $status_info = ilLPStatusWrapper::_getStatusInfo($a_parent_obj_id);

        $items = array();
        foreach ($a_item_ids as $item_id) {
            if (!isset($item_data[$item_id])) {
                continue;
            }

            if (in_array($a_user_id, $status_info["completed"][$item_id])) {
                $status = ilLPStatus::LP_STATUS_COMPLETED;
            } elseif (in_array(
                $a_user_id,
                $status_info["in_progress"][$item_id]
            )) {
                $status = ilLPStatus::LP_STATUS_IN_PROGRESS;
            } else {
                $status = ilLPStatus::LP_STATUS_NOT_ATTEMPTED;
            }

            $items[$item_id] = array(
                "title" => $item_data[$item_id]["title"],
                "status" => (int) $status,
                "type" => self::getSubItemType($a_parent_obj_id)
            );
        }

        return $items;
    }

    public static function getUserDataForObject(
        int $a_ref_id,
        string $a_order_field = "",
        string $a_order_dir = "",
        int $a_offset = 0,
        int $a_limit = 9999,
        ?array $a_filters = null,
        ?array $a_additional_fields = null,
        ?int $check_agreement = null,
        ?array $privacy_fields = null
    ): array {
        global $DIC;

        $ilDB = $DIC->database();

        $fields = array("usr_data.usr_id", "login", "active");
        $udf = self::buildColumns($fields, $a_additional_fields);

        $where = array();
        $where[] = "usr_data.usr_id <> " . $ilDB->quote(
            ANONYMOUS_USER_ID,
            "integer"
        );

        // users
        $left = "";
        $a_users = self::getParticipantsForObject($a_ref_id);

        $obj_id = ilObject::_lookupObjectId($a_ref_id);
        self::refreshObjectsStatus(array($obj_id), $a_users);

        if (is_array($a_users)) {
            $left = "LEFT";
            $where[] = $ilDB->in("usr_data.usr_id", $a_users, false, "integer");
        }

        $query = " FROM usr_data " . $left . " JOIN read_event ON (read_event.usr_id = usr_data.usr_id" .
            " AND read_event.obj_id = " . $ilDB->quote(
                $obj_id,
                "integer"
            ) . ")" .
            " LEFT JOIN ut_lp_marks ON (ut_lp_marks.usr_id = usr_data.usr_id " .
            " AND ut_lp_marks.obj_id = " . $ilDB->quote(
                $obj_id,
                "integer"
            ) . ")" .
            " LEFT JOIN usr_pref ON (usr_pref.usr_id = usr_data.usr_id AND keyword = " . $ilDB->quote(
                "language",
                "text"
            ) . ")" .
            self::buildFilters($where, $a_filters);

        $queries = array(array("fields" => $fields, "query" => $query));

        // #9598 - if language is not in fields alias is missing
        if ($a_order_field == "language") {
            $a_order_field = "usr_pref.value";
        }

        // udf data is added later on, not in this query
        $udf_order = null;
        if (!$a_order_field) {
            $a_order_field = "login";
        } elseif (substr($a_order_field, 0, 4) == "udf_") {
            $udf_order = $a_order_field;
            $a_order_field = null;
        }
        $result = self::executeQueries(
            $queries,
            $a_order_field,
            $a_order_dir,
            $a_offset,
            $a_limit
        );

        self::getUDFAndHandlePrivacy(
            $result,
            $udf,
            $check_agreement,
            $privacy_fields,
            $a_filters
        );

        // as we cannot do this in the query, sort by custom field here
        // this will not work with pagination!
        if ($udf_order) {
            $result["set"] = ilArrayUtil::stableSortArray(
                $result["set"],
                $udf_order,
                $a_order_dir
            );
        }

        return $result;
    }

    /**
     * Handle privacy and add udf data to (user) result data
     */
    protected static function getUDFAndHandlePrivacy(
        array &$a_result,
        ?array $a_udf = null,
        ?int $a_check_agreement = null,
        ?array $a_privacy_fields = null,
        ?array $a_filters = null
    ): array {
        global $DIC;

        $ilDB = $DIC->database();

        if (!$a_result["cnt"]) {
            return [];
        }

        if (is_array($a_udf) && count($a_udf) > 0) {
            $query = "SELECT usr_id, field_id, value FROM udf_text WHERE " . $ilDB->in(
                "field_id",
                $a_udf,
                false,
                "integer"
            );
            $set = $ilDB->query($query);
            $udf = array();
            while ($row = $ilDB->fetchAssoc($set)) {
                $udf[(int) $row["usr_id"]]["udf_" . $row["field_id"]] = $row["value"];
            }
        }

        // (course/group) user agreement
        if ($a_check_agreement) {
            // admins/tutors (write-access) will never have agreement ?!
            $agreements = ilMemberAgreement::lookupAcceptedAgreements(
                $a_check_agreement
            );

            // public information for users
            $query = "SELECT usr_id FROM usr_pref WHERE keyword = " . $ilDB->quote(
                "public_profile",
                "text"
            ) .
                " AND value = " . $ilDB->quote(
                    "y",
                    "text"
                ) . " OR value = " . $ilDB->quote("g", "text");
            $set = $ilDB->query($query);
            $all_public = array();
            while ($row = $ilDB->fetchAssoc($set)) {
                $all_public[] = $row["usr_id"];
            }
            $query = "SELECT usr_id,keyword FROM usr_pref WHERE " . $ilDB->like(
                "keyword",
                "text",
                "public_%",
                false
            ) .
                " AND value = " . $ilDB->quote(
                    "y",
                    "text"
                ) . " AND " . $ilDB->in(
                    "usr_id",
                    $all_public,
                    false,
                    "integer"
                );
            $set = $ilDB->query($query);
            $public = array();
            while ($row = $ilDB->fetchAssoc($set)) {
                $public[$row["usr_id"]][] = substr($row["keyword"], 7);
            }
            unset($all_public);
        }

        foreach ($a_result["set"] as $idx => $row) {
            // add udf data
            if (isset($udf[$row["usr_id"]])) {
                $a_result["set"][$idx] = $row = array_merge(
                    $row,
                    $udf[$row["usr_id"]]
                );
            }

            // remove all private data - if active agreement and agreement not given by user
            if (sizeof($a_privacy_fields) && $a_check_agreement && !in_array(
                $row["usr_id"],
                $agreements
            )) {
                foreach ($a_privacy_fields as $field) {
                    // check against public profile
                    if (isset($row[$field]) && (!isset($public[$row["usr_id"]]) ||
                            !in_array($field, $public[$row["usr_id"]]))) {
                        // remove complete entry - offending field was filtered
                        if (isset($a_filters[$field])) {
                            // we cannot remove row because of pagination!
                            foreach (array_keys($row) as $col_id) {
                                $a_result["set"][$idx][$col_id] = null;
                            }
                            $a_result["set"][$idx]["privacy_conflict"] = true;
                            // unset($a_result["set"][$idx]);
                            break;
                        } // remove offending field
                        else {
                            $a_result["set"][$idx][$field] = false;
                        }
                    }
                }
            }
        }
        return [];
    }

    /**
     * Get all object-based tracking data for user and parent object
     */
    public static function getObjectsDataForUser(
        int $a_user_id,
        int $a_parent_obj_id,
        int $a_parent_ref_id,
        string $a_order_field = "",
        string $a_order_dir = "",
        int $a_offset = 0,
        int $a_limit = 9999,
        ?array $a_filters = null,
        ?array $a_additional_fields = null,
        bool $use_collection = true
    ): array {
        global $DIC;

        $ilDB = $DIC->database();

        $fields = array("object_data.obj_id", "title", "type");
        self::buildColumns($fields, $a_additional_fields);

        $objects = self::getObjectIds(
            $a_parent_obj_id,
            $a_parent_ref_id,
            $use_collection,
            true,
            array($a_user_id)
        );

        $query = " FROM object_data LEFT JOIN read_event ON (object_data.obj_id = read_event.obj_id AND" .
            " read_event.usr_id = " . $ilDB->quote(
                $a_user_id,
                "integer"
            ) . ")" .
            " LEFT JOIN ut_lp_marks ON (ut_lp_marks.usr_id = " . $ilDB->quote(
                $a_user_id,
                "integer"
            ) . " AND" .
            " ut_lp_marks.obj_id = object_data.obj_id)" .
            " WHERE " . $ilDB->in(
                "object_data.obj_id",
                $objects["object_ids"],
                false,
                "integer"
            ) .
            self::buildFilters(array(), $a_filters);

        $queries = array();
        $queries[] = array("fields" => $fields, "query" => $query);

        if (!in_array($a_order_field, $fields)) {
            $a_order_field = "title";
        }

        $result = self::executeQueries(
            $queries,
            $a_order_field,
            $a_order_dir,
            $a_offset,
            $a_limit
        );
        if ($result["cnt"]) {
            // session data
            $sessions = self::getSessionData(
                $a_user_id,
                $objects["object_ids"]
            );

            foreach ($result["set"] as $idx => $item) {
                if ($item["type"] == "sess") {
                    $session = $sessions[(int) $item["obj_id"]];
                    $result["set"][$idx]["title"] = $session["title"];
                    $result["set"][$idx]["sort_title"] = $session["e_start"];
                    // $result["set"][$idx]["status"] = (int)$session["status"];
                }

                $result["set"][$idx]["ref_id"] = $objects["ref_ids"][(int) $item["obj_id"]];
            }

            // scos data (:TODO: will not be part of offset/limit)
            if ($objects["scorm"]) {
                $subtype = ilObjSAHSLearningModule::_lookupSubType(
                    $a_parent_obj_id
                );
                if ($subtype == "scorm2004") {
                    $sobj = new ilObjSCORM2004LearningModule(
                        $a_parent_ref_id,
                        true
                    );
                    $scos_tracking = $sobj->getTrackingDataAgg(
                        $a_user_id,
                        true
                    );
                } else {
                    $sobj = new ilObjSCORMLearningModule(
                        $a_parent_ref_id,
                        true
                    );
                    $scos_tracking = array();
                    foreach ($sobj->getTrackingDataAgg($a_user_id) as $item) {
                        // format: hhhh:mm:ss ?!
                        if ($item["time"]) {
                            $time = explode(":", $item["time"]);
                            $item["time"] = $time[0] * 60 * 60 + $time[1] * 60 + $time[2];
                        }
                        $scos_tracking[(int) $item["sco_id"]] = array("session_time" => $item["time"]);
                    }
                }

                foreach ($objects["scorm"]["scos"] as $sco) {
                    $row = array("title" => $objects["scorm"]["scos_title"][$sco],
                                 "type" => "sco"
                    );

                    $status = ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
                    if (in_array(
                        $a_user_id,
                        $objects["scorm"]["completed"][$sco]
                    )) {
                        $status = ilLPStatus::LP_STATUS_COMPLETED_NUM;
                    } elseif (in_array(
                        $a_user_id,
                        $objects["scorm"]["failed"][$sco]
                    )) {
                        $status = ilLPStatus::LP_STATUS_FAILED_NUM;
                    } elseif (in_array(
                        $a_user_id,
                        $objects["scorm"]["in_progress"][$sco]
                    )) {
                        $status = ilLPStatus::LP_STATUS_IN_PROGRESS_NUM;
                    }
                    $row["status"] = $status;

                    // add available tracking data
                    if (isset($scos_tracking[$sco])) {
                        if (isset($scos_tracking[$sco]["last_access"])) {
                            $date = new ilDateTime(
                                $scos_tracking[$sco]["last_access"],
                                IL_CAL_DATETIME
                            );
                            $row["last_access"] = $date->get(IL_CAL_UNIX);
                        }
                        $row["spent_seconds"] = $scos_tracking[$sco]["session_time"];
                    }

                    $result["set"][] = $row;
                    $result["cnt"] = ($result["cnt"] ?? 0) + 1;
                }
            }

            // #15379 - objectives data
            if ($objects["objectives_parent_id"]) {
                $objtv_ids = ilCourseObjective::_getObjectiveIds(
                    $objects["objectives_parent_id"],
                    true
                );
                foreach (self::getObjectivesStatusForUser(
                    $a_user_id,
                    $objects["objectives_parent_id"],
                    $objtv_ids
                ) as $item) {
                    $result["set"][] = $item;
                    $result["cnt"] = ($result["cnt"] ?? 0) + 1;
                }
            }

            // subitem data
            if ($objects["subitems"]) {
                $sub_type = self::getSubItemType($a_parent_obj_id);
                foreach ($objects["subitems"]["items"] as $item_id) {
                    $row = array("title" => $objects["subitems"]["item_titles"][$item_id],
                                 "type" => $sub_type
                    );

                    $status = ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
                    if (in_array(
                        $a_user_id,
                        $objects["subitems"]["completed"][(int) $item_id]
                    )) {
                        $status = ilLPStatus::LP_STATUS_COMPLETED_NUM;
                    }
                    $row["status"] = $status;

                    $result["set"][] = $row;
                    $result["cnt"] = ($result["cnt"] ?? 0) + 1;
                }
            }
        }
        return $result;
    }

    /**
     * Get sub-item object type for parent
     */
    public static function getSubItemType(int $a_parent_obj_id): string
    {
        switch (ilObject::_lookupType($a_parent_obj_id)) {
            case "lm":
                return "st";

            case "mcst":
                return "mob";
        }
        return '';
    }

    /**
     * Get session data for given objects and user
     */
    protected static function getSessionData(
        int $a_user_id,
        array $obj_ids
    ): array {
        global $DIC;

        $ilDB = $DIC->database();
        $query = "SELECT obj_id, title, e_start, e_end, CASE WHEN participated = 1 THEN 2 WHEN registered = 1 THEN 1 ELSE NULL END AS status," .
            " mark, e_comment" .
            " FROM event" .
            " JOIN event_appointment ON (event.obj_id = event_appointment.event_id)" .
            " LEFT JOIN event_participants ON (event_participants.event_id = event.obj_id AND usr_id = " . $ilDB->quote(
                $a_user_id,
                "integer"
            ) . ")" .
            " WHERE " . $ilDB->in("obj_id", $obj_ids, false, "integer");
        $set = $ilDB->query($query);
        $sessions = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $rec["comment"] = $rec["e_comment"];
            unset($rec["e_comment"]);

            $date = ilDatePresentation::formatPeriod(
                new ilDateTime(
                    $rec["e_start"],
                    IL_CAL_DATETIME,
                    ilTimeZone::UTC
                ),
                new ilDateTime($rec["e_end"], IL_CAL_DATETIME, ilTimeZone::UTC)
            );

            if ($rec["title"]) {
                $rec["title"] = $date . ': ' . $rec["title"];
            } else {
                $rec["title"] = $date;
            }
            $sessions[(int) $rec["obj_id"]] = $rec;
        }
        return $sessions;
    }

    /**
     * Get all aggregated tracking data for parent object
     * :TODO: sorting, offset, limit, objectives, collection/all
     */
    public static function getObjectsSummaryForObject(
        int $a_parent_obj_id,
        int $a_parent_ref_id,
        string $a_order_field = "",
        string $a_order_dir = "",
        int $a_offset = 0,
        int $a_limit = 9999,
        ?array $a_filters = null,
        ?array $a_additional_fields = null,
        ?array $a_preselected_obj_ids = null
    ): array {
        global $DIC;

        $ilDB = $DIC->database();

        $fields = array();
        self::buildColumns($fields, $a_additional_fields, true);

        $objects = array();
        if ($a_preselected_obj_ids === null) {
            $objects = self::getObjectIds(
                $a_parent_obj_id,
                $a_parent_ref_id,
                false,
                false
            );
        } else {
            foreach ($a_preselected_obj_ids as $obj_id => $ref_ids) {
                $objects["object_ids"][] = $obj_id;
                $objects["ref_ids"][$obj_id] = array_pop($ref_ids);
            }
        }

        $result = array();
        $object_data = [];
        if ($objects) {
            // object data
            $set = $ilDB->query(
                "SELECT obj_id,title,type FROM object_data" .
                " WHERE " . $ilDB->in(
                    "obj_id",
                    $objects["object_ids"],
                    false,
                    "integer"
                )
            );
            while ($rec = $ilDB->fetchAssoc($set)) {
                $object_data[(int) $rec["obj_id"]] = $rec;
                if ($a_preselected_obj_ids) {
                    $object_data[(int) $rec["obj_id"]]["ref_ids"] = $a_preselected_obj_ids[(int) $rec["obj_id"]];
                } else {
                    $object_data[(int) $rec["obj_id"]]["ref_ids"] = array($objects["ref_ids"][(int) $rec["obj_id"]]);
                }
            }

            foreach ($objects["ref_ids"] as $object_id => $ref_id) {
                $object_result = self::getSummaryDataForObject(
                    $ref_id,
                    $fields,
                    $a_filters
                );
                if (sizeof($object_result)) {
                    if ($object_data[$object_id]) {
                        $result[] = array_merge(
                            $object_data[$object_id],
                            $object_result
                        );
                    }
                }
            }
            // @todo: old to do objectives ?
        }

        return array("cnt" => sizeof($result), "set" => $result);
    }

    protected static function getSummaryDataForObject(
        int $a_ref_id,
        array $fields,
        ?array $a_filters = null
    ): array {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $where = array();
        $where[] = "usr_data.usr_id <> " . $ilDB->quote(
            ANONYMOUS_USER_ID,
            "integer"
        );

        // users
        $a_users = self::getParticipantsForObject($a_ref_id);

        $left = "";
        if (is_array($a_users)) { // #14840
            $left = "LEFT";
            $where[] = $ilDB->in("usr_data.usr_id", $a_users, false, "integer");
        }

        $obj_id = ilObject::_lookupObjectId($a_ref_id);
        self::refreshObjectsStatus(array($obj_id), $a_users);

        $query = " FROM usr_data " . $left . " JOIN read_event ON (read_event.usr_id = usr_data.usr_id" .
            " AND obj_id = " . $ilDB->quote($obj_id, "integer") . ")" .
            " LEFT JOIN ut_lp_marks ON (ut_lp_marks.usr_id = usr_data.usr_id " .
            " AND ut_lp_marks.obj_id = " . $ilDB->quote(
                $obj_id,
                "integer"
            ) . ")" .
            " LEFT JOIN usr_pref ON (usr_pref.usr_id = usr_data.usr_id AND keyword = " . $ilDB->quote(
                "language",
                "text"
            ) . ")" .
            self::buildFilters($where, $a_filters, true);

        $fields[] = 'COUNT(usr_data.usr_id) AS user_count';

        $queries = array();
        $queries[] = array("fields" => $fields,
                           "query" => $query,
                           "count" => "*"
        );

        $result = self::executeQueries($queries);
        $result = (array) ($result['set'][0] ?? []);
        $users_no = $result["user_count"] ?? 0;

        $valid = true;
        if (!$users_no) {
            $valid = false;
        } elseif (isset($a_filters["user_total"])) {
            if ($a_filters["user_total"]["from"] && $users_no < $a_filters["user_total"]["from"]) {
                $valid = false;
            } elseif ($a_filters["user_total"]["to"] && $users_no > $a_filters["user_total"]["to"]) {
                $valid = false;
            }
        }

        if ($valid) {
            $result["country"] = self::getSummaryPercentages("country", $query);
            $result["sel_country"] = self::getSummaryPercentages(
                "sel_country",
                $query
            );
            $result["city"] = self::getSummaryPercentages("city", $query);
            $result["gender"] = self::getSummaryPercentages("gender", $query);
            $result["language"] = self::getSummaryPercentages(
                "usr_pref.value",
                $query,
                "language"
            );
            $result["status"] = self::getSummaryPercentages("status", $query);
            $result["mark"] = self::getSummaryPercentages("mark", $query);
        } else {
            $result = array();
        }

        if ($result) {
            $result["user_total"] = $users_no;
        }

        return $result;
    }

    /**
     * Get aggregated data for field
     */
    protected static function getSummaryPercentages(
        string $field,
        string $base_query,
        ?string $alias = null
    ): array {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if (!$alias) {
            $field_alias = $field;
        } else {
            $field_alias = $alias;
            $alias = " AS " . $alias;
        }

        // move having BEHIND group by
        $having = "";
        if (preg_match(
            "/" . preg_quote(" [[--HAVING") . "(.+)" . preg_quote(
                "HAVING--]]"
            ) . "/",
            $base_query,
            $hits
        )) {
            $having = " HAVING " . $hits[1];
            $base_query = str_replace($hits[0], "", $base_query);
        }

        $query = "SELECT COUNT(*) AS counter, " . $field . $alias . " " . $base_query . " GROUP BY " . $field . $having . " ORDER BY counter DESC";
        $set = $ilDB->query($query);
        $result = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $result[$rec[$field_alias]] = (int) $rec["counter"];
        }
        return $result;
    }

    /**
     * Get participant ids for given object
     * @param int $a_ref_id
     * @return    array|null array or null if no users can bedetermined for object.
     */
    public static function getParticipantsForObject(int $a_ref_id): ?array
    {
        global $DIC;

        $tree = $DIC['tree'];

        $obj_id = ilObject::_lookupObjectId($a_ref_id);
        $obj_type = ilObject::_lookupType($obj_id);

        $members = [];

        // try to get participants from (parent) course/group
        $members_read = false;
        switch ($obj_type) {
            case 'crsr':
                $members_read = true;
                $olp = \ilObjectLP::getInstance($obj_id);
                $members = $olp->getMembers();
                break;

            case 'crs':
            case 'grp':
                $members_read = true;
                $member_obj = ilParticipants::getInstance($a_ref_id);
                $members = $member_obj->getMembers();
                break;

            /* Mantis 19296: Individual Assessment can be subtype of crs.
              * But for LP view only his own members should be displayed.
              * We need to return the members without checking the parent path. */
            case "iass":
                $members_read = true;
                $iass = new ilObjIndividualAssessment($obj_id, false);
                $members = $iass->loadMembers()->membersIds();
                break;

            default:
                // walk path to find course or group object and use members of that object
                $path = $tree->getPathId($a_ref_id);
                array_pop($path);
                foreach (array_reverse($path) as $path_ref_id) {
                    $type = ilObject::_lookupType($path_ref_id, true);
                    if ($type == "crs" || $type == "grp") {
                        $members_read = true;
                        $members = self::getParticipantsForObject($path_ref_id);
                    }
                }
                break;
        }

        // begin-patch ouf
        if ($members_read) {
            return $GLOBALS['DIC']->access(
            )->filterUserIdsByRbacOrPositionOfCurrentUser(
                'read_learning_progress',
                'read_learning_progress',
                $a_ref_id,
                $members
            );
        }

        $a_users = null;

        // no participants possible: use tracking/object data where possible
        switch ($obj_type) {
            case "sahs":
                $subtype = ilObjSAHSLearningModule::_lookupSubType($obj_id);
                if ($subtype == "scorm2004") {
                    // based on cmi_node/cp_node, used for scorm tracking data views
                    $mod = new ilObjSCORM2004LearningModule($obj_id, false);
                    $all = $mod->getTrackedUsers("");
                    if ($all) {
                        $a_users = array();
                        foreach ($all as $item) {
                            $a_users[] = $item["user_id"];
                        }
                    }
                } else {
                    $a_users = ilObjSCORMTracking::_getTrackedUsers($obj_id);
                }
                break;

            case "exc":
                $exc = new ilObjExercise($obj_id, false);
                $members = new ilExerciseMembers($exc);
                $a_users = $members->getMembers();
                break;

            case "tst":
                $class = ilLPStatusFactory::_getClassById(
                    $obj_id,
                    ilLPObjSettings::LP_MODE_TEST_FINISHED
                );
                $a_users = $class::getParticipants($obj_id);
                break;

            case "svy":
                $class = ilLPStatusFactory::_getClassById(
                    $obj_id,
                    ilLPObjSettings::LP_MODE_SURVEY_FINISHED
                );
                $a_users = $class::getParticipants($obj_id);
                break;

            case "prg":
                $prg = new ilObjStudyProgramme($obj_id, false);
                $a_users = $prg->getIdsOfUsersWithRelevantProgress();
                break;
            default:
                // keep null
                break;
        }

        if (is_null($a_users)) {
            return $a_users;
        }

        // begin-patch ouf
        return $GLOBALS['DIC']->access(
        )->filterUserIdsByRbacOrPositionOfCurrentUser(
            'read_learning_progress',
            'read_learning_progress',
            $a_ref_id,
            $a_users
        );
    }

    protected static function buildFilters(
        array $where,
        array $a_filters = null,
        bool $a_aggregate = false
    ): string {
        global $DIC;

        $ilDB = $DIC->database();

        $having = array();

        if (is_array($a_filters) && sizeof($a_filters) > 0) {
            foreach ($a_filters as $id => $value) {
                switch ($id) {
                    case "login":
                    case "firstname":
                    case "lastname":
                    case "institution":
                    case "department":
                    case "street":
                    case "email":
                    case "matriculation":
                    case "country":
                    case "city":
                    case "title":
                        $where[] = $ilDB->like(
                            "usr_data." . $id,
                            "text",
                            "%" . $value . "%"
                        );
                        break;

                    case "gender":
                    case "zipcode":
                    case "sel_country":
                        $where[] = "usr_data." . $id . " = " . $ilDB->quote(
                            $value,
                            "text"
                        );
                        break;

                    case "u_comment":
                        $where[] = $ilDB->like(
                            "ut_lp_marks." . $id,
                            "text",
                            "%" . $value . "%"
                        );
                        break;

                    case "status":
                        if ($value == ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM) {
                            // #10645 - not_attempted is default
                            $where[] = "(ut_lp_marks.status = " . $ilDB->quote(
                                ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM,
                                "text"
                            ) .
                                " OR ut_lp_marks.status IS NULL)";
                            break;
                        }
                    // fallthrough

                    // no break
                    case "mark":
                        $where[] = "ut_lp_marks." . $id . " = " . $ilDB->quote(
                            $value,
                            "text"
                        );
                        break;

                    case "percentage":
                        if (!$a_aggregate) {
                            if (isset($value["from"])) {
                                $where[] = "ut_lp_marks." . $id . " >= " . $ilDB->quote(
                                    $value["from"],
                                    "integer"
                                );
                            }
                            if (isset($value["to"])) {
                                $where[] = "(ut_lp_marks." . $id . " <= " . $ilDB->quote(
                                    $value["to"],
                                    "integer"
                                ) .
                                    " OR ut_lp_marks." . $id . " IS NULL)";
                            }
                        } else {
                            if (isset($value["from"])) {
                                $having[] = "ROUND(AVG(ut_lp_marks." . $id . ")) >= " . $ilDB->quote(
                                    $value["from"],
                                    "integer"
                                );
                            }
                            if (isset($value["to"])) {
                                $having[] = "ROUND(AVG(ut_lp_marks." . $id . ")) <= " . $ilDB->quote(
                                    $value["to"],
                                    "integer"
                                );
                            }
                        }
                        break;

                    case "language":
                        $where[] = "usr_pref.value = " . $ilDB->quote(
                            $value,
                            "text"
                        );
                        break;

                    // timestamp
                    case "last_access":
                        if (isset($value["from"])) {
                            $value["from"] = substr(
                                $value["from"],
                                0,
                                -2
                            ) . "00";
                            $value["from"] = new ilDateTime(
                                $value["from"],
                                IL_CAL_DATETIME
                            );
                            $value["from"] = $value["from"]->get(IL_CAL_UNIX);
                        }
                        if (isset($value["to"])) {
                            if (strlen($value["to"]) == 19) {
                                $value["to"] = substr(
                                    $value["to"],
                                    0,
                                    -2
                                ) . "59"; // #14858
                            }
                            $value["to"] = new ilDateTime(
                                $value["to"],
                                IL_CAL_DATETIME
                            );
                            $value["to"] = $value["to"]->get(IL_CAL_UNIX);
                        }
                    // fallthrough

                    // no break
                    case 'status_changed':
                        // fallthrough

                    case "registration":
                        if ($id == "registration") {
                            $id = "create_date";
                        }
                    // fallthrough

                    // no break
                    case "create_date":
                    case "first_access":
                    case "birthday":
                        if (isset($value["from"])) {
                            $where[] = $id . " >= " . $ilDB->quote(
                                $value["from"],
                                "date"
                            );
                        }
                        if (isset($value["to"])) {
                            if (strlen($value["to"]) == 19) {
                                $value["to"] = substr(
                                    $value["to"],
                                    0,
                                    -2
                                ) . "59"; // #14858
                            }
                            $where[] = $id . " <= " . $ilDB->quote(
                                $value["to"],
                                "date"
                            );
                        }
                        break;

                    case "read_count":
                        if (!$a_aggregate) {
                            if (isset($value["from"])) {
                                $where[] = "(read_event." . $id . "+read_event.childs_" . $id . ") >= " . $ilDB->quote(
                                    $value["from"],
                                    "integer"
                                );
                            }
                            if (isset($value["to"])) {
                                $where[] = "((read_event." . $id . "+read_event.childs_" . $id . ") <= " . $ilDB->quote(
                                    $value["to"],
                                    "integer"
                                ) .
                                    " OR (read_event." . $id . "+read_event.childs_" . $id . ") IS NULL)";
                            }
                        } else {
                            if (isset($value["from"])) {
                                $having[] = "SUM(read_event." . $id . "+read_event.childs_" . $id . ") >= " . $ilDB->quote(
                                    $value["from"],
                                    "integer"
                                );
                            }
                            if (isset($value["to"])) {
                                $having[] = "SUM(read_event." . $id . "+read_event.childs_" . $id . ") <= " . $ilDB->quote(
                                    $value["to"],
                                    "integer"
                                );
                            }
                        }
                        break;

                    case "spent_seconds":
                        if (!$a_aggregate) {
                            if (isset($value["from"]) && $value["from"] > 0) {
                                $where[] = "(read_event." . $id . "+read_event.childs_" . $id . ") >= " . $ilDB->quote(
                                    $value["from"],
                                    "integer"
                                );
                            }
                            if (isset($value["to"]) && $value["to"] > 0) {
                                $where[] = "((read_event." . $id . "+read_event.childs_" . $id . ") <= " . $ilDB->quote(
                                    $value["to"],
                                    "integer"
                                ) .
                                    " OR (read_event." . $id . "+read_event.childs_" . $id . ") IS NULL)";
                            }
                        } else {
                            if (isset($value["from"]) && $value["from"] > 0) {
                                $having[] = "ROUND(AVG(read_event." . $id . "+read_event.childs_" . $id . ")) >= " . $ilDB->quote(
                                    $value["from"],
                                    "integer"
                                );
                            }
                            if (isset($value["to"]) && $value["to"] > 0) {
                                $having[] = "ROUND(AVG(read_event." . $id . "+read_event.childs_" . $id . ")) <= " . $ilDB->quote(
                                    $value["to"],
                                    "integer"
                                );
                            }
                        }
                        break;

                    default:
                        // var_dump("unknown: ".$id);
                        break;
                }
            }
        }

        $sql = "";
        if (sizeof($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        if (sizeof($having)) {
            // ugly "having" hack because of summary view
            $sql .= " [[--HAVING " . implode(" AND ", $having) . "HAVING--]]";
        }

        return $sql;
    }

    protected static function buildColumns(
        array &$a_fields,
        array $a_additional_fields = null,
        bool $a_aggregate = false
    ): array {
        if ($a_additional_fields === null || !count($a_additional_fields)) {
            return [];
        }
        $udf = [];
        foreach ($a_additional_fields as $field) {
            if (substr($field, 0, 4) != "udf_") {
                $function = null;
                if ($a_aggregate) {
                    $pos = strrpos($field, "_");
                    if ($pos === false) {
                        continue;
                    }
                    $function = strtoupper(substr($field, $pos + 1));
                    $field = substr($field, 0, $pos);
                    if (!in_array(
                        $function,
                        array("MIN", "MAX", "SUM", "AVG", "COUNT")
                    )) {
                        continue;
                    }
                }

                switch ($field) {
                    case 'org_units':
                        break;

                    case "language":
                        if ($function) {
                            $a_fields[] = $function . "(value) " . $field . "_" . strtolower(
                                $function
                            );
                        } else {
                            $a_fields[] = "value as " . $field;
                        }
                        break;

                    case "read_count":
                    case "spent_seconds":
                        if (!$function) {
                            $a_fields[] = "(" . $field . "+childs_" . $field . ") " . $field;
                        } else {
                            if ($function == "AVG") {
                                $a_fields[] = "ROUND(AVG(" . $field . "+childs_" . $field . "), 2) " . $field . "_" . strtolower(
                                    $function
                                );
                            } else {
                                $a_fields[] = $function . "(COALESCE(" . $field . ", 0) + COALESCE(childs_" . $field . ", 0)) " . $field . "_" . strtolower(
                                    $function
                                );
                            }
                        }
                        break;

                    case "read_count_spent_seconds":
                        if ($function == "AVG") {
                            $a_fields[] = "ROUND(AVG((spent_seconds+childs_spent_seconds)/(read_count+childs_read_count)), 2) " . $field . "_" . strtolower(
                                $function
                            );
                        }
                        break;

                    default:
                        if ($function) {
                            if ($function == "AVG") {
                                $a_fields[] = "ROUND(AVG(" . $field . "), 2) " . $field . "_" . strtolower(
                                    $function
                                );
                            } else {
                                $a_fields[] = $function . "(" . $field . ") " . $field . "_" . strtolower(
                                    $function
                                );
                            }
                        } else {
                            $a_fields[] = $field;
                        }
                        break;
                }
            } else {
                $udf[] = substr($field, 4);
            }
        }

        // clean-up
        $a_fields = array_unique($a_fields);
        if (count($udf)) {
            $udf = array_unique($udf);
        }
        return $udf;
    }

    /**
     * Get (sub)objects for given object, also handles learning objectives (course only)
     * @param int        $a_parent_obj_id
     * @param int        $a_parent_ref_id
     * @param bool       $use_collection
     * @param bool       $a_refresh_status
     * @param array|null $a_user_ids
     * @return    array    object_ids, objectives_parent_id
     */
    public static function getObjectIds(
        int $a_parent_obj_id,
        int $a_parent_ref_id,
        bool $use_collection = true,
        bool $a_refresh_status = true,
        ?array $a_user_ids = null
    ): array {
        $object_ids = array($a_parent_obj_id);
        $ref_ids = array($a_parent_obj_id => $a_parent_ref_id);
        $objectives_parent_id = $scorm = $subitems = false;

        $olp = ilObjectLP::getInstance($a_parent_obj_id);
        $mode = $olp->getCurrentMode();
        switch ($mode) {
            // what about LP_MODE_SCORM_PACKAGE ?
            case ilLPObjSettings::LP_MODE_SCORM:
                $status_scorm = get_class(
                    ilLPStatusFactory::_getInstance(
                        $a_parent_obj_id,
                        ilLPObjSettings::LP_MODE_SCORM
                    )
                );
                $scorm = $status_scorm::_getStatusInfo($a_parent_obj_id);
                break;

            case ilLPObjSettings::LP_MODE_OBJECTIVES:
                if (ilObject::_lookupType($a_parent_obj_id) == "crs") {
                    $objectives_parent_id = $a_parent_obj_id;
                }
                break;

            case ilLPObjSettings::LP_MODE_COLLECTION_MANUAL:
            case ilLPObjSettings::LP_MODE_COLLECTION_TLT:
            case ilLPObjSettings::LP_MODE_COLLECTION_MOBS:
                $status_coll_tlt = get_class(
                    ilLPStatusFactory::_getInstance($a_parent_obj_id, $mode)
                );
                $subitems = $status_coll_tlt::_getStatusInfo($a_parent_obj_id);
                break;

            default:
                // lp collection
                if ($use_collection) {
                    $collection = $olp->getCollectionInstance();
                    if ($collection) {
                        foreach ($collection->getItems() as $child_ref_id) {
                            $child_id = ilObject::_lookupObjId($child_ref_id);
                            $object_ids[] = $child_id;
                            $ref_ids[$child_id] = $child_ref_id;
                        }
                    }
                } // all objects in branch
                else {
                    self::getSubTree($a_parent_ref_id, $object_ids, $ref_ids);
                    $object_ids = array_unique($object_ids);
                }

                foreach ($object_ids as $idx => $object_id) {
                    if (!$object_id) {
                        unset($object_ids[$idx]);
                    }
                }
                break;
        }

        if ($a_refresh_status) {
            self::refreshObjectsStatus($object_ids, $a_user_ids);
        }

        return array("object_ids" => $object_ids,
                     "ref_ids" => $ref_ids,
                     "objectives_parent_id" => $objectives_parent_id,
                     "scorm" => $scorm,
                     "subitems" => $subitems
        );
    }

    /**
     * Get complete branch of tree (recursively)
     */
    protected static function getSubTree(
        int $a_parent_ref_id,
        array &$a_object_ids,
        array &$a_ref_ids
    ): void {
        global $DIC;

        $tree = $DIC['tree'];

        $children = $tree->getChilds($a_parent_ref_id);
        if ($children) {
            foreach ($children as $child) {
                if ($child["type"] == "adm" || $child["type"] == "rolf") {
                    continue;
                }

                // as there can be deactivated items in the collection
                // we should allow them here too

                $olp = ilObjectLP::getInstance($child["obj_id"]);
                $cmode = $olp->getCurrentMode();

                if ($cmode != ilLPObjSettings::LP_MODE_UNDEFINED) {
                    $a_object_ids[] = $child["obj_id"];
                    $a_ref_ids[$child["obj_id"]] = $child["ref_id"];
                }

                self::getSubTree($child["ref_id"], $a_object_ids, $a_ref_ids);
            }
        }
    }

    /**
     * Execute given queries, including count query
     * @param array  $queries fields, query, count
     * @param string $a_order_field
     * @param string $a_order_dir
     * @param int    $a_offset
     * @param int    $a_limit
     * @return    array    cnt, set
     */
    public static function executeQueries(
        array $queries,
        string $a_order_field = "",
        string $a_order_dir = "",
        int $a_offset = 0,
        int $a_limit = 9999
    ): array {
        global $DIC;

        $ilDB = $DIC->database();
        $cnt = 0;
        $subqueries = array();
        foreach ($queries as $item) {
            // ugly "having" hack because of summary view
            $item['query'] = str_replace("[[--HAVING", "HAVING", $item['query']);
            $item['query'] = str_replace("HAVING--]]", "", $item['query']);

            if (!isset($item["count"])) {
                $count_field = $item["fields"];
                $count_field = array_shift($count_field);
            } else {
                $count_field = $item["count"];
            }
            $count_query = "SELECT COUNT(" . $count_field . ") AS cnt" . $item["query"];
            $set = $ilDB->query($count_query);
            if ($rec = $ilDB->fetchAssoc($set)) {
                $cnt += $rec["cnt"];
            }

            $subqueries[] = "SELECT " . implode(
                ",",
                $item["fields"]
            ) . $item["query"];
        }

        // set query
        $result = array();
        if ($cnt > 0) {
            if (sizeof($subqueries) > 1) {
                $base = array_shift($subqueries);
                $query = $base . " UNION (" . implode(
                    ") UNION (",
                    $subqueries
                ) . ")";
            } else {
                $query = $subqueries[0];
            }

            if ($a_order_dir != "asc" && $a_order_dir != "desc") {
                $a_order_dir = "asc";
            }
            if ($a_order_field) {
                $query .= " ORDER BY " . $a_order_field . " " . strtoupper(
                    $a_order_dir
                );
            }

            $offset = $a_offset;
            $limit = $a_limit;
            $ilDB->setLimit($limit, $offset);
            $set = $ilDB->query($query);
            while ($rec = $ilDB->fetchAssoc($set)) {
                $result[] = $rec;
            }
        }

        return array("cnt" => $cnt, "set" => $result);
    }

    /**
     * Get status matrix for users on objects
     * @param int         $a_parent_ref_id
     * @param array       $a_obj_ids
     * @param string|null $a_user_filter
     * @param array|null  $a_additional_fields
     * @param array|null  $a_privacy_fields
     * @param int|null    $a_check_agreement
     * @return    array    cnt, set
     */
    public static function getUserObjectMatrix(
        int $a_parent_ref_id,
        array $a_obj_ids,
        string $a_user_filter = null,
        ?array $a_additional_fields = null,
        ?array $a_privacy_fields = null,
        ?int $a_check_agreement = null
    ): array {
        global $DIC;
        $ilDB = $DIC->database();

        $result = array("cnt" => 0, "set" => null);
        if (sizeof($a_obj_ids)) {
            $where = array();
            $where[] = "usr_data.usr_id <> " . $ilDB->quote(
                ANONYMOUS_USER_ID,
                "integer"
            );
            if ($a_user_filter) {
                $where[] = $ilDB->like(
                    "usr_data.login",
                    "text",
                    "%" . $a_user_filter . "%"
                );
            }

            // users
            $left = "";
            $a_users = self::getParticipantsForObject($a_parent_ref_id);
            if (is_array($a_users)) {
                $left = "LEFT";
                $where[] = $ilDB->in(
                    "usr_data.usr_id",
                    $a_users,
                    false,
                    "integer"
                );
            }

            $parent_obj_id = ilObject::_lookupObjectId($a_parent_ref_id);
            self::refreshObjectsStatus($a_obj_ids, $a_users);

            $fields = array("usr_data.usr_id", "login", "active");
            $udf = self::buildColumns($fields, $a_additional_fields);

            // #18673 - if parent supports percentage does not matter for "sub-items"
            $fields[] = "percentage";

            $raw = array();
            foreach ($a_obj_ids as $obj_id) {
                // one request for each object
                $query = " FROM usr_data " . $left . " JOIN read_event ON (read_event.usr_id = usr_data.usr_id" .
                    " AND read_event.obj_id = " . $ilDB->quote(
                        $obj_id,
                        "integer"
                    ) . ")" .
                    " LEFT JOIN ut_lp_marks ON (ut_lp_marks.usr_id = usr_data.usr_id " .
                    " AND ut_lp_marks.obj_id = " . $ilDB->quote(
                        $obj_id,
                        "integer"
                    ) . ")" .
                    " LEFT JOIN usr_pref ON (usr_pref.usr_id = usr_data.usr_id AND keyword = " . $ilDB->quote(
                        "language",
                        "text"
                    ) . ")" .
                    self::buildFilters($where);

                $raw = self::executeQueries(
                    array(array("fields" => $fields, "query" => $query)),
                    "login"
                );
                if ($raw["cnt"]) {
                    // convert to final structure
                    foreach ($raw["set"] as $row) {
                        $result["set"][(int) $row["usr_id"]]["login"] = ($row["login"] ?? '');
                        $result["set"][(int) $row["usr_id"]]["usr_id"] = (int) ($row["usr_id"] ?? 0);

                        // #14953
                        $result["set"][(int) $row["usr_id"]]["obj_" . $obj_id] = (int) ($row["status"] ?? 0);
                        $result["set"][(int) $row["usr_id"]]["obj_" . $obj_id . "_perc"] = (int) ($row["percentage"] ?? 0);
                        if ($obj_id == $parent_obj_id) {
                            $result["set"][(int) $row["usr_id"]]["status_changed"] = (int) ($row["status_changed"] ?? 0);
                            $result["set"][(int) $row["usr_id"]]["last_access"] = (int) ($row["last_access"] ?? 0);
                            $result["set"][(int) $row["usr_id"]]["spent_seconds"] = (int) ($row["spent_seconds"] ?? 0);
                            $result["set"][(int) $row["usr_id"]]["read_count"] = (int) ($row["read_count"] ?? 0);
                        }

                        // @todo int cast?
                        foreach ($fields as $field) {
                            // #14957 - value [as] language
                            if (stristr($field, "language")) {
                                $field = "language";
                            }

                            if (isset($row[$field])) {
                                // #14955
                                if ($obj_id == $parent_obj_id ||
                                    !in_array(
                                        $field,
                                        array("mark", "u_comment")
                                    )) {
                                    $result["set"][(int) $row["usr_id"]][$field] = $row[$field];
                                }
                            }
                        }
                    }
                }
            }

            $result["cnt"] = 0;
            if (is_array($result["set"])) {
                $result["cnt"] = count($result["set"]);
            }
            $result["users"] = $a_users;

            self::getUDFAndHandlePrivacy(
                $result,
                $udf,
                $a_check_agreement,
                $a_privacy_fields,
                $a_additional_fields
            );
        }
        return $result;
    }

    public static function getUserObjectiveMatrix(
        int $a_parent_obj_id,
        array $a_users
    ): array {
        global $DIC;

        $ilDB = $DIC->database();

        if ($a_parent_obj_id && $a_users) {
            $res = array();

            $objective_ids = ilCourseObjective::_getObjectiveIds(
                $a_parent_obj_id,
                true
            );

            // #17402 - are initital test(s) qualifying?
            $lo_set = ilLOSettings::getInstanceByObjId($a_parent_obj_id);
            $initial_qualifying = $lo_set->isInitialTestQualifying();

            // there may be missing entries for any user / objective combination
            foreach ($objective_ids as $objective_id) {
                foreach ($a_users as $user_id) {
                    $res[$user_id][$objective_id] = ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
                }
            }

            $query = "SELECT * FROM loc_user_results" .
                " WHERE " . $ilDB->in(
                    "objective_id",
                    $objective_ids,
                    false,
                    "integer"
                ) .
                " AND " . $ilDB->in("user_id", $a_users, false, "integer");
            if (!$initial_qualifying) {
                $query .= " AND type = " . $ilDB->quote(
                    ilLOUserResults::TYPE_QUALIFIED,
                    "integer"
                );
            }
            $query .= " ORDER BY type"; // qualified must come last!
            $set = $ilDB->query($query);
            while ($row = $ilDB->fetchAssoc($set)) {
                $objective_id = (int) $row["objective_id"];
                $user_id = (int) $row["user_id"];

                // if both initial and qualified, qualified will overwrite initial

                // #15873 - see ilLOUserResults::getObjectiveStatusForLP()
                if ($row["status"] == ilLOUserResults::STATUS_COMPLETED) {
                    $res[$user_id][$objective_id] = ilLPStatus::LP_STATUS_COMPLETED_NUM;
                } elseif ($row["status"] == ilLOUserResults::STATUS_FAILED) {
                    $res[$user_id][$objective_id] = (int) $row["is_final"]
                        ? ilLPStatus::LP_STATUS_FAILED_NUM
                        : ilLPStatus::LP_STATUS_IN_PROGRESS_NUM;
                }
            }

            return $res;
        }
        return [];
    }

    public static function getObjectAccessStatistics(
        array $a_ref_ids,
        string $a_year,
        ?string $a_month = null
    ): array {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $obj_ids = array_keys($a_ref_ids);

        if ($a_month) {
            $column = "dd";
        } else {
            $column = "mm";
        }

        $res = array();
        $sql = "SELECT obj_id," . $column . ",SUM(read_count) read_count,SUM(childs_read_count) childs_read_count," .
            "SUM(spent_seconds) spent_seconds,SUM(childs_spent_seconds) childs_spent_seconds" .
            " FROM obj_stat" .
            " WHERE " . $ilDB->in("obj_id", $obj_ids, "", "integer") .
            " AND yyyy = " . $ilDB->quote($a_year, "integer");
        if ($a_month) {
            $sql .= " AND mm = " . $ilDB->quote($a_month, "integer");
        }
        $sql .= " GROUP BY obj_id," . $column;
        $set = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($set)) {
            $row["read_count"] += (int) $row["childs_read_count"];
            $row["spent_seconds"] += (int) $row["childs_spent_seconds"];
            $res[$row["obj_id"]][$row[$column]]["read_count"] =
                ($res[$row["obj_id"]][$row[$column]]["read_count"] ?? 0) + $row["read_count"];
            $res[$row["obj_id"]][$row[$column]]["spent_seconds"] =
                ($res[$row["obj_id"]][$row[$column]]["spent_seconds"] ?? 0) + $row["spent_seconds"];
        }

        // add user data

        $sql = "SELECT obj_id," . $column . ",SUM(counter) counter" .
            " FROM obj_user_stat" .
            " WHERE " . $ilDB->in("obj_id", $obj_ids, "", "integer") .
            " AND yyyy = " . $ilDB->quote($a_year, "integer");
        if ($a_month) {
            $sql .= " AND mm = " . $ilDB->quote($a_month, "integer");
        }
        $sql .= " GROUP BY obj_id," . $column;
        $set = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($set)) {
            if (!isset($res[(int) $row["obj_id"]][$row[$column]]["users"])) {
                $res[(int) $row["obj_id"]][$row[$column]]["users"] = 0;
            }
            $res[(int) $row["obj_id"]][$row[$column]]["users"] += (int) $row["counter"];
        }

        return $res;
    }

    public static function getObjectTypeStatistics(): array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $objDefinition = $DIC['objDefinition'];

        // re-use add new item selection (folder is not that important)
        $types = array_keys(
            $objDefinition->getCreatableSubObjects(
                "root",
                ilObjectDefinition::MODE_REPOSITORY
            )
        );

        // repository
        $tree = new ilTree(1);
        $sql = "SELECT " . $tree->getObjectDataTable(
            ) . ".obj_id," . $tree->getObjectDataTable() . ".type," .
            $tree->getTreeTable() . "." . $tree->getTreePk(
            ) . "," . $tree->getTableReference() . ".ref_id" .
            " FROM " . $tree->getTreeTable() .
            " " . $tree->buildJoin() .
            " WHERE " . $ilDB->in(
                $tree->getObjectDataTable() . ".type",
                $types,
                "",
                "text"
            );
        $set = $ilDB->query($sql);
        $res = array();
        while ($row = $ilDB->fetchAssoc($set)) {
            $res[$row["type"]]["type"] = $row["type"];
            $res[$row["type"]]["references"] = ($res[$row["type"]]["references"] ?? 0) + 1;
            $res[$row["type"]]["objects"][] = (int) $row["obj_id"];
            if ($row[$tree->getTreePk()] < 0) {
                $res[$row["type"]]["deleted"] = ($res[$row["type"]]["deleted"] ?? 0) + 1;
            }
        }

        foreach ($res as $type => $values) {
            $res[$type]["objects"] = sizeof(array_unique($values["objects"]));
        }

        // portfolios (not part of repository)
        foreach (self::getPortfolios() as $obj_id) {
            $res["prtf"]["type"] = "prtf";
            $res["prtf"]["references"] = ($res["prtf"]["references"] ?? 0) + 1;
            $res["prtf"]["objects"] = ($res["prtf"]["objects"] ?? 0) + 1;
        }

        foreach (self::getWorkspaceBlogs() as $obj_id) {
            $res["blog"]["type"] = "blog";
            $res["blog"]["references"] = ($res["blog"]["references"] ?? 0) + 1;
            $res["blog"]["objects"] = ($res["blog"]["objects"] ?? 0) + 1;
        }

        return $res;
    }

    public static function getWorkspaceBlogs(?string $a_title = null): array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $res = array();

        // blogs in workspace?
        $sql = "SELECT od.obj_id,oref.wsp_id,od.type" .
            " FROM tree_workspace wst" .
            " JOIN object_reference_ws oref ON (oref.wsp_id = wst.child)" .
            " JOIN object_data od ON (oref.obj_id = od.obj_id)" .
            " WHERE od.type = " . $ilDB->quote("blog", "text");

        if ($a_title) {
            $sql .= " AND " . $ilDB->like(
                "od.title",
                "text",
                "%" . $a_title . "%"
            );
        }

        $set = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($set)) {
            $res[] = (int) $row["obj_id"];
        }
        return $res;
    }

    public static function getPortfolios(?string $a_title = null): array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $res = array();

        $sql = "SELECT od.obj_id" .
            " FROM usr_portfolio prtf" .
            " JOIN object_data od ON (od.obj_id = prtf.id)";

        if ($a_title) {
            $sql .= " WHERE " . $ilDB->like(
                "od.title",
                "text",
                "%" . $a_title . "%"
            );
        }

        $set = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($set)) {
            $res[] = (int) $row["obj_id"];
        }

        return $res;
    }

    public static function getObjectDailyStatistics(
        array $a_ref_ids,
        string $a_year,
        ?string $a_month = null
    ): array {
        global $DIC;

        $ilDB = $DIC->database();
        $obj_ids = array_keys($a_ref_ids);

        $res = array();
        $sql = "SELECT obj_id,hh,SUM(read_count) read_count,SUM(childs_read_count) childs_read_count," .
            "SUM(spent_seconds) spent_seconds,SUM(childs_spent_seconds) childs_spent_seconds" .
            " FROM obj_stat" .
            " WHERE " . $ilDB->in("obj_id", $obj_ids, false, "integer") .
            " AND yyyy = " . $ilDB->quote($a_year, "integer");
        if ($a_month) {
            $sql .= " AND mm = " . $ilDB->quote($a_month, "integer");
        }
        $sql .= " GROUP BY obj_id,hh";
        $set = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($set)) {
            $row["read_count"] += (int) $row["childs_read_count"];
            $row["spent_seconds"] += (int) $row["childs_spent_seconds"];
            $res[$row["obj_id"]][(int) $row["hh"]]["read_count"] =
                ($res[$row["obj_id"]][(int) $row["hh"]]["read_count"] ?? 0) + $row["read_count"];
            $res[$row["obj_id"]][(int) $row["hh"]]["spent_seconds"] =
                ($res[$row["obj_id"]][(int) $row["hh"]]["spent_seconds"] ?? 0) + $row["spent_seconds"];
        }
        return $res;
    }

    public static function getObjectStatisticsMonthlySummary(): array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $set = $ilDB->query(
            "SELECT COUNT(*) AS COUNTER,yyyy,mm" .
            " FROM obj_stat" .
            " GROUP BY yyyy, mm" .
            " ORDER BY yyyy DESC, mm DESC"
        );
        $res = array();
        while ($row = $ilDB->fetchAssoc($set)) {
            $res[] = array("month" => $row["yyyy"] . "-" . $row["mm"],
                           "count" => (int) ($row["counter"] ?? 0)
            );
        }
        return $res;
    }

    public static function deleteObjectStatistics(array $a_months): void
    {
        global $DIC;

        $ilDB = $DIC->database();

        // no combined column, have to concat
        $date_compare = $ilDB->in(
            $ilDB->concat(
                array(array("yyyy", ""),
                      array($ilDB->quote("-", "text"), ""),
                      array("mm", "")
                )
            ),
            $a_months,
            false,
            "text"
        );
        $sql = "DELETE FROM obj_stat" .
            " WHERE " . $date_compare;
        $ilDB->manipulate($sql);

        // fulldate == YYYYMMDD
        $tables = array("obj_lp_stat", "obj_type_stat", "obj_user_stat");
        foreach ($a_months as $month) {
            $year = substr($month, 0, 4);
            $month = substr($month, 5);
            $from = $year . str_pad($month, 2, "0", STR_PAD_LEFT) . "01";
            $to = $year . str_pad($month, 2, "0", STR_PAD_LEFT) . "31";

            foreach ($tables as $table) {
                $sql = "DELETE FROM " . $table .
                    " WHERE fulldate >= " . $ilDB->quote($from, "integer") .
                    " AND fulldate <= " . $ilDB->quote($to, "integer");
                $ilDB->manipulate($sql);
            }
        }
    }

    public static function searchObjects(
        string $a_type,
        ?string $a_title = null,
        ?int $a_root = null,
        ?array $a_hidden = null,
        ?array $a_preset_obj_ids = null
    ): array {
        global $DIC;

        $ilDB = $DIC->database();
        $tree = $DIC->repositoryTree();

        if ($a_type == "lres") {
            $a_type = array('lm', 'sahs', 'htlm');
        }

        $sql = "SELECT r.ref_id,r.obj_id" .
            " FROM object_data o" .
            " JOIN object_reference r ON (o.obj_id = r.obj_id)" .
            " JOIN tree t ON (t.child = r.ref_id)" .
            " WHERE t.tree = " . $ilDB->quote(1, "integer");

        if (!is_array($a_type)) {
            $sql .= " AND o.type = " . $ilDB->quote($a_type, "text");
        } else {
            $sql .= " AND " . $ilDB->in("o.type", $a_type, false, "text");
        }

        if ($a_title) {
            $sql .= " AND (" . $ilDB->like(
                "o.title",
                "text",
                "%" . $a_title . "%"
            ) .
                " OR " . $ilDB->like(
                    "o.description",
                    "text",
                    "%" . $a_title . "%"
                ) . ")";
        }

        if (is_array($a_hidden)) {
            $sql .= " AND " . $ilDB->in("o.obj_id", $a_hidden, true, "integer");
        }

        if (is_array($a_preset_obj_ids)) {
            $sql .= " AND " . $ilDB->in(
                "o.obj_id",
                $a_preset_obj_ids,
                false,
                "integer"
            );
        }

        $set = $ilDB->query($sql);
        $res = array();
        while ($row = $ilDB->fetchAssoc($set)) {
            if ($a_root && $a_root != ROOT_FOLDER_ID) {
                foreach (ilObject::_getAllReferences(
                    $row['obj_id']
                ) as $ref_id) {
                    if ($tree->isGrandChild($a_root, $ref_id)) {
                        $res[$row["obj_id"]][] = (int) $row["ref_id"];
                    }
                }
            } else {
                $res[$row["obj_id"]][] = (int) $row["ref_id"];
            }
        }
        return $res;
    }

    /**
     * check whether status (for all relevant users) exists
     */
    protected static function refreshObjectsStatus(
        array $a_obj_ids,
        ?array $a_users = null
    ): void {
        foreach ($a_obj_ids as $obj_id) {
            ilLPStatus::checkStatusForObject($obj_id, $a_users);
        }
    }

    /**
     * Get last update info for object statistics
     */
    public static function getObjectStatisticsLogInfo(): array
    {
        global $DIC;

        $ilDB = $DIC->database();
        $set = $ilDB->query(
            "SELECT COUNT(*) counter, MIN(tstamp) tstamp" .
            " FROM obj_stat_log"
        );
        return $ilDB->fetchAssoc($set);
    }

    public static function getObjectLPStatistics(
        array $a_obj_ids,
        int $a_year,
        int $a_month = null,
        bool $a_group_by_day = false
    ): array {
        global $DIC;

        $ilDB = $DIC->database();
        if ($a_group_by_day) {
            $column = "dd";
        } else {
            $column = "mm,yyyy";
        }

        $res = array();
        $sql = "SELECT obj_id," . $column . "," .
            "MIN(mem_cnt) mem_cnt_min,AVG(mem_cnt) mem_cnt_avg, MAX(mem_cnt) mem_cnt_max," .
            "MIN(in_progress) in_progress_min,AVG(in_progress) in_progress_avg,MAX(in_progress) in_progress_max," .
            "MIN(completed) completed_min,AVG(completed) completed_avg,MAX(completed) completed_max," .
            "MIN(failed) failed_min,AVG(failed) failed_avg,MAX(failed) failed_max," .
            "MIN(not_attempted) not_attempted_min,AVG(not_attempted) not_attempted_avg,MAX(not_attempted) not_attempted_max" .
            " FROM obj_lp_stat" .
            " WHERE " . $ilDB->in("obj_id", $a_obj_ids, false, "integer") .
            " AND yyyy = " . $ilDB->quote($a_year, "integer");
        if ($a_month) {
            $sql .= " AND mm = " . $ilDB->quote($a_month, "integer");
        }
        $sql .= " GROUP BY obj_id," . $column;
        $set = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($set)) {
            $row['obj_id'] = (int) $row['obj_id'];
            $res[] = $row;
        }

        return $res;
    }

    public static function getObjectTypeStatisticsPerMonth(
        string $a_aggregation,
        ?string $a_year = null
    ): array {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if (!$a_year) {
            $a_year = date("Y");
        }

        $agg = strtoupper($a_aggregation);

        $res = array();
        $sql = "SELECT type,yyyy,mm," . $agg . "(cnt_objects) cnt_objects," . $agg . "(cnt_references) cnt_references," .
            "" . $agg . "(cnt_deleted) cnt_deleted FROM obj_type_stat" .
            " WHERE yyyy = " . $ilDB->quote($a_year, "integer") .
            " GROUP BY type,yyyy,mm";
        $set = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($set)) {
            $row["mm"] = str_pad($row["mm"], 2, "0", STR_PAD_LEFT);
            $res[$row["type"]][$row["yyyy"] . "-" . $row["mm"]] = array(
                "objects" => (int) $row["cnt_objects"],
                "references" => (int) $row["cnt_references"],
                "deleted" => (int) $row["cnt_deleted"]
            );
        }

        return $res;
    }
}
