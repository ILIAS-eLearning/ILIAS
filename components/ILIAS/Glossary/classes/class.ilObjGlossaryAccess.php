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
 * Class ilObjGlossaryAccess
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjGlossaryAccess extends ilObjectAccess
{
    protected ilObjUser $user;
    protected ilLanguage $lng;
    protected ilRbacSystem $rbacsystem;
    protected ilAccessHandler $access;

    public function __construct()
    {
        global $DIC;

        $this->user = $DIC->user();
        $this->lng = $DIC->language();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->access = $DIC->access();
    }

    public function _checkAccess(string $cmd, string $permission, int $ref_id, int $obj_id, ?int $user_id = null): bool
    {
        $ilUser = $this->user;
        $lng = $this->lng;
        $rbacsystem = $this->rbacsystem;
        $ilAccess = $this->access;

        if (is_null($user_id)) {
            $user_id = $ilUser->getId();
        }

        switch ($permission) {
            case "read":
                if (!self::_lookupOnline($obj_id)
                    && !$rbacsystem->checkAccessOfUser($user_id, 'write', $ref_id)) {
                    $ilAccess->addInfoItem(ilAccessInfo::IL_NO_OBJECT_ACCESS, $lng->txt("offline"));
                    return false;
                }
                break;

            case "visible":
                if (!self::_lookupOnline($obj_id) &&
                    (!$rbacsystem->checkAccessOfUser($user_id, 'write', $ref_id))) {
                    $ilAccess->addInfoItem(ilAccessInfo::IL_NO_OBJECT_ACCESS, $lng->txt("offline"));
                    return false;
                }
                break;
        }


        return true;
    }

    public static function _getCommands(): array
    {
        $commands = array(
            array("permission" => "read", "cmd" => "view", "lang_var" => "show",
                "default" => true),
            array("permission" => "write", "cmd" => "edit", "lang_var" => "edit_content"),
            array("permission" => "edit_content", "cmd" => "edit", "lang_var" => "edit_content"), // #11099
            array("permission" => "write", "cmd" => "properties", "lang_var" => "settings")
        );

        return $commands;
    }

    //
    // access relevant methods
    //

    public static function _lookupOnline(int $a_id): bool
    {
        global $DIC;

        $ilDB = $DIC->database();

        $q = "SELECT * FROM glossary WHERE id = " .
            $ilDB->quote($a_id, "integer");
        $lm_set = $ilDB->query($q);
        if ($lm_rec = $ilDB->fetchAssoc($lm_set)) {
            return ilUtil::yn2tf($lm_rec["is_online"]);
        }
        return false;
    }

    public static function _lookupOnlineStatus(array $a_ids): array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $q = "SELECT id, is_online FROM glossary WHERE " .
            $ilDB->in("id", $a_ids, false, "integer");
        $lm_set = $ilDB->query($q);
        $status = [];
        while ($r = $ilDB->fetchAssoc($lm_set)) {
            $status[$r["id"]] = ilUtil::yn2tf($r["is_online"]);
        }

        return $status;
    }


    public static function _checkGoto(string $target): bool
    {
        global $DIC;

        $ilAccess = $DIC->access();

        $t_arr = explode("_", $target);

        if (($t_arr[0] != "glo" && $t_arr[0] != "git") || ((int) $t_arr[1]) <= 0) {
            return false;
        }

        if ($t_arr[0] == "glo") {
            if ($ilAccess->checkAccess("read", "", $t_arr[1]) ||
                $ilAccess->checkAccess("visible", "", $t_arr[1])) {
                return true;
            }
        }

        if ($t_arr[0] == "git") {
            if ((int) ($t_arr[2] ?? 0) > 0) {
                $ref_ids = array($t_arr[2]);
            } else {
                // determine learning object
                $glo_id = ilGlossaryTerm::_lookGlossaryID((int) $t_arr[1]);
                $ref_ids = ilObject::_getAllReferences($glo_id);
            }
            // check read permissions
            foreach ($ref_ids as $ref_id) {
                // Permission check
                if ($ilAccess->checkAccess("read", "", $ref_id)) {
                    return true;
                }
            }
        }

        return false;
    }
}
