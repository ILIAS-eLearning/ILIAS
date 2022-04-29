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

    protected $source;
    
    public $login;
    public $email;
    public $firstname;
    public $lastname;
    public $institution;
    public $uid_hash;

    protected string $external_account = '';
    protected string $auth_mode = '';


    /**
     * Constructor
     * @param mixed ilObjUser or encoded json string
     * @access public
     *
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
        } else {
            $this->loadFromJSON();
        }
    }
    
    /**
     * get login
     *
     * @access public
     *
     */
    public function getLogin()
    {
        return $this->login;
    }

    public function getExternalAccount() : string
    {
        return $this->external_account;
    }



    /**
     * get firstname
     *
     * @access public
     *
     */
    public function getFirstname()
    {
        return $this->firstname;
    }
    
    /**
     * getLastname
     *
     * @access public
     */
    public function getLastname()
    {
        return $this->lastname;
    }
    
    /**
     * get email
     *
     * @access public
     */
    public function getEmail()
    {
        return $this->email;
    }
    /**
     * get institution
     *
     * @access public
     *
     */
    public function getInstitution()
    {
        return $this->institution;
    }
    
    /**
     * get Email
     *
     * @access public
     *
     */
    public function getImportId()
    {
        return $this->uid_hash;
    }
    
    /**
     * load from object
     *
     * @access public
     *
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
     * load from json
     *
     * @access public
     *
     */
    public function loadFromJSON() : void
    {
        $this->source = json_decode(urldecode($this->source), false, 512, JSON_THROW_ON_ERROR);
        
        $this->login = $this->source->login();
        $this->firstname = $this->source->firstname();
        $this->lastname = $this->source->lastname();
        $this->email = $this->source->email();
        $this->institution = $this->source->institution();
        
        $this->uid_hash = $this->source->uid_hash;
    }
    
    /**
     * load user data from GET parameters
     *
     * @access public
     *
     */
    public function loadFromGET() : void
    {
        $this->login = ilUtil::stripSlashes(urldecode($_GET['ecs_login']));
        $this->firstname = ilUtil::stripSlashes(urldecode($_GET['ecs_firstname']));
        $this->lastname = ilUtil::stripSlashes(urldecode($_GET['ecs_lastname']));
        $this->email = ilUtil::stripSlashes(urldecode($_GET['ecs_email']));
        $this->institution = ilUtil::stripSlashes(urldecode($_GET['ecs_institution']));
        
        if ($_GET['ecs_uid_hash']) {
            $this->uid_hash = ilUtil::stripSlashes(urldecode($_GET['ecs_uid_hash']));
        } elseif ($_GET['ecs_uid']) {
            $this->uid_hash = ilUtil::stripSlashes(urldecode($_GET['ecs_uid']));
        }
    }

    public function toJSON() : string
    {
        return urlencode(json_encode($this, JSON_THROW_ON_ERROR));
    }
    
    /**
     * get GET parameter string
     *
     * @access public
     *
     */
    public function toGET(ilECSParticipantSetting $setting) : string
    {
        $login = '';
        $external_account_info = '';

        // check for external auth mode
        $external_auth_modes = $setting->getOutgoingExternalAuthModes();
        if (in_array($this->auth_mode, $external_auth_modes)) {
            $placeholder = $setting->getOutgoingUsernamePlaceholderByAuthMode($this->auth_mode);
            if (stristr($placeholder, ilECSParticipantSetting::LOGIN_PLACEHOLDER) !== false) {
                $login = str_replace(
                    ilECSParticipantSetting::LOGIN_PLACEHOLDER,
                    $this->getLogin(),
                    $placeholder
                );
            }
            if (stristr($placeholder, ilECSParticipantSetting::EXTERNAL_ACCOUNT_PLACEHOLDER) !== false) {
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
            '&ecs_firstname=' . urlencode((string) $this->firstname) .
            '&ecs_lastname=' . urlencode((string) $this->lastname) .
            '&ecs_email=' . urlencode((string) $this->email) .
            '&ecs_institution=' . urlencode((string) $this->institution) .
            '&ecs_uid_hash=' . urlencode((string) $this->uid_hash) .
            $external_account_info;
    }
    
    /**
     * Concatenate all attributes to one string
     * @return string
     */
    public function toREALM() : string
    {
        return
            $this->login . '' .
            $this->firstname .
            $this->lastname .
            $this->email .
            $this->institution .
            $this->uid_hash;
    }
}
