<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Authentication/interfaces/interface.ilAuthCredentials.php';

/**
 * Description of class class
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilAuthFrontendCredentials implements ilAuthCredentials
{
    private $logger = null;
    
    private $username = '';
    private $password = '';
    private $captcha = '';
    private $auth_mode = '';
    
    public function __construct()
    {
        $this->logger = ilLoggerFactory::getLogger('auth');
    }
    
    /**
     * Get logger
     * @return \ilLogger
     */
    public function getLogger()
    {
        return $this->logger;
    }
    
    /**
     * Set Logger
     * @param ilLogger $logger
     */
    public function setLogger(ilLogger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Set username
     * @param string username
     */
    public function setUsername($a_name)
    {
        $this->getLogger()->debug('Username: "' . $a_name . '"');
        $this->username = trim($a_name);
    }

    /**
     * Get username
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password
     * @param string $a_password
     */
    public function setPassword($a_password)
    {
        $this->password = $a_password;
    }

    /**
     * Get password
     */
    public function getPassword()
    {
        return $this->password;
    }

    
    /**
     * Set captcha code
     * @param string
     */
    public function setCaptchaCode($a_code)
    {
        $this->captcha = $a_code;
    }
    
    /**
     * Get captcha code
     */
    public function getCaptchaCode()
    {
        return $this->captcha;
    }
    

    /**
     * Set auth mode
     * @param type $a_auth_mode
     */
    public function setAuthMode($a_auth_mode)
    {
        $this->auth_mode = $a_auth_mode;
    }
    
    /**
     * Get auth mode
     */
    public function getAuthMode()
    {
        return $this->auth_mode;
    }
}
