<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Object/classes/class.ilObjectAccess.php");
include_once './Services/Conditions/interfaces/interface.ilConditionHandling.php';

/**
* Class ilObjExerciseAccess
*
*
* @author 		Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesExercise
*/
class ilObjExerciseAccess extends ilObjectAccess implements ilConditionHandling
{
    
    /**
     * Get possible conditions operators
     */
    public static function getConditionOperators()
    {
        include_once './Services/Conditions/classes/class.ilConditionHandler.php';
        return array(
            ilConditionHandler::OPERATOR_PASSED,
            ilConditionHandler::OPERATOR_FAILED
        );
    }
    
    
    /**
     * check condition
     * @param type $a_exc_id
     * @param type $a_operator
     * @param type $a_value
     * @param type $a_usr_id
     * @return boolean
     */
    public static function checkCondition($a_exc_id, $a_operator, $a_value, $a_usr_id)
    {
        include_once './Services/Conditions/classes/class.ilConditionHandler.php';
        include_once './Modules/Exercise/classes/class.ilExerciseMembers.php';
        
        switch ($a_operator) {
            case ilConditionHandler::OPERATOR_PASSED:
                if (ilExerciseMembers::_lookupStatus($a_exc_id, $a_usr_id) == "passed") {
                    return true;
                } else {
                    return false;
                }
                break;
                
            case ilConditionHandler::OPERATOR_FAILED:
                return ilExerciseMembers::_lookupStatus($a_exc_id, $a_usr_id) == 'failed';

            default:
                return true;
        }
        return true;
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
    public static function _getCommands()
    {
        $commands = array(
            array("permission" => "read", "cmd" => "showOverview", "lang_var" => "show",
                "default" => true),
            array("permission" => "write", "cmd" => "listAssignments", "lang_var" => "edit_assignments"),
            array("permission" => "write", "cmd" => "edit", "lang_var" => "settings")
        );
        
        return $commands;
    }
    
    public static function _lookupRemainingWorkingTimeString($a_obj_id)
    {
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
            $dl = ilUtil::period2String(new ilDateTime($dl, IL_CAL_UNIX));
        }
        
        return array(
            "mtime" => $dl,
            "cnt" => sizeof($cnt)
        );
    }
    
    /**
    * check whether goto script will succeed
    */
    public static function _checkGoto($a_target)
    {
        global $DIC;

        $ilAccess = $DIC->access();
        
        $t_arr = explode("_", $a_target);

        if ($t_arr[0] != "exc" || ((int) $t_arr[1]) <= 0) {
            return false;
        }

        if ($ilAccess->checkAccess("read", "", $t_arr[1])) {
            return true;
        }
        return false;
    }

    /**
     * @param ilWACPath $ilWACPath
     *
     * @return bool
     */
    public function canBeDelivered(ilWACPath $ilWACPath)
    {
        global $ilAccess;

        return true;

        // to do: check the path, extract the IDs from the path
        // determine the object ID of the corresponding exercise
        // get all ref IDs of the exercise from the object id and check if use
        // has read access to any of these ref ids (if yes, return true)

        preg_match("/\\/poll_([\\d]*)\\//uism", $ilWACPath->getPath(), $results);

        foreach (ilObject2::_getAllReferences($results[1]) as $ref_id) {
            if ($ilAccess->checkAccess('read', '', $ref_id)) {
                return true;
            }
        }

        return false;
    }
}
