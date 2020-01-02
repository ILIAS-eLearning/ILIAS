<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Object/classes/class.ilObjectAccess.php");
require_once('./Services/WebAccessChecker/interfaces/interface.ilWACCheckingClass.php');

/**
* Class ilObjPollAccess
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id: class.ilObjRootFolderAccess.php 15678 2008-01-06 20:40:55Z akill $
*
*/
class ilObjPollAccess extends ilObjectAccess implements ilWACCheckingClass
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
    * @return	boolean		true, if everything is ok
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
        
        // check "global" online switch
        if (!self::_lookupOnline($a_obj_id) &&
            !$rbacsystem->checkAccessOfUser($a_user_id, 'write', $a_ref_id)) {
            $ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, $lng->txt("offline"));
            return false;
        }
        
        return true;
    }
    
    /**
    * get status
    */
    public static function _lookupOnline($a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $result = $ilDB->query("SELECT * FROM il_poll" .
            " WHERE id = " . $ilDB->quote($a_obj_id, "integer"));
        $row = $ilDB->fetchAssoc($result);
        return $row["online_status"];
    }
    
    /**
     * Is activated?
     *
     * @param int $a_obj_id
     * @param int $a_ref_id
     * @return boolean
     */
    public static function _isActivated($a_ref_id)
    {
        include_once './Services/Object/classes/class.ilObjectActivation.php';
        $item = ilObjectActivation::getItem($a_ref_id);
        switch ($item['timing_type']) {
            case ilObjectActivation::TIMINGS_ACTIVATION:
                if (time() < $item['timing_start'] or
                   time() > $item['timing_end']) {
                    return false;
                }
                // fallthrough
                
                // no break
            default:
                return true;
        }
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
            // array("permission" => "write", "cmd" => "export", "lang_var" => "export")
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
        
        if ($t_arr[0] != "poll" || ((int) $t_arr[1]) <= 0) {
            return false;
        }

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
        $ilAccess = $this->access;
        preg_match("/\\/poll_([\\d]*)\\//uism", $ilWACPath->getPath(), $results);

        foreach (ilObject2::_getAllReferences($results[1]) as $ref_id) {
            if ($ilAccess->checkAccess('read', '', $ref_id)) {
                return true;
            }
        }

        return false;
    }
}
