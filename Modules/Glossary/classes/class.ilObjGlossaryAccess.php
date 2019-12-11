<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */


include_once("./Services/Object/classes/class.ilObjectAccess.php");

/**
* Class ilObjGlossaryAccess
*
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesGlossary
*/
class ilObjGlossaryAccess extends ilObjectAccess
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
    * @return	mixed		true, if everything is ok, message (string) when
    *						access is not granted
    */
    public function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = "")
    {
        $ilUser = $this->user;
        $lng = $this->lng;
        $rbacsystem = $this->rbacsystem;
        $ilAccess = $this->access;

        if ($a_user_id == "") {
            $a_user_id = $ilUser->getId();
        }

        switch ($a_permission) {
            case "read":
                if (!ilObjGlossaryAccess::_lookupOnline($a_obj_id)
                    && !$rbacsystem->checkAccessOfUser($a_user_id, 'write', $a_ref_id)) {
                    $ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, $lng->txt("offline"));
                    return false;
                }
                break;

            case "visible":
                if (!ilObjGlossaryAccess::_lookupOnline($a_obj_id) &&
                    (!$rbacsystem->checkAccessOfUser($a_user_id, 'write', $a_ref_id))) {
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
            array("permission" => "edit_content", "cmd" => "edit", "lang_var" => "edit_content"), // #11099
            array("permission" => "write", "cmd" => "properties", "lang_var" => "settings")
        );
        
        return $commands;
    }

    //
    // access relevant methods
    //

    /**
    * check wether learning module is online
    */
    public static function _lookupOnline($a_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $q = "SELECT * FROM glossary WHERE id = " .
            $ilDB->quote($a_id, "integer");
        $lm_set = $ilDB->query($q);
        $lm_rec = $ilDB->fetchAssoc($lm_set);

        return ilUtil::yn2tf($lm_rec["is_online"]);
    }

    /**
    * check whether goto script will succeed
    */
    public static function _checkGoto($a_target)
    {
        global $DIC;

        $ilAccess = $DIC->access();
        
        $t_arr = explode("_", $a_target);

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
            if ($t_arr[2] > 0) {
                $ref_ids = array($t_arr[2]);
            } else {
                // determine learning object
                require_once("./Modules/Glossary/classes/class.ilGlossaryTerm.php");
                $glo_id = ilGlossaryTerm::_lookGlossaryID($t_arr[1]);
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
