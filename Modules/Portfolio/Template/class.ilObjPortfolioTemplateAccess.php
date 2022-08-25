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
 * Class ilObjPortfolioTemplateAccess
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilObjPortfolioTemplateAccess extends ilObjectAccess
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

    public static function _getCommands(): array
    {
        $commands = array(
            array("permission" => "read", "cmd" => "preview", "lang_var" => "preview", "default" => true),
            array("permission" => "write", "cmd" => "view", "lang_var" => "edit"),
            array("permission" => "read", "cmd" => "createfromtemplate", "lang_var" => "prtf_create_portfolio_from_template"),
            // array("permission" => "write", "cmd" => "export", "lang_var" => "export_html")
        );

        return $commands;
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

        switch ($cmd) {
            case "view":
                if (!self::_lookupOnline($obj_id)
                     && !$rbacsystem->checkAccessOfUser($user_id, 'write', $ref_id)) {
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
                if (!self::_lookupOnline($obj_id) &&
                     (!$rbacsystem->checkAccessOfUser($user_id, 'write', $ref_id))) {
                    $ilAccess->addInfoItem(ilAccessInfo::IL_NO_OBJECT_ACCESS, $lng->txt("offline"));
                    return false;
                }
                break;
        }

        return true;
    }

    public static function _lookupOnline(int $a_id): bool
    {
        return ilObjPortfolioTemplate::lookupOnline($a_id);
    }

    /**
     * @deprecated
     */
    public static function _lookupOnlineStatus(array $a_ids): array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $q = "SELECT id, is_online FROM usr_portfolio WHERE " .
            $ilDB->in("id", $a_ids, false, "integer");
        $lm_set = $ilDB->query($q);
        $status = [];
        while ($r = $ilDB->fetchAssoc($lm_set)) {
            $status[$r["id"]] = $r["is_online"];
        }
        return $status;
    }


    /**
     * check whether goto script will succeed
     */
    public static function _checkGoto(string $target): bool
    {
        global $DIC;

        $ilAccess = $DIC->access();

        $t_arr = explode("_", $target);

        if ($t_arr[0] !== "prtt" || ((int) $t_arr[1]) <= 0) {
            return false;
        }

        if ($ilAccess->checkAccess("read", "", $t_arr[1]) ||
            $ilAccess->checkAccess("visible", "", $t_arr[1])) {
            return true;
        }
        return false;
    }
}
