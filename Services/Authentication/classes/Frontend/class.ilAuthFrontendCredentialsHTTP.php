<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Authentication/classes/Frontend/class.ilAuthFrontendCredentials.php';
include_once './Services/Authentication/interfaces/interface.ilAuthCredentials.php';

/**
 * HTTP auth credentials
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilAuthFrontendCredentialsHTTP extends ilAuthFrontendCredentials implements ilAuthCredentials
{
    private $settings = null;
    

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Init credentials from request
     */
    public function initFromRequest()
    {
        $this->setUsername($_SERVER['PHP_AUTH_USER']);
        $this->setPassword($_SERVER['PHP_AUTH_PW']);
    }
}
