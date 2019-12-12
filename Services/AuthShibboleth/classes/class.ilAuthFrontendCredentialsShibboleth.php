<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Authentication/classes/Frontend/class.ilAuthFrontendCredentials.php';
include_once './Services/Authentication/interfaces/interface.ilAuthCredentials.php';

/**
 * Description of class class
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilAuthFrontendCredentialsShibboleth extends ilAuthFrontendCredentials implements ilAuthCredentials
{
    /**
     * @var ilSetting
     */
    private $settings = null;
    

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        include_once './Services/Administration/classes/class.ilSetting.php';
        $this->settings = $GLOBALS['DIC']['ilSetting'];
    }
    
    
    /**
     * @return \ilSetting
     */
    protected function getSettings()
    {
        return $this->settings;
    }
    
    /**
     * Init credentials from request
     */
    public function initFromRequest()
    {
        //$this->getLogger()->dump($_SERVER, ilLogLevel::DEBUG);
        $this->setUsername($this->settings->get('shib_login', ''));
        $this->setPassword('');
    }
}
