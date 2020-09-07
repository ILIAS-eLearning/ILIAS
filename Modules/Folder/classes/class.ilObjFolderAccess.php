<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Object/classes/class.ilObjectAccess.php");

/**
* Class ilObjFileAccess
*
*
* @author 	Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*/
class ilObjFolderAccess extends ilObjectAccess
{
    private static $folderSettings;

    private static function getFolderSettings()
    {
        if (is_null(ilObjFolderAccess::$folderSettings)) {
            ilObjFolderAccess::$folderSettings = new ilSetting('fold');
        }
        return ilObjFolderAccess::$folderSettings;
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
    public static function _getCommands()
    {
        $commands = array();
        $commands[] = array("permission" => "read", "cmd" => "view", "lang_var" => "show", "default" => true);

        include_once './Services/WebServices/FileManager/classes/class.ilFMSettings.php';
        if (ilFMSettings::getInstance()->isEnabled()) {
            $commands[] = array(
                'permission' => 'read',
                'cmd' => 'fileManagerLaunch',
                'lang_var' => 'fm_start',
                'enable_anonymous' => false
            );
        }

        // why here, why read permission? it just needs info_screen_enabled = true in ilObjCategoryListGUI (alex, 30.7.2008)
        // this is not consistent, with all other objects...
        //$commands[] = array("permission" => "read", "cmd" => "showSummary", "lang_var" => "info_short", "enable_anonymous" => "false");
        if (ilObjFolderAccess::hasDownloadAction($_GET["ref_id"])) {
            $commands[] = array("permission" => "read", "cmd" => "downloadFolder", "lang_var" => "download"); // #18805
        }
        // BEGIN WebDAV: Mount Webfolder.
        include_once('Services/WebDAV/classes/class.ilDAVActivationChecker.php');
        if (ilDAVActivationChecker::_isActive()) {
            include_once './Services/WebDAV/classes/class.ilWebDAVUtil.php';
            if (ilWebDAVUtil::getInstance()->isLocalPasswordInstructionRequired()) {
                $commands[] = array('permission' => 'read', 'cmd' => 'showPasswordInstruction', 'lang_var' => 'mount_webfolder', 'enable_anonymous' => 'false');
            } else {
                $commands[] = array("permission" => "read", "cmd" => "mount_webfolder", "lang_var" => "mount_webfolder", "enable_anonymous" => "false");
            }
        }
        $commands[] = array("permission" => "write", "cmd" => "enableAdministrationPanel", "lang_var" => "edit_content");
        $commands[] = array("permission" => "write", "cmd" => "edit", "lang_var" => "settings");
        
        return $commands;
    }

    
    private static function hasDownloadAction($ref_id)
    {
        global $DIC;

        $tree = $DIC->repositoryTree();
        $ilUser = $DIC->user();
        $settings = ilObjFolderAccess::getFolderSettings();
        // default value should reflect previous behaviour (-> 0)
        if ($settings->get("enable_download_folder", 0) != 1) {
            return false;
        }
            
        /*
         * deactivated check for now, because wrong ref_id here!

         *
        $children = $tree->getChildsByTypeFilter($ref_id, array("file","fold"));

        // no children at all, so no download button
        if (count ($children) == 0)
            return false;
        // check if at least one of the children has a read permission
        foreach ($children as $child)
        {
            if ($rbacsystem->checkAccessOfUser($ilUser->getId(), "read", $child["ref_id"]))
                return true;
        }
        return false;
        */
        return true;
    }
}
