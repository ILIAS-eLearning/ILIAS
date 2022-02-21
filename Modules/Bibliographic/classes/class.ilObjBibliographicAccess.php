<?php

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
    public static function _getCommands(): array
    {
        $commands = array(
            array(
                "permission" => "read",
                "cmd" => "render",
                "lang_var" => "show",
                "default" => true,
            ),
            array("permission" => "write", "cmd" => "view", "lang_var" => "edit_content"),
            array("permission" => "write", "cmd" => "edit", "lang_var" => "settings"),
        );

        return $commands;
    }


    public static function _checkGoto(string $target): bool
    {
        global $DIC;
        $ilAccess = $DIC['ilAccess'];
        $lng = $DIC['lng'];
        $t_arr = explode('_', $target);
        if ($t_arr[0] != 'bibl' || ((int) $t_arr[1]) <= 0) {
            return false;
        }
        if ($ilAccess->checkAccess('read', '', $t_arr[1])) {
            return true;
        }

        return false;
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
                $DIC->refinery()->to()->int()
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


    /**
     * @param $ref_id
     * @param $obj_id
     */
    private static function checkEntryIdMatch($obj_id, $entry_id): bool
    {
        /**
         * @var $ilBiblEntry ilBiblEntry
         */
        $ilBiblEntry = ilBiblEntry::find($entry_id);
        if (is_null($ilBiblEntry)) {
            return false;
        }

        return ($ilBiblEntry->getDataId() == $obj_id);
    }


    /**
     * Check wether bibliographic is online or not
     *
     * @param int $a_id bibl id
     */
    public static function _lookupOnline(int $a_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $q = "SELECT is_online FROM il_bibl_data WHERE id = " . $ilDB->quote($a_id, "integer");
        $bibl_set = $ilDB->query($q);
        $bibl_rec = $ilDB->fetchAssoc($bibl_set);

        return $bibl_rec["is_online"];
    }
}
