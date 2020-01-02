<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Object/classes/class.ilObjectAccess.php");
require_once('./Services/WebAccessChecker/interfaces/interface.ilWACCheckingClass.php');

/**
 * Class ilObjUserAccess
 *
 *
 * @author        Alex Killing <alex.killing@gmx.de>
 * @author        Fabian Schmid <fs@studer-raimann.ch>
 *
 * @version       $Id$
 *
 * @ingroup       ServicesUser
 */
class ilObjUserAccess extends ilObjectAccess implements ilWACCheckingClass
{
    public static function _getCommands()
    {
        die();
    }


    public function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = "")
    {
        die();
    }


    /**
     * check whether goto script will succeed
     */
    public static function _checkGoto($a_target)
    {
        $settings = isset($GLOBALS['DIC']) ? $GLOBALS['DIC']->settings() : $GLOBALS['DIC']['ilSetting'];

        if ('usr_registration' == $a_target) {
            require_once 'Services/Registration/classes/class.ilRegistrationSettings.php';
            $regSeetings = new ilRegistrationSettings();
            if ($regSeetings->getRegistrationType() == IL_REG_DISABLED) {
                $GLOBALS['DIC']->language()->loadLanguageModule('registration');
                ilUtil::sendFailure(sprintf($GLOBALS['DIC']->language()->txt('registration_disabled_no_access'), $settings->get('admin_email')), true);
                return false;
            }
        } elseif ('usr_nameassist' == $a_target) {
            if (!$settings->get('password_assistance')) {
                $GLOBALS['DIC']->language()->loadLanguageModule('pwassist');
                ilUtil::sendFailure(sprintf($GLOBALS['DIC']->language()->txt('unassist_disabled_no_access'), $settings->get('admin_email')), true);
                return false;
            }
        } elseif ('usr_pwassist' == $a_target) {
            if (!$settings->get('password_assistance')) {
                $GLOBALS['DIC']->language()->loadLanguageModule('pwassist');
                ilUtil::sendFailure(sprintf($GLOBALS['DIC']->language()->txt('pwassist_disabled_no_access'), $settings->get('admin_email')), true);
                return false;
            }
        }

        return true;
    }


    /**
     * @param ilWACPath $ilWACPath
     *
     * @return bool
     */
    public function canBeDelivered(ilWACPath $ilWACPath)
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $ilSetting = $DIC['ilSetting'];

        preg_match("/usr_(\\d*).*/ui", $ilWACPath->getFileName(), $matches);
        $usr_id = $matches[1];

        // check if own image is viewed
        if ($usr_id == $ilUser->getId()) {
            return true;
        }

        // check if image is in the public profile
        $public_upload = ilObjUser::_lookupPref($usr_id, 'public_upload');
        if ($public_upload != 'y') {
            return false;
        }

        // check the publication status of the profile
        $public_profile = ilObjUser::_lookupPref($usr_id, 'public_profile');

        if ($public_profile == 'g' and $ilSetting->get('enable_global_profiles') and $ilSetting->get('pub_section')) {
            // globally public
            return true;
        } elseif (($public_profile == 'y' or $public_profile == 'g') and $ilUser->getId() != ANONYMOUS_USER_ID && $ilUser->getId() != 0) {
            // public for logged in users
            return true;
        } else {
            // not public
            return false;
        }
    }
}
