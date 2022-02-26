<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */


/**
 * Class ilObjUserAccess
 * @author        Alex Killing <alex.killing@gmx.de>
 * @author        Fabian Schmid <fs@studer-raimann.ch>
 */
class ilObjUserAccess extends ilObjectAccess implements ilWACCheckingClass
{
    public static function _getCommands()
    {
        throw new ilException("_getCommands must not be called on user object.");
    }

    public function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = "")
    {
        throw new ilException("_checkAccess must not be called on user object.");
    }

    public static function _checkGoto($a_target)
    {
        global $DIC;
        $main_tpl = $DIC->ui()->mainTemplate();
        $settings = isset($GLOBALS['DIC']) ? $GLOBALS['DIC']->settings() : $GLOBALS['DIC']['ilSetting'];

        if ('usr_registration' == $a_target) {
            $regSeetings = new ilRegistrationSettings();
            if ($regSeetings->getRegistrationType() == ilRegistrationSettings::IL_REG_DISABLED) {
                $GLOBALS['DIC']->language()->loadLanguageModule('registration');
                $main_tpl->setOnScreenMessage('failure', sprintf($GLOBALS['DIC']->language()->txt('registration_disabled_no_access'), $settings->get('admin_email')), true);
                return false;
            }
        } elseif ('usr_nameassist' == $a_target) {
            if (!$settings->get('password_assistance')) {
                $GLOBALS['DIC']->language()->loadLanguageModule('pwassist');
                $main_tpl->setOnScreenMessage('failure', sprintf($GLOBALS['DIC']->language()->txt('unassist_disabled_no_access'), $settings->get('admin_email')), true);
                return false;
            }
        } elseif ('usr_pwassist' == $a_target) {
            if (!$settings->get('password_assistance')) {
                $GLOBALS['DIC']->language()->loadLanguageModule('pwassist');
                $main_tpl->setOnScreenMessage('failure', sprintf($GLOBALS['DIC']->language()->txt('pwassist_disabled_no_access'), $settings->get('admin_email')), true);
                return false;
            }
        }

        return true;
    }

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
