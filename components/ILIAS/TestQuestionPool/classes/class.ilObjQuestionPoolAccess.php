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
* Class ilObjQuestionPoolAccess
*
*
* @author		Helmut Schottmueller <helmut.schottmueller@mac.com>
* @author 		Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup components\ILIASTestQuestionPool
*/
class ilObjQuestionPoolAccess extends ilObjectAccess
{
    private ilObjUser $current_user;
    private ilLanguage $lng;
    private ilRbacSystem $rbacsystem;
    private ilAccess $access;

    public function __construct()
    {
        global $DIC;
        $this->current_user = $DIC['ilUser'];
        $this->lng = $DIC['lng'];
        $this->rbacsystem = $DIC['rbacsystem'];
        $this->access = $DIC['ilAccess'];
        ;
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
        if (is_null($user_id)) {
            $user_id = $this->current_user->getId();
        }

        if ($this->rbacsystem->checkAccessOfUser($user_id, 'write', $ref_id)) {
            return true;
        }

        switch ($permission) {
            case 'visible':
            case 'read':
                if (self::_isOffline($obj_id)) {
                    $this->access->addInfoItem(ilAccessInfo::IL_NO_OBJECT_ACCESS, $this->lng->txt("tst_warning_pool_offline"));
                    return false;
                }
                break;
        }

        return true;
    }
}
