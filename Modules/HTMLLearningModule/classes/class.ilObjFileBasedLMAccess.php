<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Object/classes/class.ilObjectAccess.php");

/**
* Class ilFileBasedLMAccess
*
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesHTMLLearningModule
*/
class ilObjFileBasedLMAccess extends ilObjectAccess
{
    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilRbacSystem
     */
    protected $rbacsystem;

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
        $this->lng = $DIC->language();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->access = $DIC->access();
    }

    public static $startfile;

    /**
    * checks wether a user may invoke a command or not
    * (this method is called by ilAccessHandler::checkAccess)
    *
    * @param	string		$a_cmd		command (not permission!)
    * @param	string		$a_permission	permission
    * @param	int			$a_ref_id	reference id
    * @param	int			$a_obj_id	object id
    * @param	int			$a_user_id	user id (if not provided, current user is taken)
    *
    * @return	boolean		true, if everything is ok
    */
    public function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = "")
    {
        $ilUser = $this->user;
        $lng = $this->lng;
        $ilAccess = $this->access;

        if ($a_user_id == "") {
            $a_user_id = $ilUser->getId();
        }

        switch ($a_permission) {
            case "read":

                if (ilObjFileBasedLMAccess::_determineStartUrl($a_obj_id) == "") {
                    $ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, $lng->txt("offline"));
                    return false;
                }
                break;
        }
        return true;
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
            array("permission" => "read", "cmd" => "view", "lang_var" => "show",
                "default" => true),
            array("permission" => "write", "cmd" => "edit", "lang_var" => "edit_content"),
            array("permission" => "write", "cmd" => "properties", "lang_var" => "settings")
        );
        
        return $commands;
    }

    //
    // access relevant methods
    //

    /**
     * determine start url
     */
    public static function _determineStartUrl($a_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        if (isset(self::$startfile[$a_id])) {
            $start_file = self::$startfile[$a_id];
        } else {
            $q = "SELECT startfile FROM file_based_lm WHERE id = " . $ilDB->quote($a_id, "integer");
            $set = $ilDB->query($q);
            $rec = $ilDB->fetchAssoc($set);
            $start_file = $rec["startfile"];
            self::$startfile[$a_id] = $start_file . "";
        }
        
        $dir = ilUtil::getWebspaceDir() . "/lm_data/lm_" . $a_id;
        
        if (($start_file != "") &&
            (@is_file($dir . "/" . $start_file))) {
            return "./" . $dir . "/" . $start_file;
        } elseif (@is_file($dir . "/index.html")) {
            return "./" . $dir . "/index.html";
        } elseif (@is_file($dir . "/index.htm")) {
            return "./" . $dir . "/index.htm";
        }

        return "";
    }

    /**
    * check whether goto script will succeed
    */
    public static function _checkGoto($a_target)
    {
        global $DIC;

        $ilAccess = $DIC->access();
        
        $t_arr = explode("_", $a_target);

        if ($t_arr[0] != "htlm" || ((int) $t_arr[1]) <= 0) {
            return false;
        }

        if ($ilAccess->checkAccess("visible", "", $t_arr[1]) ||
            $ilAccess->checkAccess("read", "", $t_arr[1])) {
            return true;
        }
        return false;
    }

    /**
     * Returns the number of bytes used on the harddisk by the learning module
     * with the specified object id.
     * @param int object id of a file object.
     */
    public static function _lookupDiskUsage($a_id)
    {
        $lm_data_dir = ilUtil::getWebspaceDir('filesystem') . "/lm_data";
        $lm_dir = $lm_data_dir . DIRECTORY_SEPARATOR . "lm_" . $a_id;
        
        return file_exists($lm_dir) ? ilUtil::dirsize($lm_dir) : 0;
    }


    /**
     * Preload data
     *
     * @param array $a_obj_ids array of object ids
     */
    public static function _preloadData($a_obj_ids, $a_ref_ids)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $q = "SELECT id, startfile FROM file_based_lm WHERE " .
            $ilDB->in("id", $a_obj_ids, false, "integer");

        $lm_set = $ilDB->query($q);
        while ($rec = $ilDB->fetchAssoc($lm_set)) {
            self::$startfile[$rec["id"]] = $rec["startfile"] . "";
        }
    }
}
