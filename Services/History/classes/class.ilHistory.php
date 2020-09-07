<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* This class methods for maintain history enties for objects
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
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
    *
    * @param	int			$a_obj_id		object id
    * @param	string		$a_action		action
    * @param	string		$a_info_params	information text parameters, separated by comma
    *										or as an array
    * @param	string		$a_obj_type		object type (must only be set, if object is not
    *										in object_data table)
    * @param	string		$a_user_comment	user comment
    */
    public static function _createEntry(
        $a_obj_id,
        $a_action,
        $a_info_params = "",
        $a_obj_type = "",
        $a_user_comment = "",
        $a_update_last = false
    ) {
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
                
        /*$query = "INSERT INTO history (id, obj_id, obj_type, action, hdate, usr_id, info_params, user_comment) VALUES ".
            "(".
            $ilDB->quote($id).", ".
            $ilDB->quote($a_obj_id).", ".
            $ilDB->quote($a_obj_type).", ".
            $ilDB->quote($a_action).", ".
            "now(), ".
            $ilDB->quote($ilUser->getId()).", ".
            $ilDB->quote($a_info_params).", ".
            $ilDB->quote($a_user_comment).
            ")";
        $ilDB->query($query);*/
        } else {
            // if entry should be updated, update user comment only
            // if it is set (this means, user comment has been empty
            // because if old and new comment are given, an INSERT is forced
            // see if statement above)
            //$uc_str = ($a_user_comment != "")
            //	? ", user_comment = ".$ilDB->quote($a_user_comment)
            //	: "";
            /*$query = "UPDATE history SET ".
                " hdate = now() ".
                $uc_str.
                " WHERE id = ".$ilDB->quote($last_entry["id"]);
            $ilDB->query($query);*/
            
            $fields = array(
                "hdate" => array("timestamp", ilUtil::now())
                );
            if ($a_user_comment != "") {
                $fields["user_comment"] = array("clob", $a_user_comment);
            }

            $ilDB->update("history", $fields, array(
                "id" => array("integer", $id)
                ));
        }
    }
    
    /**
    * get all history entries for an object
    *
    * @param	int		$a_obj_id		object id
    *
    * @return	array	array of history entries (arrays with keys
    *					"date", "user_id", "obj_id", "action", "info_params")
    */
    public static function _getEntriesForObject($a_obj_id, $a_obj_type = "")
    {
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
                "hist_entry_id" => $hist_rec["id"],
                "title" => $hist_rec["title"]);
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

    public static function _compareHistArray($a, $b)
    {
        if ($a["date"] == $b["date"]) {
            return 0;
        }
        return ($a["date"] < $b["date"]) ? -1 : 1;
    }

    /**
    * remove all history entries for an object
    *
    * @param	int		$a_obj_id		object id
    *
    * @return	boolean
    */
    public static function _removeEntriesForObject($a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $q = "DELETE FROM history WHERE obj_id = " .
            $ilDB->quote($a_obj_id, "integer");
        $r = $ilDB->manipulate($q);
        
        return true;
    }
    
    /**
    * copy all history entries for an object
    *
    * @param	integer $a_src_id		source object id
    * @param	integer $a_dst_id		destination object id
    * @return	boolean
    */
    public static function _copyEntriesForObject($a_src_id, $a_dst_id)
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

            /*
            $q = "INSERT INTO history (obj_id, obj_type, action, hdate, usr_id, info_params, user_comment) VALUES ".
                 "(".
                    $ilDB->quote($a_dst_id).", ".
                    $ilDB->quote($row->obj_type).", ".
                    $ilDB->quote($row->action).", ".
                    $ilDB->quote($row->hdate).", ".
                    $ilDB->quote($row->usr_id).", ".
                    $ilDB->quote($row->info_params).", ".
                    $ilDB->quote($row->user_comment).
                 ")";

            $ilDB->query($q);*/
        }
        
        return true;
    }
    
    /**
     * returns a single history entry
     *
     *
     */
    public static function _getEntryByHistoryID($a_hist_entry_id)
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
     *
     * @param int $a_hist_entry_id The id of the entry to remove.
     */
    public static function _removeEntryByHistoryID($a_hist_entry_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $q = "DELETE FROM history WHERE id = " .
            $ilDB->quote($a_hist_entry_id, "integer");
        $ilDB->manipulate($q);
    }
    
    /**
     * Changes the user id of the specified history entry.
     *
     * @param int $a_hist_entry_id The history entry to change the user id.
     * @param int $new_user_id The new user id.
     */
    public static function _changeUserId($a_hist_entry_id, $new_user_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $ilDB->update(
            "history",
            array("usr_id" => array("integer", $new_user_id)),
            array("id" => array("integer", $a_hist_entry_id))
        );
    }
} // END class.ilHistory
