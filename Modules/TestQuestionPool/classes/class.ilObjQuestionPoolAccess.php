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
    public static function _getCommands(): array
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

    public function _checkAccess(string $cmd, string $permission, int $ref_id, int $obj_id, ?int $user_id = null): bool
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];
        $lng = $DIC['lng'];
        $rbacsystem = $DIC['rbacsystem'];
        $ilAccess = $DIC['ilAccess'];

        if (is_null($user_id)) {
            $user_id = $ilUser->getId();
        }

        if ($rbacsystem->checkAccessOfUser($user_id, 'write', $ref_id)) {
            return true;
        }

        switch ($permission) {
            case 'visible':
            case 'read':
                if (!self::isOnline($obj_id)) {
                    $ilAccess->addInfoItem(ilAccessInfo::IL_NO_OBJECT_ACCESS, $lng->txt("tst_warning_pool_offline"));
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
    public static function isOnline($a_obj_id): bool
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
