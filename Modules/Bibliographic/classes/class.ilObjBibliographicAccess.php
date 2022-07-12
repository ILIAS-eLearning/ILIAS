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
 * Class ilObjBibliographicAccess
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 * @author Gabriel Comte <gc@studer-raimann.ch>
 *
 */
class ilObjBibliographicAccess extends ilObjectAccess
{

    /**
     * get commands
     *
     * this method returns an array of all possible commands/permission combinations
     *
     * example:
     * $commands = array
     *    (
     *        array("permission" => "read", "cmd" => "view", "lang_var" => "show"),
     *        array("permission" => "write", "cmd" => "edit", "lang_var" => "edit"),
     *    );
     * @return array<int, array<string, string>>|array<int, array<string, string|bool>>
     */
    public static function _getCommands() : array
    {
        return array(
            array(
                "permission" => "read",
                "cmd" => "render",
                "lang_var" => "show",
                "default" => true,
            ),
            array("permission" => "write", "cmd" => "view", "lang_var" => "edit_content"),
            array("permission" => "write", "cmd" => "edit", "lang_var" => "settings"),
        );
    }


    public static function _checkGoto(string $target) : bool
    {
        global $DIC;
        $ilAccess = $DIC['ilAccess'];
        $t_arr = explode('_', $target);
        if ($t_arr[0] != 'bibl' || ((int) $t_arr[1]) <= 0) {
            return false;
        }
        return (bool) $ilAccess->checkAccess('read', '', $t_arr[1]);
    }


    /**
     * checks whether a user may invoke a command or not
     * (this method is called by ilAccessHandler::checkAccess)
     */
    public function _checkAccess(string $cmd, string $permission, int $ref_id, int $obj_id, ?int $user_id = null) : bool
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];
        $lng = $DIC['lng'];
        $rbacsystem = $DIC['rbacsystem'];
        $ilAccess = $DIC['ilAccess'];
        if (is_null($user_id)) {
            $user_id = $ilUser->getId();
        }

        if ($DIC->http()->wrapper()->query()->has(ilObjBibliographicGUI::P_ENTRY_ID)) {
            $entry_id = $DIC->http()->wrapper()->query()->retrieve(
                ilObjBibliographicGUI::P_ENTRY_ID,
                $DIC->refinery()->kindlyTo()->int()
            );
            if (!self::checkEntryIdMatch($obj_id, $entry_id)) {
                return false;
            }
        }

        switch ($cmd) {
            case "view":
                if (!self::_lookupOnline($obj_id)
                    && !$rbacsystem->checkAccessOfUser($user_id, 'write', $ref_id)
                ) {
                    $ilAccess->addInfoItem(ilAccessInfo::IL_NO_OBJECT_ACCESS, $lng->txt("offline"));

                    return false;
                }
                break;
            // for permission query feature
            case "infoScreen":
                if (!self::_lookupOnline($obj_id)) {
                    $ilAccess->addInfoItem(ilAccessInfo::IL_NO_OBJECT_ACCESS, $lng->txt("offline"));
                } else {
                    $ilAccess->addInfoItem(ilAccessInfo::IL_STATUS_MESSAGE, $lng->txt("online"));
                }
                break;
        }
        switch ($permission) {
            case "read":
            case "visible":
                if (!self::_lookupOnline($obj_id)
                    && (!$rbacsystem->checkAccessOfUser($user_id, 'write', $ref_id))
                ) {
                    $ilAccess->addInfoItem(ilAccessInfo::IL_NO_OBJECT_ACCESS, $lng->txt("offline"));

                    return false;
                }
                break;
        }

        return true;
    }


    private static function checkEntryIdMatch(int $obj_id, int $entry_id) : bool
    {
        /**
         * @var ilBiblEntry $ilBiblEntry
         */
        $ilBiblEntry = ilBiblEntry::find($entry_id);
        if (is_null($ilBiblEntry)) {
            return false;
        }

        return ($ilBiblEntry->getDataId() === $obj_id);
    }


    /**
     * Check wether bibliographic is online or not
     */
    public static function _lookupOnline(int $a_id) : bool
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $q = "SELECT is_online FROM il_bibl_data WHERE id = " . $ilDB->quote($a_id, "integer");
        $bibl_set = $ilDB->query($q);
        $bibl_rec = $ilDB->fetchAssoc($bibl_set);
    
        return (bool) $bibl_rec["is_online"] ?? false;
    }
}
