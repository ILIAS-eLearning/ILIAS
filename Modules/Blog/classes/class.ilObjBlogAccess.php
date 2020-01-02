<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Object/classes/class.ilObjectAccess.php");
require_once('./Services/WebAccessChecker/interfaces/interface.ilWACCheckingClass.php');

/**
* Class ilObjBlogAccess
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id: class.ilObjRootFolderAccess.php 15678 2008-01-06 20:40:55Z akill $
*
*/
class ilObjBlogAccess extends ilObjectAccess implements ilWACCheckingClass
{
    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilAccessHandler
     */
    protected $access;


    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->user = $DIC->user();
        $this->access = $DIC->access();
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
        $commands = array(
            array("permission" => "read", "cmd" => "preview", "lang_var" => "show", "default" => true),
            array("permission" => "write", "cmd" => "render", "lang_var" => "edit"),
            array("permission" => "contribute", "cmd" => "render", "lang_var" => "edit"),
            array("permission" => "write", "cmd" => "edit", "lang_var" => "settings"),
            array("permission" => "write", "cmd" => "export", "lang_var" => "export_html")
        );
        
        return $commands;
    }
    
    /**
    * check whether goto script will succeed
    */
    public static function _checkGoto($a_target)
    {
        global $DIC;

        $ilAccess = $DIC->access();
        
        $t_arr = explode("_", $a_target);
        
        if (substr($a_target, -3) == "wsp") {
            include_once "Services/PersonalWorkspace/classes/class.ilSharedResourceGUI.php";
            return ilSharedResourceGUI::hasAccess($t_arr[1]);
        }
        
        if ($t_arr[0] != "blog" || ((int) $t_arr[1]) <= 0) {
            return false;
        }

        // #12648
        if ($ilAccess->checkAccess("read", "", $t_arr[1])) {
            return true;
        }
        return false;
    }

    /**
     * @param ilWACPath $ilWACPath
     *
     * @return bool
     */
    public function canBeDelivered(ilWACPath $ilWACPath)
    {
        $ilUser = $this->user;
        $ilAccess = $this->access;
        
        if (preg_match("/\\/blog_([\\d]*)\\//uism", $ilWACPath->getPath(), $results)) {
            $obj_id = $results[1];
            
            // personal workspace
            include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";
            $tree = new ilWorkspaceTree(0);
            $node_id = $tree->lookupNodeId($obj_id);
            if ($node_id) {
                include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceAccessHandler.php";
                $access_handler = new ilWorkspaceAccessHandler($tree);
                if ($access_handler->checkAccessOfUser($tree, $ilUser->getId(), "read", "view", $node_id, "blog")) {
                    return true;
                }
            }
            // repository (RBAC)
            else {
                $ref_ids  = ilObject::_getAllReferences($obj_id);
                foreach ($ref_ids as $ref_id) {
                    if ($ilAccess->checkAccessOfUser($ilUser->getId(), "read", "view", $ref_id, "blog", $obj_id)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
