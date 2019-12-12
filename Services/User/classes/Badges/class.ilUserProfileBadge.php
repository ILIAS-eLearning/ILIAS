<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Badge/interfaces/interface.ilBadgeType.php";
require_once "./Services/Badge/interfaces/interface.ilBadgeAuto.php";

/**
 * Class ilUserProfileBadge
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id:$
 *
 * @package ServicesUser
 */
class ilUserProfileBadge implements ilBadgeType, ilBadgeAuto
{
    public function getId()
    {
        return "profile";
    }
    
    public function getCaption()
    {
        global $DIC;

        $lng = $DIC['lng'];
        return $lng->txt("badge_user_profile");
    }
    
    public function isSingleton()
    {
        return false;
    }
    
    public function getValidObjectTypes()
    {
        return array("bdga");
    }
    
    public function getConfigGUIInstance()
    {
        include_once "Services/User/classes/Badges/class.ilUserProfileBadgeGUI.php";
        return new ilUserProfileBadgeGUI();
    }
    
    public function evaluate($a_user_id, array $a_params, array $a_config)
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];
        
        $user = new ilObjUser($a_user_id);
        
        // active profile portfolio?
        $has_prtf = false;
        if ($ilSetting->get('user_portfolios')) {
            include_once "Modules/Portfolio/classes/class.ilObjPortfolio.php";
            $has_prtf = ilObjPortfolio::getDefaultPortfolio($a_user_id);
        }
        if (!$has_prtf) {
            // is profile public?
            if (!in_array($user->getPref("public_profile"), array("y", "g"))) {
                return false;
            }
        }
        
        // use getter mapping from user profile
        include_once("./Services/User/classes/class.ilUserProfile.php");
        $up = new ilUserProfile();
        $pfields = $up->getStandardFields();
        
        // check for value AND publication status
        
        foreach ($a_config["profile"] as $field) {
            $field = substr($field, 4);
            
            if (substr($field, 0, 4) == "udf_") {
                $udf_field_id = substr($field, 4);
                if ($user->getPref("public_udf_" . $udf_field_id) != "y") {
                    return false;
                }
                $udf = $user->getUserDefinedData();
                if ($udf["f_" . $udf_field_id] == "") {
                    return false;
                }
            }
            // picture
            else {
                if ($user->getPref("public_" . $field) != "y") {
                    return false;
                }
                if ($field == "upload") {
                    if (!ilObjUser::_getPersonalPicturePath($a_user_id, "xsmall", true, true)) {
                        return false;
                    }
                }
                // use profile mapping if possible
                else {
                    if (isset($pfields[$field]["method"])) {
                        $m = $pfields[$field]["method"];
                        if (!$user->{$m}()) {
                            return false;
                        }
                    }
                }
            }
        }
        
        return true;
    }
}
