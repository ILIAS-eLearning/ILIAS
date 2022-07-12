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
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilCASSettings
{
    public const SYNC_DISABLED = 0;
    public const SYNC_CAS = 1;
    public const SYNC_LDAP = 2;

    private static ?ilCASSettings $instance = null;

    private ilSetting $storage;
    private string $server = '';
    private int $port = 0;
    private string $uri = '';
    private string $login_instructions = '';
    private bool $active = false;
    private bool $create_users = false;
    private bool $allow_local = false;
    private int $user_default_role = 0;
    private int $default_role = 0;
    
    /**
     * Singleton constructor
     */
    protected function __construct()
    {
        global $DIC;

        $this->storage = $DIC->settings();
        $this->read();
    }
    
    /**
     * Get singleton instance
     */
    public static function getInstance() : ilCASSettings
    {
        if (self::$instance) {
            return self::$instance;
        }
        return self::$instance = new ilCASSettings();
    }

    public function setServer(string $a_server) : void
    {
        $this->server = $a_server;
    }

    public function getServer() : string
    {
        return $this->server;
    }

    public function setPort(int $a_port) : void
    {
        $this->port = $a_port;
    }

    public function getPort() : int
    {
        return $this->port;
    }

    public function setUri(string $a_uri) : void
    {
        $this->uri = $a_uri;
    }

    public function getUri() : string
    {
        return $this->uri;
    }

    public function setLoginInstruction(string $a_inst) : void
    {
        $this->login_instructions = $a_inst;
    }

    public function getLoginInstruction() : string
    {
        return $this->login_instructions;
    }

    public function setActive($a_active) : void
    {
        $this->active = $a_active;
    }

    public function isActive() : bool
    {
        return $this->active;
    }

    public function enableUserCreation($a_uc) : void
    {
        $this->create_users = $a_uc;
    }

    public function isUserCreationEnabled() : bool
    {
        return $this->create_users;
    }

    public function enableLocalAuthentication($a_local) : void
    {
        $this->allow_local = $a_local;
    }

    public function isLocalAuthenticationEnabled() : bool
    {
        return $this->allow_local;
    }

    public function setDefaultRole($a_role) : void
    {
        $this->default_role = $a_role;
    }

    public function getDefaultRole() : int
    {
        return $this->default_role;
    }

    public function save() : void
    {
        $this->getStorage()->set('cas_server', $this->getServer());
        $this->getStorage()->set('cas_port', (string) $this->getPort());
        $this->getStorage()->set('cas_uri', $this->getUri());
        $this->getStorage()->set('cas_login_instructions', $this->getLoginInstruction());
        $this->getStorage()->set('cas_active', (string) $this->isActive());
        $this->getStorage()->set('cas_create_users', (string) $this->isUserCreationEnabled());
        $this->getStorage()->set('cas_allow_local', (string) $this->isLocalAuthenticationEnabled());
        $this->getStorage()->set('cas_user_default_role', (string) $this->getDefaultRole());
    }

    private function read() : void
    {
        $this->setServer($this->getStorage()->get('cas_server', $this->server));
        $this->setPort((int) $this->getStorage()->get('cas_port', (string) $this->port));
        $this->setUri($this->getStorage()->get('cas_uri', $this->uri));
        $this->setActive((bool) $this->getStorage()->get('cas_active', (string) $this->active));
        $this->setDefaultRole((int) $this->getStorage()->get('cas_user_default_role', (string) $this->user_default_role));
        $this->setLoginInstruction($this->getStorage()->get('cas_login_instructions', $this->login_instructions));
        $this->enableLocalAuthentication((bool) $this->getStorage()->get('cas_allow_local', (string) $this->allow_local));
        $this->enableUserCreation((bool) $this->getStorage()->get('cas_create_users', (string) $this->create_users));
    }

    private function getStorage() : ilSetting
    {
        return $this->storage;
    }
}
