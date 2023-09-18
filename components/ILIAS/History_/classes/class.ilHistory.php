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

/**
 * This class methods for maintain history enties for objects
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilHistory
{
    /**
     * Creates a new history entry for an object. The information text parameters
     * have to be separated by comma. The information text has to be stored
     * in a langage variable "hist_<object_type>_<action>". This text can contain
     * placeholders %1, %2, ... for each parameter. The placehoders are replaced
     * by the parameters in ilHistoryTableGUI.
     *
     * Please note that the object type must be specified, if the object is not
     * derived from ilObject.
     * @param int    $a_obj_id object id
     * @param string $a_action
     * @param array  $a_info_params information parameters
     * @param string $a_obj_type object type (must only be set, if object is not in object_data table)
     * @param string $a_user_comment
     * @param bool   $a_update_last
     */
    public static function _createEntry(
        int $a_obj_id,
        string $a_action,
        array $a_info_params = [],
        string $a_obj_type = "",
        string $a_user_comment = "",
        bool $a_update_last = false
    ): void {
        global $DIC;

        $ilDB = $DIC->database();
        $ilUser = $DIC->user();

        if ($a_obj_type == "") {
            $a_obj_type = ilObject::_lookupType($a_obj_id);
        }

        if (is_array($a_info_params)) {
            foreach ($a_info_params as $key => $param) {
                $a_info_params[$key] = str_replace(",", "&#044;", $param);
            }
            $a_info_params = implode(",", $a_info_params);
        }

        // get last entry of object
        $last_entry_sql = "SELECT * FROM history WHERE " .
            " obj_id = " . $ilDB->quote($a_obj_id, "integer") . " AND " .
            " obj_type = " . $ilDB->quote($a_obj_type, "text") . " ORDER BY hdate DESC";
        $last_entry_set = $ilDB->query($last_entry_sql);
        $last_entry = $ilDB->fetchAssoc($last_entry_set);

        // note: insert is forced if last entry already has a comment and a
        // new comment is given too OR
        // if entry should not be updated OR
        // if current action or user id are not equal with last entry
        if (($a_user_comment != "" && $last_entry["user_comment"] != "")
            || !$a_update_last || $a_action != $last_entry["action"]
            || $ilUser->getId() != $last_entry["usr_id"]) {
            $id = $ilDB->nextId("history");
            $ilDB->insert("history", array(
                "id" => array("integer", $id),
                "obj_id" => array("integer", $a_obj_id),
                "obj_type" => array("text", $a_obj_type),
                "action" => array("text", $a_action),
                "hdate" => array("timestamp", ilUtil::now()),
                "usr_id" => array("integer", $ilUser->getId()),
                "info_params" => array("text", $a_info_params),
                "user_comment" => array("clob", $a_user_comment)
                ));
        } else {
            $fields = array(
                "hdate" => array("timestamp", ilUtil::now())
                );
            if ($a_user_comment != "") {
                $fields["user_comment"] = array("clob", $a_user_comment);
            }

            $ilDB->update("history", $fields, array(
                "id" => array("integer", $last_entry["id"])
                ));
        }
    }

    /**
     * get all history entries for an object
     * @param int    $a_obj_id
     * @param string $a_obj_type
     * @return array array of history entries (arrays with keys "date", "user_id", "obj_id", "action", "info_params")
     */
    public static function _getEntriesForObject(
        int $a_obj_id,
        string $a_obj_type = ""
    ): array {
        global $DIC;

        $ilDB = $DIC->database();

        if ($a_obj_type == "") {
            $a_obj_type = ilObject::_lookupType($a_obj_id);
        }
        $query = "SELECT * FROM history WHERE obj_id = " .
            $ilDB->quote($a_obj_id, "integer") . " AND " .
            "obj_type = " . $ilDB->quote($a_obj_type, "text") .
            " ORDER BY hdate DESC";

        $hist_set = $ilDB->query($query);
        $hist_items = array();
        while ($hist_rec = $ilDB->fetchAssoc($hist_set)) {
            $hist_items[] = array("date" => $hist_rec["hdate"],
                "user_id" => $hist_rec["usr_id"],
                "obj_id" => $hist_rec["obj_id"],
                "obj_type" => $hist_rec["obj_type"],
                "action" => $hist_rec["action"],
                "info_params" => $hist_rec["info_params"],
                "user_comment" => $hist_rec["user_comment"],
                "hist_entry_id" => $hist_rec["id"]);
        }

        if ($a_obj_type == "lm") {
            $query = "SELECT h.*, l.title as title FROM history h, lm_data l WHERE " .
                " l.lm_id = " . $ilDB->quote($a_obj_id, "integer") . " AND " .
                " l.obj_id = h.obj_id AND " .
                " (h.obj_type=" . $ilDB->quote($a_obj_type . ":pg", "text") . " OR h.obj_type=" . $ilDB->quote($a_obj_type . ":st", "text") . ") " .
                " ORDER BY h.hdate DESC";

            $hist_set = $ilDB->query($query);
            while ($hist_rec = $ilDB->fetchAssoc($hist_set)) {
                $hist_items[] = array("date" => $hist_rec["hdate"],
                    "user_id" => $hist_rec["usr_id"],
                    "obj_id" => $hist_rec["obj_id"],
                    "obj_type" => $hist_rec["obj_type"],
                    "action" => $hist_rec["action"],
                    "info_params" => $hist_rec["info_params"],
                    "user_comment" => $hist_rec["user_comment"],
                    "hist_entry_id" => $hist_rec["id"],
                    "title" => $hist_rec["title"]);
            }
            usort($hist_items, array("ilHistory", "_compareHistArray"));
            $hist_items2 = array_reverse($hist_items);
            return $hist_items2;
        }

        return $hist_items;
    }

    public static function _compareHistArray(array $a, array $b): int
    {
        if ($a["date"] == $b["date"]) {
            return 0;
        }
        return ($a["date"] < $b["date"]) ? -1 : 1;
    }

    /**
     * remove all history entries for an object
     */
    public static function _removeEntriesForObject(int $a_obj_id): void
    {
        global $DIC;

        $ilDB = $DIC->database();

        $q = "DELETE FROM history WHERE obj_id = " .
            $ilDB->quote($a_obj_id, "integer");
        $ilDB->manipulate($q);
    }

    /**
     * copy all history entries for an object
     */
    public static function _copyEntriesForObject(int $a_src_id, int $a_dst_id): void
    {
        global $DIC;

        $ilDB = $DIC->database();

        $q = "SELECT * FROM history WHERE obj_id = " .
            $ilDB->quote($a_src_id, "integer");
        $r = $ilDB->query($q);

        while ($row = $ilDB->fetchObject($r)) {
            $id = $ilDB->nextId("history");
            $ilDB->insert("history", array(
                "id" => array("integer", $id),
                "obj_id" => array("integer", $a_dst_id),
                "obj_type" => array("text", $row->obj_type),
                "action" => array("text", $row->action),
                "hdate" => array("timestamp", ilUtil::now()),
                "usr_id" => array("integer", $row->usr_id),
                "info_params" => array("text", $row->info_params),
                "user_comment" => array("clob", $row->user_comment)
                ));
        }
    }

    /**
     * returns a single history entry
     */
    public static function _getEntryByHistoryID(int $a_hist_entry_id): array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $q = "SELECT * FROM history WHERE id = " .
            $ilDB->quote($a_hist_entry_id, "integer");
        $r = $ilDB->query($q);

        return $ilDB->fetchAssoc($r);
    }

    /**
     * Removes a single entry from the history.
     */
    public static function _removeEntryByHistoryID(int $a_hist_entry_id): void
    {
        global $DIC;

        $ilDB = $DIC->database();

        $q = "DELETE FROM history WHERE id = " .
            $ilDB->quote($a_hist_entry_id, "integer");
        $ilDB->manipulate($q);
    }

    /**
     * Changes the user id of the specified history entry.
     */
    public static function _changeUserId(int $a_hist_entry_id, int $new_user_id): void
    {
        global $DIC;

        $ilDB = $DIC->database();

        $ilDB->update(
            "history",
            array("usr_id" => array("integer", $new_user_id)),
            array("id" => array("integer", $a_hist_entry_id))
        );
    }
}
