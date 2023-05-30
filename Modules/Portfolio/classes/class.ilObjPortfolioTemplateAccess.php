<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjPortfolioTemplateAccess
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilObjPortfolioTemplateAccess extends ilObjectAccess
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

    public static function _getCommands()
    {
        $commands = array(
            array("permission" => "read", "cmd" => "preview", "lang_var" => "preview", "default" => true),
            array("permission" => "write", "cmd" => "view", "lang_var" => "edit_content"),
            array("permission" => "write", "cmd" => "edit", "lang_var" => "settings"),
            array("permission" => "read", "cmd" => "createfromtemplate", "lang_var" => "prtf_create_portfolio_from_template"),
            // array("permission" => "write", "cmd" => "export", "lang_var" => "export_html")
        );
        
        return $commands;
    }
    
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
               case "view":
                    if (!self::_lookupOnline($a_obj_id)
                         && !$rbacsystem->checkAccessOfUser($a_user_id, 'write', $a_ref_id)) {
                        $ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, $lng->txt("offline"));
                        return false;
                    }
                    break;
                    
               // for permission query feature
               case "infoScreen":
                    if (!self::_lookupOnline($a_obj_id)) {
                        $ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, $lng->txt("offline"));
                    } else {
                        $ilAccess->addInfoItem(IL_STATUS_MESSAGE, $lng->txt("online"));
                    }
                    break;

          }
          
        switch ($a_permission) {
               case "read":
               case "visible":
                    if (!self::_lookupOnline($a_obj_id) &&
                         (!$rbacsystem->checkAccessOfUser($a_user_id, 'write', $a_ref_id))) {
                        $ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, $lng->txt("offline"));
                        return false;
                    }
                    break;
          }

        return true;
    }
    
    public static function _lookupOnline($a_id)
    {
        return ilObjPortfolioTemplate::lookupOnline($a_id);
    }

    /**
     * Check wether booking pool is online (legacy version)
     *
     * @deprecated
     */
    public static function _lookupOnlineStatus($a_ids)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $q = "SELECT id, is_online FROM usr_portfolio WHERE " .
            $ilDB->in("id", $a_ids, false, "integer");
        $lm_set = $ilDB->query($q);
        $status = [];
        while ($r = $ilDB->fetchAssoc($lm_set)) {
            $status[$r["id"]] = $r["is_online"];
        }
        return $status;
    }


    /**
    * check whether goto script will succeed
    */
    public static function _checkGoto($a_target)
    {
        global $DIC;

        $ilAccess = $DIC->access();
        
        $t_arr = explode("_", $a_target);
        
        if ($t_arr[0] != "prtt" || ((int) $t_arr[1]) <= 0) {
            return false;
        }
        
        if ($ilAccess->checkAccess("read", "", $t_arr[1]) ||
            $ilAccess->checkAccess("visible", "", $t_arr[1])) {
            return true;
        }
        return false;
    }
}
