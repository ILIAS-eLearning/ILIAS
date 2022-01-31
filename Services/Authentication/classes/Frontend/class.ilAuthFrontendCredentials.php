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
