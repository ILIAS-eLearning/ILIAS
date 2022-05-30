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
 * Class ilUserProfileBadge
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilUserProfileBadge implements ilBadgeType, ilBadgeAuto
{
    public function getId() : string
    {
        return "profile";
    }
    
    public function getCaption() : string
    {
        global $DIC;

        $lng = $DIC['lng'];
        return $lng->txt("badge_user_profile");
    }
    
    public function isSingleton() : bool
    {
        return false;
    }
    
    public function getValidObjectTypes() : array // Missing array type.
    {
        return array("bdga");
    }
    
    public function getConfigGUIInstance() : ?ilBadgeTypeGUI
    {
        return new ilUserProfileBadgeGUI();
    }
    
    public function evaluate(int $a_user_id, array $a_params, array $a_config) : bool // Missing array type.
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];
        
        $user = new ilObjUser($a_user_id);
        
        // active profile portfolio?
        $has_prtf = false;
        if ($ilSetting->get('user_portfolios')) {
            $has_prtf = ilObjPortfolio::getDefaultPortfolio($a_user_id);
        }
        if (!$has_prtf) {
            // is profile public?
            if (!in_array($user->getPref("public_profile"), array("y", "g"))) {
                return false;
            }
        }
        
        // use getter mapping from user profile
        $up = new ilUserProfile();
        $pfields = $up->getStandardFields();
        
        // check for value AND publication status
        
        foreach ($a_config["profile"] as $field) {
            $field = substr($field, 4);
            
            if (substr($field, 0, 4) === "udf_") {
                $udf_field_id = substr($field, 4);
                if ($user->getPref("public_udf_" . $udf_field_id) !== "y") {
                    return false;
                }
                $udf = $user->getUserDefinedData();
                if ($udf["f_" . $udf_field_id] == "") {
                    return false;
                }
            }
            // picture
            else {
                if ($user->getPref("public_" . $field) !== "y") {
                    return false;
                }
                if ($field === "upload") {
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
