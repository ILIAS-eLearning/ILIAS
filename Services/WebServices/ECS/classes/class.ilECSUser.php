<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
* Stores relevant user data.
*
* @author Stefan Meyer <meyer@leifos.com>
*/
class ilECSUser
{
    private ilSetting $setting;

    /** @var  ilObjUser|array */
    private $source;
    
    public string $login;
    public string $email;
    public string $firstname;
    public string $lastname;
    public string $institution;
    public string $uid_hash;

    protected string $external_account = '';
    protected string $auth_mode = '';


    /**
     * @param ilObjUser|array ilObjUser or array containing user info
     */
    public function __construct($a_data)
    {
        global $DIC;

        $this->setting = $DIC->settings();

        $this->source = $a_data;
        if (is_object($a_data)) {
            $this->loadFromObject();
        } elseif (is_array($a_data)) {
            $this->loadFromGET();
        }
    }
    
    /**
     * get login
     */
    public function getLogin() : string
    {
        return $this->login;
    }

    public function getExternalAccount() : string
    {
        return $this->external_account;
    }



    /**
     * get firstname
     */
    public function getFirstname() : string
    {
        return $this->firstname;
    }
    
    /**
     * getLastname
     */
    public function getLastname() : string
    {
        return $this->lastname;
    }
    
    /**
     * get email
     */
    public function getEmail() : string
    {
        return $this->email;
    }
    /**
     * get institution
     */
    public function getInstitution() : string
    {
        return $this->institution;
    }
    
    /**
     * get Email
     */
    public function getImportId() : string
    {
        return $this->uid_hash;
    }
    
    /**
     * load from object
     */
    public function loadFromObject() : void
    {
        $this->login = $this->source->getLogin();
        $this->firstname = $this->source->getFirstname();
        $this->lastname = $this->source->getLastname();
        $this->email = $this->source->getEmail();
        $this->institution = $this->source->getInstitution();
        if ($this->source instanceof ilObjUser) {
            $this->external_account = $this->source->getExternalAccount();
            $this->auth_mode = $this->source->getAuthMode();
        }
        $this->uid_hash = 'il_' . $this->setting->get('inst_id', "0") . '_usr_' . $this->source->getId();
    }
    
    /**
     * load user data from GET parameters
     */
    public function loadFromGET() : void
    {
        //TODO add proper testing for get parameters
        $this->login = ilUtil::stripSlashes(urldecode($this->source['ecs_login']));
        $this->firstname = ilUtil::stripSlashes(urldecode($this->source['ecs_firstname']));
        $this->lastname = ilUtil::stripSlashes(urldecode($this->source['ecs_lastname']));
        $this->email = ilUtil::stripSlashes(urldecode($this->source['ecs_email']));
        $this->institution = ilUtil::stripSlashes(urldecode($this->source['ecs_institution']));
        
        if ($this->source['ecs_uid_hash']) {
            $this->uid_hash = ilUtil::stripSlashes(urldecode($this->source['ecs_uid_hash']));
        } elseif ($this->source['ecs_uid']) {
            $this->uid_hash = ilUtil::stripSlashes(urldecode($this->source['ecs_uid']));
        }
    }

    public function toJSON() : string
    {
        return urlencode(json_encode($this, JSON_THROW_ON_ERROR));
    }
    
    /**
     * get GET parameter string
     */
    public function toGET(ilECSParticipantSetting $setting) : string
    {
        $login = '';
        $external_account_info = '';

        // check for external auth mode
        $external_auth_modes = $setting->getOutgoingExternalAuthModes();
        if (in_array($this->auth_mode, $external_auth_modes)) {
            $placeholder = $setting->getOutgoingUsernamePlaceholderByAuthMode($this->auth_mode);
            if (stripos($placeholder, ilECSParticipantSetting::LOGIN_PLACEHOLDER) !== false) {
                $login = str_replace(
                    ilECSParticipantSetting::LOGIN_PLACEHOLDER,
                    $this->getLogin(),
                    $placeholder
                );
            }
            if (stripos($placeholder, ilECSParticipantSetting::EXTERNAL_ACCOUNT_PLACEHOLDER) !== false) {
                $login = str_replace(
                    ilECSParticipantSetting::EXTERNAL_ACCOUNT_PLACEHOLDER,
                    $this->getExternalAccount(),
                    $placeholder
                );
            }
            $external_account_info = '&ecs_external_account=1';
        } else {
            $login = $this->getLogin();
        }
        return '&ecs_login=' . urlencode((string) $login) .
            '&ecs_firstname=' . urlencode($this->firstname) .
            '&ecs_lastname=' . urlencode($this->lastname) .
            '&ecs_email=' . urlencode($this->email) .
            '&ecs_institution=' . urlencode($this->institution) .
            '&ecs_uid_hash=' . urlencode($this->uid_hash) .
            $external_account_info;
    }
    
    /**
     * Concatenate all attributes to one string
     */
    public function toREALM() : string
    {
        return
            $this->login .
            $this->firstname .
            $this->lastname .
            $this->email .
            $this->institution .
            $this->uid_hash;
    }
}
