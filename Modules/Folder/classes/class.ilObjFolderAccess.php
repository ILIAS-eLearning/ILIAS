<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Class ilObjFileAccess
 *
 * @author 	Stefan Meyer <meyer@leifos.com>
 */
class ilObjFolderAccess extends ilObjectAccess
{
    private static ?ilSetting $folderSettings = null;

    private static function getFolderSettings(): ilSetting
    {
        if (is_null(self::$folderSettings)) {
            self::$folderSettings = new ilSetting('fold');
        }
        return self::$folderSettings;
    }

    public function _checkAccess(string $cmd, string $permission, int $ref_id, int $obj_id, ?int $user_id = null): bool
    {
        if ($cmd === "download" &&
            !self::hasDownloadAction($ref_id)) {
            return false;
        }
        return true;
    }

    public static function _getCommands(): array
    {
        $commands = [];
        $commands[] = ["permission" => "read", "cmd" => "view", "lang_var" => "show", "default" => true];

        // why here, why read permission? it just needs info_screen_enabled = true in ilObjCategoryListGUI (alex, 30.7.2008)
        // this is not consistent, with all other objects...
        //$commands[] = array("permission" => "read", "cmd" => "showSummary", "lang_var" => "info_short", "enable_anonymous" => "false");
        $commands[] = ["permission" => "read", "cmd" => "download", "lang_var" => "download"]; // #18805
        // BEGIN WebDAV: Mount Webfolder.
        if (ilDAVActivationChecker::_isActive()) {
            $webdav_obj = new ilObjWebDAV();
            $commands[] = $webdav_obj->retrieveWebDAVCommandArrayForActionMenu();
        }
        $commands[] = ["permission" => "write", "cmd" => "enableAdministrationPanel", "lang_var" => "edit_content"];
        $commands[] = ["permission" => "write", "cmd" => "edit", "lang_var" => "settings"];

        return $commands;
    }


    private static function hasDownloadAction(int $ref_id): bool
    {
        $settings = self::getFolderSettings();
        if ((int) $settings->get("enable_download_folder", '0') !== 1) {
            return false;
        }
        return true;
    }

    public static function _checkGoto(string $target): bool
    {
        global $DIC;

        $ilAccess = $DIC->access();

        $t_arr = explode("_", $target);

        if ($t_arr[0] !== "fold" || ((int) $t_arr[1]) <= 0) {
            return false;
        }

        if ($ilAccess->checkAccess("read", "", (int) $t_arr[1]) ||
            $ilAccess->checkAccess("visible", "", (int) $t_arr[1])) {
            return true;
        }
        return false;
    }
}
