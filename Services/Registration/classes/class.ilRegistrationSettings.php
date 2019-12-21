<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/


define('IL_REG_DISABLED', 1);
define('IL_REG_DIRECT', 2);
define('IL_REG_APPROVE', 3);
define('IL_REG_ACTIVATION', 4);
define('IL_REG_CODES', 5);

define('IL_REG_ROLES_FIXED', 1);
define('IL_REG_ROLES_EMAIL', 2);

define('IL_REG_ERROR_UNKNOWN', 1);
define('IL_REG_ERROR_NO_PERM', 2);

/**
* Class ilObjAuthSettingsGUI
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ServicesRegistration
*/
class ilRegistrationSettings
{
    const ERR_UNKNOWN_RCP = 1;
    const ERR_MISSING_RCP = 2;
    
    const REG_HASH_LIFETIME_MIN_VALUE = 60;

    private $registration_type;
    private $password_generation_enabled;
    private $access_limitation;
    private $approve_recipient_logins;
    private $approve_recipient_ids;
    private $role_type;
    private $unknown;
    private $reg_hash_life_time = 0;
    private $reg_allow_codes = false;
    private $allowed_domains;
    
    public function __construct()
    {
        $this->__read();
    }

    public function getRegistrationType()
    {
        return $this->registration_type;
    }
    public function setRegistrationType($a_type)
    {
        $this->registration_type = $a_type;
    }

    public static function _lookupRegistrationType()
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];

        $ret = (int) $ilSetting->get('new_registration_type', IL_REG_DISABLED);

        if ($ret < 1 or $ret > 5) {
            //data is corrupted and should be processed like "No Registration possible" (#18261)
            $ret = IL_REG_DISABLED;
        }

        return $ret;
    }

    public function enabled()
    {
        return $this->registration_type != IL_REG_DISABLED;
    }
    public function directEnabled()
    {
        return $this->registration_type == IL_REG_DIRECT;
    }
    public function approveEnabled()
    {
        return $this->registration_type == IL_REG_APPROVE;
    }
    public function activationEnabled()
    {
        return $this->registration_type == IL_REG_ACTIVATION;
    }
    public function registrationCodeRequired()
    {
        return $this->registration_type == IL_REG_CODES;
    }
    
    public function passwordGenerationEnabled()
    {
        return $this->password_generation_enabled;
    }
    public function setPasswordGenerationStatus($a_status)
    {
        $this->password_generation_enabled = $a_status;
    }

    public function getAccessLimitation()
    {
        return $this->access_limitation;
    }

    public function setAccessLimitation($a_access_limitation)
    {
        $this->access_limitation = $a_access_limitation;
    }

    public function setApproveRecipientLogins($a_rec_string)
    {
        $this->approve_recipient_logins = $a_rec_string;
        $this->approve_recipient_ids = array();

        // convert logins to array of ids
        foreach (explode(',', trim($this->approve_recipient_logins)) as $login) {
            if ($uid = ilObjUser::_lookupId(trim($login))) {
                $this->approve_recipient_ids[] = $uid;
            }
        }
    }
    public function getApproveRecipientLogins()
    {
        return $this->approve_recipient_logins;
    }
    public function getApproveRecipients()
    {
        return $this->approve_recipient_ids ? $this->approve_recipient_ids : array();
    }
    public function getUnknown()
    {
        return implode(',', $this->unknown);
    }

    public function roleSelectionEnabled()
    {
        return $this->role_type == IL_REG_ROLES_FIXED;
    }
    public function automaticRoleAssignmentEnabled()
    {
        return $this->role_type == IL_REG_ROLES_EMAIL;
    }
    public function setRoleType($a_type)
    {
        $this->role_type = $a_type;
    }
    
    public function setRegistrationHashLifetime($a_lifetime)
    {
        $this->reg_hash_life_time = $a_lifetime;
        
        return $this;
    }
    
    public function getRegistrationHashLifetime()
    {
        return max($this->reg_hash_life_time, self::REG_HASH_LIFETIME_MIN_VALUE);
    }

    public function setAllowCodes($a_allow_codes)
    {
        $this->reg_allow_codes = (bool) $a_allow_codes;

        return $this;
    }

    public function getAllowCodes()
    {
        return $this->reg_allow_codes;
    }
    
    public function setAllowedDomains($a_value)
    {
        $a_value = explode(";", trim($a_value));
        $this->allowed_domains = $a_value;
    }
    
    public function getAllowedDomains()
    {
        return (array) $this->allowed_domains;
    }
    
    public function validate()
    {
        $this->unknown = array();
        $this->mail_perm = array();

        $login_arr = explode(',', $this->getApproveRecipientLogins());
        $login_arr = $login_arr ? $login_arr : array();
        foreach ($login_arr as $recipient) {
            if (!$recipient = trim($recipient)) {
                continue;
            }
            if (!ilObjUser::_lookupId($recipient)) {
                $this->unknown[] = $recipient;
                continue;
            } else {
                $valid = $recipient;
            }
        }
        if (count($this->unknown)) {
            return self::ERR_UNKNOWN_RCP;
        }
        if ($this->getRegistrationType() == IL_REG_APPROVE and !count((array) $valid)) {
            return self::ERR_MISSING_RCP;
        }
        return 0;
    }

            
    public function save()
    {
        global $DIC;

        $ilias = $DIC['ilias'];

        $ilias->setSetting('reg_role_assignment', $this->role_type);
        $ilias->setSetting('new_registration_type', $this->registration_type);
        $ilias->setSetting('passwd_reg_auto_generate', $this->password_generation_enabled);
        $ilias->setSetting('approve_recipient', addslashes(serialize($this->approve_recipient_ids)));
        $ilias->setSetting('reg_access_limitation', $this->access_limitation);
        $ilias->setSetting('reg_hash_life_time', $this->reg_hash_life_time);
        $ilias->setSetting('reg_allow_codes', $this->reg_allow_codes);
        $ilias->setSetting('reg_allowed_domains', implode(';', $this->allowed_domains));
        
        return true;
    }

    public function __read()
    {
        global $DIC;

        $ilias = $DIC['ilias'];

        //static method validates value
        $this->registration_type = self::_lookupRegistrationType();

        $this->role_type = $ilias->getSetting('reg_role_assignment', 1);
        $this->password_generation_enabled = $ilias->getSetting('passwd_reg_auto_generate');
        $this->access_limitation = $ilias->getSetting('reg_access_limitation');
        $this->reg_hash_life_time = $ilias->getSetting('reg_hash_life_time');
        $this->reg_allow_codes = (bool) $ilias->getSetting('reg_allow_codes');
        
        $this->approve_recipient_ids = unserialize(stripslashes($ilias->getSetting('approve_recipient')));
        $this->approve_recipient_ids = $this->approve_recipient_ids ?
            $this->approve_recipient_ids :
            array();

        // create login array
        $tmp_logins = array();
        foreach ($this->approve_recipient_ids as $id) {
            if ($login = ilObjUser::_lookupLogin($id)) {
                $tmp_logins[] = $login;
            }
        }
        $this->approve_recipient_logins = implode(',', $tmp_logins);

        $this->setAllowedDomains($ilias->getSetting('reg_allowed_domains'));
    }
}
