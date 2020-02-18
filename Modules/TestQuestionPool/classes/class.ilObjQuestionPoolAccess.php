<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Services/Object/classes/class.ilObjectAccess.php";
include_once "./Modules/Test/classes/inc.AssessmentConstants.php";

/**
* Class ilObjQuestionPoolAccess
*
*
* @author		Helmut Schottmueller <helmut.schottmueller@mac.com>
* @author 		Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup ModulesTestQuestionPool
*/
class ilObjQuestionPoolAccess extends ilObjectAccess
{
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
            array("permission" => "write", "cmd" => "questions", "lang_var" => "tst_edit_questions"),
            array("permission" => "write", "cmd" => "ilObjQuestionPoolSettingsGeneralGUI::showForm", "lang_var" => "settings"),
            #array("permission" => "write", "cmd" => "questions", "lang_var" => "edit",
            #	"default" => false),
            array("permission" => "read", "cmd" => "questions", "lang_var" => "edit",
                "default" => true)
        );
        
        return $commands;
    }

    /**
     * @param string $a_cmd
     * @param string $a_permission
     * @param int $a_ref_id
     * @param int $a_obj_id
     * @param string $a_user_id
     * @return bool
     */
    public function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = "")
    {
        global $DIC;
        $lng = $DIC['lng'];
        $ilAccess = $DIC['ilAccess'];

        global $DIC;
        $ilUser = $DIC['ilUser'];
        $lng = $DIC['lng'];
        $rbacsystem = $DIC['rbacsystem'];
        $ilAccess = $DIC['ilAccess'];

        if ($a_user_id == "") {
            $a_user_id = $ilUser->getId();
        }

        if ($rbacsystem->checkAccessOfUser($a_user_id, 'write', $a_ref_id)) {
            return true;
        }

        switch ($a_permission) {
            case 'visible':
            case 'read':
                if (!self::isOnline($a_obj_id)) {
                    $ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, $lng->txt("tst_warning_pool_offline"));
                    return false;
                }
                break;
        }

        return true;
    }

    /**
     * returns the objects's ONline status
     *
     * @param integer $a_obj_id
     * @return boolean $online
     */
    public static function isOnline($a_obj_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $query = "
			SELECT		COUNT(id_questionpool) cnt
			FROM		qpl_questionpool
			WHERE		obj_fi = %s
			AND			isonline = 1
		";

        $res = $ilDB->queryF($query, array('integer'), array($a_obj_id));
        $row = $ilDB->fetchAssoc($res);
        
        return $row['cnt'] > 0;
    }
}
