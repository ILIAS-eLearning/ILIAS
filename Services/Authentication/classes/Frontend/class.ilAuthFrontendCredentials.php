<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilAuthFrontendCredentials implements ilAuthCredentials
{
    private ilLogger $logger;

    private string $username = '';
    private string $password = '';
    private string $auth_mode = '';
    
    public function __construct()
    {
        global $DIC;
        $this->logger = $DIC->logger()->auth();
    }

    /**
     * Set username
     */
    public function setUsername(string $a_name) : void
    {
        $this->logger->debug('Username: "' . $a_name . '"');
        $this->username = trim($a_name);
    }

    /**
     * Get username
     */
    public function getUsername() : string
    {
        return $this->username;
    }

    /**
     * Set password
     */
    public function setPassword(string $a_password) : void
    {
        $this->password = $a_password;
    }

    /**
     * Get password
     */
    public function getPassword() : string
    {
        return $this->password;
    }

    /**
     * Set auth mode
     */
    public function setAuthMode(string $a_auth_mode) : void
    {
        $this->auth_mode = $a_auth_mode;
    }
    
    /**
     * Get auth mode
     */
    public function getAuthMode() : string
    {
        return $this->auth_mode;
    }
}
