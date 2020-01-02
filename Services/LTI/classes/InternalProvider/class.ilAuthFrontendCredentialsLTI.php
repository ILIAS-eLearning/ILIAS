<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */


include_once './Services/Authentication/classes/Frontend/class.ilAuthFrontendCredentials.php';
include_once './Services/Authentication/interfaces/interface.ilAuthCredentials.php';

/**
 * Auth credentials for lti oauth based authentication
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilAuthFrontendCredentialsLTI extends ilAuthFrontendCredentials implements ilAuthCredentials
{
    public function __construct()
    {
        parent::__construct();
        // overwrite default lti logger
        $this->setLogger($GLOBALS['DIC']->logger()->lti());
    }


    
    /**
     * Init credentials from request
     */
    public function initFromRequest()
    {
        $this->getLogger()->debug('New lti authentication request...');
        $this->getLogger()->dump($_REQUEST, ilLogLevel::DEBUG);
        
        $this->setUsername($_POST['user_id']);
    }
}
