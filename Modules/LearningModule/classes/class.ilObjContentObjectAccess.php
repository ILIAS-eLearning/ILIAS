<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */


include_once("./Services/Object/classes/class.ilObjectAccess.php");

/**
 * Class ilObjContentObjectAccess
 *
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ModulesIliasLearningModule
 */
class ilObjContentObjectAccess extends ilObjectAccess
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

    public static $lo_access;
    
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

        switch ($a_cmd) {
            case "continue":

                // continue is now default and works all the time
                // see ilLMPresentationGUI::resume()
                /*
                if ($ilUser->getId() == ANONYMOUS_USER_ID)
                {
                    $ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, $lng->txt("lm_no_continue_for_anonym"));
                    return false;
                }
                if (ilObjContentObjectAccess::_getLastAccessedPage($a_ref_id,$a_user_id) <= 0)
                {
                    $ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, $lng->txt("not_accessed_yet"));
                    return false;
                }
                */
                break;
                
            // for permission query feature
            case "info":
                if (!ilObject::lookupOfflineStatus($a_obj_id)) {
                    $ilAccess->addInfoItem(IL_STATUS_MESSAGE, $lng->txt("online"));
                }
                break;

        }

        return true;
    }

    //
    // access relevant methods
    //


    /**
    * get last accessed page
    *
    * @param	int		$a_obj_id	content object id
    * @param	int		$a_user_id	user object id
    */
    public static function _getLastAccessedPage($a_ref_id, $a_user_id = "")
    {
        global $DIC;

        $ilDB = $DIC->database();
        $ilUser = $DIC->user();
        
        if ($a_user_id == "") {
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
        
        if ($acc_rec["obj_id"] > 0) {
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

    /**
    * check whether goto script will succeed
    */
    public static function _checkGoto($a_target)
    {
        global $DIC;

        $ilAccess = $DIC->access();
        
        $t_arr = explode("_", $a_target);

        if (($t_arr[0] != "lm" &&  $t_arr[0] != "st"
            &&  $t_arr[0] != "pg")
            || ((int) $t_arr[1]) <= 0) {
            return false;
        }

        if ($t_arr[0] == "lm") {
            if ($ilAccess->checkAccess("read", "", $t_arr[1]) ||
                $ilAccess->checkAccess("visible", "", $t_arr[1])) {
                return true;
            }
        } else {
            if ($t_arr[2] > 0) {
                $ref_ids = array($t_arr[2]);
            } else {
                // determine learning object
                include_once("./Modules/LearningModule/classes/class.ilLMObject.php");
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
    
    /**
     * Preload data
     *
     * @param array $a_obj_ids array of object ids
     */
    public static function _preloadData($a_obj_ids, $a_ref_ids)
    {
        global $DIC;

        $ilDB = $DIC->database();
        $ilUser = $DIC->user();
        
        $q = "SELECT obj_id, lm_id FROM lo_access WHERE " .
            "usr_id = " . $ilDB->quote($ilUser->getId(), "integer") . " AND " .
            $ilDB->in("lm_id", $a_ref_ids, false, "integer");
        ;
        $set = $ilDB->query($q);
        foreach ($a_ref_ids as $r) {
            self::$lo_access[$r] = 0;
        }
        while ($rec = $ilDB->fetchAssoc($set)) {
            self::$lo_access[$rec["lm_id"]] = $rec["obj_id"];
        }
    }
}
