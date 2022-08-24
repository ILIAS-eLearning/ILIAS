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
class ilObjContentObjectAccess extends ilObjectAccess
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

    public static array $lo_access;

    public function _checkAccess(string $cmd, string $permission, int $ref_id, int $obj_id, ?int $user_id = null): bool
    {
        $ilUser = $this->user;
        $lng = $this->lng;
        $ilAccess = $this->access;

        switch ($cmd) {
            case "continue":

                // continue is now default and works all the time
                // see ilLMPresentationGUI::resume()
                /*
                if ($ilUser->getId() == ANONYMOUS_USER_ID)
                {
                    $ilAccess->addInfoItem(ilAccessInfo::IL_NO_OBJECT_ACCESS, $lng->txt("lm_no_continue_for_anonym"));
                    return false;
                }
                if (ilObjContentObjectAccess::_getLastAccessedPage($ref_id,$user_id) <= 0)
                {
                    $ilAccess->addInfoItem(ilAccessInfo::IL_NO_OBJECT_ACCESS, $lng->txt("not_accessed_yet"));
                    return false;
                }
                */
                break;

            // for permission query feature
            case "info":
                if (!ilObject::lookupOfflineStatus($obj_id)) {
                    $ilAccess->addInfoItem(ilAccessInfo::IL_STATUS_MESSAGE, $lng->txt("online"));
                }
                break;

        }

        return true;
    }

    //
    // access relevant methods
    //

    public static function _getLastAccessedPage(
        int $a_ref_id,
        int $a_user_id = 0
    ): int {
        global $DIC;

        $ilDB = $DIC->database();
        $ilUser = $DIC->user();

        if ($a_user_id == 0) {
            $a_user_id = $ilUser->getId();
        }

        if (isset(self::$lo_access[$a_ref_id])) {
            $acc_rec["obj_id"] = self::$lo_access[$a_ref_id];
        } else {
            $q = "SELECT * FROM lo_access WHERE " .
                "usr_id = " . $ilDB->quote($a_user_id, "integer") . " AND " .
                "lm_id = " . $ilDB->quote($a_ref_id, "integer");

            $acc_set = $ilDB->query($q);
            $acc_rec = $ilDB->fetchAssoc($acc_set);
        }

        if (($acc_rec["obj_id"] ?? 0) > 0) {
            $lm_id = ilObject::_lookupObjId($a_ref_id);
            $mtree = new ilTree($lm_id);
            $mtree->setTableNames('lm_tree', 'lm_data');
            $mtree->setTreeTablePK("lm_id");
            if ($mtree->isInTree($acc_rec["obj_id"])) {
                return $acc_rec["obj_id"];
            }
        }

        return 0;
    }

    public static function _checkGoto(string $target): bool
    {
        global $DIC;

        $ilAccess = $DIC->access();

        $t_arr = explode("_", $target);

        if (($t_arr[0] != "lm" && $t_arr[0] != "st"
            && $t_arr[0] != "pg")
            || ((int) $t_arr[1]) <= 0) {
            return false;
        }

        if ($t_arr[0] == "lm") {
            if ($ilAccess->checkAccess("read", "", $t_arr[1]) ||
                $ilAccess->checkAccess("visible", "", $t_arr[1])) {
                return true;
            }
        } else {
            if (($t_arr[2] ?? 0) > 0) {
                $ref_ids = array($t_arr[2]);
            } else {
                // determine learning object
                $lm_id = ilLMObject::_lookupContObjID($t_arr[1]);
                $ref_ids = ilObject::_getAllReferences($lm_id);
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

    public static function _preloadData(array $obj_ids, array $ref_ids): void
    {
        global $DIC;

        $reading_time_manager = new \ILIAS\LearningModule\ReadingTime\ReadingTimeManager();
        $reading_time_manager->loadData($obj_ids);

        $ilDB = $DIC->database();
        $ilUser = $DIC->user();

        $q = "SELECT obj_id, lm_id FROM lo_access WHERE " .
            "usr_id = " . $ilDB->quote($ilUser->getId(), "integer") . " AND " .
            $ilDB->in("lm_id", $ref_ids, false, "integer");
        $set = $ilDB->query($q);
        foreach ($ref_ids as $r) {
            self::$lo_access[$r] = 0;
        }
        while ($rec = $ilDB->fetchAssoc($set)) {
            self::$lo_access[$rec["lm_id"]] = $rec["obj_id"];
        }
    }

    public static function isInfoEnabled(int $obj_id): bool
    {
        return (bool) ilContainer::_lookupContainerSetting(
            $obj_id,
            ilObjectServiceSettingsGUI::INFO_TAB_VISIBILITY,
            true
        );
    }
}
