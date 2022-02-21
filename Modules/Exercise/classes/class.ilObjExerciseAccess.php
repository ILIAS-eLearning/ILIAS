<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjExerciseAccess extends ilObjectAccess implements ilConditionHandling
{
    
    /**
     * Get possible conditions operators
     */
    public static function getConditionOperators() : array
    {
        return array(
            ilConditionHandler::OPERATOR_PASSED,
            ilConditionHandler::OPERATOR_FAILED
        );
    }
    
    
    /**
     * check condition
     * @param int    $a_trigger_obj_id
     * @param string $a_operator
     * @param string $a_value
     * @param int    $a_usr_id
     * @return bool
     */
    public static function checkCondition(int $a_trigger_obj_id, string $a_operator, string $a_value, int $a_usr_id) : bool
    {
        switch ($a_operator) {
            case ilConditionHandler::OPERATOR_PASSED:
                if (ilExerciseMembers::_lookupStatus($a_trigger_obj_id, $a_usr_id) == "passed") {
                    return true;
                }
                return false;

            case ilConditionHandler::OPERATOR_FAILED:
                return ilExerciseMembers::_lookupStatus($a_trigger_obj_id, $a_usr_id) == 'failed';

            default:
                return true;
        }
    }
    

    /**
     * get commands
     *
     * this method returns an array of all possible commands/permission combinations
     *
     * example:
     * $commands = array
     *	(
     *		array("permission" => "read", "cmd" => "view", "lang_var" => "show"),
     *		array("permission" => "write", "cmd" => "edit", "lang_var" => "edit"),
     *	);
     */
    public static function _getCommands() : array
    {
        return array(
            array("permission" => "read", "cmd" => "showOverview", "lang_var" => "show",
                "default" => true),
            array("permission" => "write", "cmd" => "listAssignments", "lang_var" => "edit_assignments"),
            array("permission" => "write", "cmd" => "edit", "lang_var" => "settings")
        );
    }

    /**
     * @throws ilDateTimeException
     */
    public static function _lookupRemainingWorkingTimeString(
        int $a_obj_id
    ) : array {
        global $DIC;

        $ilDB = $DIC->database();
        
        // #14077 - mind peer deadline, too
        
        $dl = null;
        $cnt = array();
        
        $q = "SELECT id, time_stamp, deadline2, peer_dl" .
            " FROM exc_assignment WHERE exc_id = " . $ilDB->quote($a_obj_id, "integer") .
            " AND (time_stamp > " . $ilDB->quote(time(), "integer") .
            " OR (peer_dl > " . $ilDB->quote(time(), "integer") .
            " AND peer > " . $ilDB->quote(0, "integer") . "))";
        $set = $ilDB->query($q);
        while ($row = $ilDB->fetchAssoc($set)) {
            if ($row["time_stamp"] > time() &&
                ($row["time_stamp"] < $dl || !$dl)) {
                $dl = $row["time_stamp"];
            }
            /* extended deadline should not be presented anywhere
            if($row["deadline2"] > time() &&
                ($row["deadline2"] < $dl || !$dl))
            {
                $dl = $row["deadline2"];
            }
            */
            if ($row["peer_dl"] > time() &&
                ($row["peer_dl"] < $dl || !$dl)) {
                $dl = $row["peer_dl"];
            }
            $cnt[$row["id"]] = true;
        }
        
        // :TODO: mind personal deadline?
        
        if ($dl) {
            $dl = ilLegacyFormElementsUtil::period2String(new ilDateTime($dl, IL_CAL_UNIX));
        }
        
        return array(
            "mtime" => $dl,
            "cnt" => sizeof($cnt)
        );
    }
    
    /**
    * check whether goto script will succeed
    */
    public static function _checkGoto($a_target) : bool
    {
        global $DIC;

        $ilAccess = $DIC->access();
        
        $t_arr = explode("_", $a_target);

        if ($t_arr[0] != "exc" || ((int) $t_arr[1]) <= 0) {
            return false;
        }

        if ($ilAccess->checkAccess("read", "", $t_arr[1]) ||
            $ilAccess->checkAccess("visible", "", $t_arr[1])) {
            return true;
        }
        return false;
    }

    /**
     * @param ilWACPath $ilWACPath
     *
     * @return bool
     */
    public function canBeDelivered(ilWACPath $ilWACPath) : bool
    {
        return true;
    }
}
