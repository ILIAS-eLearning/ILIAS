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
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjWikiAccess extends ilObjectAccess
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


    public static function _getCommands() : array
    {
        $commands = array(
            array("permission" => "read", "cmd" => "view", "lang_var" => "show",
                "default" => true),
            array("permission" => "write", "cmd" => "editSettings", "lang_var" => "settings")
        );
        
        return $commands;
    }
    
    public function _checkAccess(string $cmd, string $permission, int $ref_id, int $obj_id, ?int $user_id = null) : bool
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

                $info = ilExcRepoObjAssignment::getInstance()->getAccessInfo($ref_id, $user_id);
                if (!$info->isGranted()) {
                    $ilAccess->addInfoItem(ilAccessInfo::IL_NO_OBJECT_ACCESS, implode(" / ", $info->getNotGrantedReasons()));
                    return false;
                }
                break;
        }

        return true;
    }

    public static function _checkGoto(string $target) : bool
    {
        global $DIC;

        $ilAccess = $DIC->access();
        //	echo "-".$target."-"; exit;
        $t_arr = explode("_", $target);

        if ($t_arr[0] !== "wiki" || (((int) $t_arr[1]) <= 0) && $t_arr[1] !== "wpage") {
            return false;
        }
        
        if ($t_arr[1] === "wpage") {
            $wpg_id = (int) $t_arr[2];
            $w_id = ilWikiPage::lookupWikiId($wpg_id);
            if ((int) $t_arr[3] > 0) {
                $refs = array((int) $t_arr[3]);
            } else {
                $refs = ilObject::_getAllReferences($w_id);
            }
            foreach ($refs as $r) {
                if ($ilAccess->checkAccess("read", "", $r) ||
                    $ilAccess->checkAccess("visible", "", $r)) {
                    return true;
                }
            }
        } elseif ($ilAccess->checkAccess("read", "", $t_arr[1]) ||
            $ilAccess->checkAccess("visible", "", $t_arr[1])) {
            return true;
        }
        return false;
    }
    
    public static function _lookupOnline(int $a_id) : bool
    {
        global $DIC;

        $ilDB = $DIC->database();

        $q = "SELECT * FROM il_wiki_data WHERE id = " .
            $ilDB->quote($a_id, "integer");
        $wk_set = $ilDB->query($q);
        $wk_rec = $ilDB->fetchAssoc($wk_set);

        return (bool) $wk_rec["is_online"];
    }

    /**
     * see legacyOnlineFilter in ilContainer
     * @deprecated
     */
    public static function _lookupOnlineStatus(array $a_ids) : array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $q = "SELECT id, is_online FROM il_wiki_data WHERE " .
            $ilDB->in("id", $a_ids, false, "integer");
        $lm_set = $ilDB->query($q);
        $status = [];
        while ($r = $ilDB->fetchAssoc($lm_set)) {
            $status[$r["id"]] = (bool) $r["is_online"];
        }
        return $status;
    }


    /**
     * Check wether files should be public
     */
    public static function _lookupPublicFiles(int $a_id) : bool
    {
        global $DIC;

        $ilDB = $DIC->database();

        $q = "SELECT * FROM il_wiki_data WHERE id = " .
            $ilDB->quote($a_id, "integer");
        $wk_set = $ilDB->query($q);
        $wk_rec = $ilDB->fetchAssoc($wk_set);

        return (bool) $wk_rec["public_files"];
    }
}
