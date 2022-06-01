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
 * Class ilObjBlogAccess
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilObjBlogAccess extends ilObjectAccess
{
    protected ilObjUser $user;
    protected ilAccessHandler $access;

    public function __construct()
    {
        global $DIC;

        $this->user = $DIC->user();
        $this->access = $DIC->access();
    }

    public static function _getCommands() : array
    {
        return array(
            array("permission" => "read", "cmd" => "preview", "lang_var" => "show", "default" => true),
            array("permission" => "write", "cmd" => "render", "lang_var" => "edit"),
            array("permission" => "contribute", "cmd" => "render", "lang_var" => "edit"),
            array("permission" => "write", "cmd" => "edit", "lang_var" => "settings"),
            array("permission" => "write", "cmd" => "export", "lang_var" => "export_html")
        );
    }
    
    public static function _checkGoto(string $target) : bool
    {
        global $DIC;

        $ilAccess = $DIC->access();
        
        $t_arr = explode("_", $target);
        
        if (substr($target, -3) === "wsp") {
            return ilSharedResourceGUI::hasAccess($t_arr[1]);
        }
        
        if ($t_arr[0] !== "blog" || ((int) $t_arr[1]) <= 0) {
            return false;
        }

        // #12648
        if ($ilAccess->checkAccess("read", "", $t_arr[1]) ||
            $ilAccess->checkAccess("visible", "", $t_arr[1])) {
            return true;
        }
        return false;
    }

    public function canBeDelivered(ilWACPath $ilWACPath) : bool
    {
        $ilUser = $this->user;
        $ilAccess = $this->access;
        if (preg_match("/\\/blog_([\\d]*)\\//uim", $ilWACPath->getPath(), $results)) {
            $obj_id = $results[1];
            if ($obj_id == "") {
                return false;
            }
            
            // personal workspace
            $tree = new ilWorkspaceTree(0);
            $node_id = $tree->lookupNodeId((int) $obj_id);
            if ($node_id) {
                $access_handler = new ilWorkspaceAccessHandler($tree);
                if ($access_handler->checkAccessOfUser($tree, $ilUser->getId(), "read", "view", $node_id, "blog")) {
                    return true;
                }
            }
            // repository (RBAC)
            else {
                $ref_ids = ilObject::_getAllReferences((int) $obj_id);
                foreach ($ref_ids as $ref_id) {
                    if ($ilAccess->checkAccessOfUser($ilUser->getId(), "read", "view", $ref_id, "blog", (int) $obj_id)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public static function isCommentsExportPossible(int $blog_id) : bool
    {
        global $DIC;

        $setting = $DIC->settings();
        $notes = $DIC->notes();
        $privacy = ilPrivacySettings::getInstance();
        if ($setting->get("disable_comments")) {
            return false;
        }
        if (!$privacy->enabledCommentsExport()) {
            return false;
        }
        if (!$notes->domain()->commentsActive($blog_id)) {
            return false;
        }
        return true;
    }
}
