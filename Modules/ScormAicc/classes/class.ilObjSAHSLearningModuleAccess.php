<?php declare(strict_types=1);

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
 * Class ilObjContentObjectAccess
 * @author  Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ModulesScormAicc
 */
class ilObjSAHSLearningModuleAccess extends ilObjectAccess implements ilConditionHandling
{

    /**
     * Get possible conditions operaditors
     * @return string[]
     */
    public static function getConditionOperators() : array
    {
        return array(
            ilConditionHandler::OPERATOR_FINISHED,
            ilConditionHandler::OPERATOR_FAILED
        );
    }

    public static function checkCondition(
        int $a_trigger_obj_id,
        string $a_operator,
        string $a_value,
        int $a_usr_id
    ) : bool {
        switch ($a_operator) {

            case ilConditionHandler::OPERATOR_FAILED:
                return ilLPStatus::_lookupStatus($a_trigger_obj_id, $a_usr_id) == ilLPStatus::LP_STATUS_FAILED_NUM;
                break;

            case ilConditionHandler::OPERATOR_FINISHED:
            default:
                return ilLPStatus::_hasUserCompleted($a_trigger_obj_id, $a_usr_id);

        }
    }

    /**
     * get commands
     * this method returns an array of all possible commands/permission combinations
     * example:
     * $commands = array
     *    (
     *        array("permission" => "read", "cmd" => "view", "lang_var" => "show"),
     *        array("permission" => "write", "cmd" => "edit", "lang_var" => "edit"),
     *    );
     * @return array<int, array>
     */
    public static function _getCommands(int $a_obj_id = null) : array
    {
        $commands = array(
            array("permission" => "read", "cmd" => "view", "lang_var" => "show", "default" => true),
            //            array("permission" => "write", "cmd" => "editContent", "lang_var" => "edit_content"),
            array("permission" => "write", "cmd" => "edit", "lang_var" => "settings")
        );
        return $commands;
    }

    /**
     * check whether goto script will succeed
     */
    public static function _checkGoto(string $target) : bool
    {
        global $DIC;
        $ilAccess = $DIC->access();

        $t_arr = explode("_", $target);

        if ($t_arr[0] !== "sahs" || ((int) $t_arr[1]) <= 0) {
            return false;
        }

        if ($ilAccess->checkAccess("visible", "", (int) $t_arr[1]) || $ilAccess->checkAccess("read", "", (int) $t_arr[1])) {
            return true;
        }
        return false;
    }

    //
    // access relevant methods
    //

//    /**
//    * Lookup editable
//    */
//    public static function _lookupEditable($a_obj_id)
//    {
//        global $DIC;
//        $ilDB = $DIC->database();
//
//        $set = $ilDB->queryF(
//            'SELECT * FROM sahs_lm WHERE id = %s',
//            array('integer'),
//            array($a_obj_id)
//        );
//        $rec = $ilDB->fetchAssoc($set);
//
//        return $rec["editable"];
//    }

    /**
     * Returns the number of bytes used on the harddisk by the learning module
     * with the specified object id.
     * @param int object id of a file object.
     */
    public static function _lookupDiskUsage(int $a_id) : int
    {
        $lm_data_dir = ilFileUtils::getWebspaceDir('filesystem') . "/lm_data";
        $lm_dir = $lm_data_dir . DIRECTORY_SEPARATOR . "lm_" . $a_id;

        return file_exists($lm_dir) ? ilFileUtils::dirsize($lm_dir) : 0;
    }

//    /**
//     * Checks offlineMode and returns false if
//     * @param $a_obj_id
//     * @return bool
//     */
//    public static function _lookupUserIsOfflineMode($a_obj_id) : bool
//    {
//        global $DIC;
//        $ilDB = $DIC->database();
//        $ilUser = $DIC->user();
//
//        $user_id = $ilUser->getId();
//
//        $set = $ilDB->queryF(
//            'SELECT offline_mode FROM sahs_user WHERE obj_id = %s AND user_id = %s',
//            array('integer', 'integer'),
//            array($a_obj_id, $user_id)
//        );
//        $rec = $ilDB->fetchAssoc($set);
//        if (isset($rec["offline_mode"]) && $rec["offline_mode"] === "offline") {
//            return true;
//        }
//        return false;
//    }

//    /**
//     * checks wether a user may invoke a command or not
//     * (this method is called by ilAccessHandler::checkAccess)
//     * @param string $a_cmd        command (not permission!)
//     * @param string $a_permission permission
//     * @param int    $a_ref_id     reference id
//     * @param int    $a_obj_id     object id
//     * @param int    $a_user_id    user id (if not provided, current user is taken)
//     * @return    boolean        true, if everything is ok
//     */
//    public function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = 0) : bool //UK weg?
//    {
//        global $DIC;
//        $ilUser = $DIC->user();
//        $lng = $DIC->language();
//        $rbacsystem = $DIC['rbacsystem'];//$DIC->rbac();
//        $ilAccess = $DIC->access();
//
//        if ($a_user_id == 0) {
//            $a_user_id = $ilUser->getId();
//        }
////        switch ($a_cmd) {
////            case "view":
////                if (!ilObjSAHSLearningModuleAccess::_lookupOnline($a_obj_id)
////                    && !$rbacsystem->checkAccessOfUser($a_user_id, 'write', $a_ref_id)) {
////                    $ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, $lng->txt("offline"));
////                    return false;
////                }
////                break;
////        }
////
////        switch ($a_permission) {
////            case "visible":
////                if (!ilObjSAHSLearningModuleAccess::_lookupOnline($a_obj_id) &&
////                    (!$rbacsystem->checkAccessOfUser($a_user_id, 'write', $a_ref_id))) {
////                    $ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, $lng->txt("offline"));
////                    return false;
////                }
////                break;
////        }
//
//        return true;
//    }
}
