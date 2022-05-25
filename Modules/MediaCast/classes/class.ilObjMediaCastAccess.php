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
class ilObjMediaCastAccess extends ilObjectAccess
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
            array("permission" => "read", "cmd" => "showContent", "lang_var" => "show",
                "default" => true),
            array("permission" => "write", "cmd" => "listItems", "lang_var" => "edit_content"),
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
            case "listItems":

                if (!ilObjMediaCastAccess::_lookupOnline($obj_id)
                    && !$rbacsystem->checkAccessOfUser($user_id, 'write', $ref_id)) {
                    $ilAccess->addInfoItem(ilAccessInfo::IL_NO_OBJECT_ACCESS, $lng->txt("offline"));
                    return false;
                }
                break;
                
            // for permission query feature
            case "infoScreen":
                if (!ilObjMediaCastAccess::_lookupOnline($obj_id)) {
                    $ilAccess->addInfoItem(ilAccessInfo::IL_NO_OBJECT_ACCESS, $lng->txt("offline"));
                } else {
                    $ilAccess->addInfoItem(ilAccessInfo::IL_STATUS_MESSAGE, $lng->txt("online"));
                }
                break;

        }
        switch ($permission) {
            case "read":
            case "visible":
                if (!ilObjMediaCastAccess::_lookupOnline($obj_id) &&
                    (!$rbacsystem->checkAccessOfUser($user_id, 'write', $ref_id))) {
                    $ilAccess->addInfoItem(ilAccessInfo::IL_NO_OBJECT_ACCESS, $lng->txt("offline"));
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
        
        $t_arr = explode("_", $target);

        if ($t_arr[0] != "mcst" || ((int) $t_arr[1]) <= 0) {
            return false;
        }

        if ($ilAccess->checkAccess("read", "", $t_arr[1]) ||
            $ilAccess->checkAccess("visible", "", $t_arr[1])) {
            return true;
        }
        return false;
    }
    
    public static function _lookupOnline(int $a_id) : bool
    {
        global $DIC;

        $ilDB = $DIC->database();

        $q = "SELECT * FROM il_media_cast_data WHERE id = " . $ilDB->quote($a_id);
        $mc_set = $ilDB->query($q);
        $mc_rec = $mc_set->fetchRow(ilDBConstants::FETCHMODE_ASSOC);

        return (bool) ($mc_rec["is_online"] ?? false);
    }

    public static function _lookupPublicFiles(int $a_id) : bool
    {
        global $DIC;

        $ilDB = $DIC->database();

        $q = "SELECT * FROM il_media_cast_data WHERE id = " . $ilDB->quote($a_id);
        $mc_set = $ilDB->query($q);
        $mc_rec = $mc_set->fetchRow(ilDBConstants::FETCHMODE_ASSOC);

        return (bool) $mc_rec["public_files"];
    }

    /**
     * Returns the number of bytes used on the harddisk by the file object
     * with the specified object id.
     */
    public static function _lookupDiskUsage(int $a_id) : int
    {
        $obj = new ilObjMediaCast($a_id, false);
        $obj->read();
        $items = $obj->getItemsArray();
        $size = 0;
        foreach ($items as $item) {
            $news_item = new ilNewsItem($item["id"]);
            $news_item->read();
            $mobId = $news_item->getMobId();
            $size += ilFileUtils::dirsize(ilObjMediaObject::_getDirectory($mobId));
        }
        return $size;
    }
}
