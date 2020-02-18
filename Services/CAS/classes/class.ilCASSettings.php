<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Description of class
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesCAS
 */
class ilCASSettings
{
    const SYNC_DISABLED = 0;
    const SYNC_CAS = 1;
    const SYNC_LDAP = 2;

    private static $instance = null;

    private $storage = null;
    private $server = '';
    private $port = 0;
    private $uri = '';
    private $login_instructions = '';
    private $active = 0;
    private $create_users = 0;
    private $allow_local = 0;
    private $user_default_role = 0;
    
    /**
     * Singleton constructor
     */
    protected function __construct()
    {
        $this->storage = new ilSetting();
        $this->read();
    }
    
    /**
     * Get singleton instance
     */
    public static function getInstance()
    {
        if (self::$instance) {
            return self::$instance;
        }
        return self::$instance = new ilCASSettings();
    }

    public function setServer($a_server)
    {
        $this->server = $a_server;
    }

    public function getServer()
    {
        return $this->server;
    }

    public function setPort($a_port)
    {
        $this->port = $a_port;
    }

    public function getPort()
    {
        return $this->port;
    }

    public function setUri($a_uri)
    {
        $this->uri = $a_uri;
    }

    public function getUri()
    {
        return $this->uri;
    }

    public function setLoginInstruction($a_inst)
    {
        $this->login_instructions = $a_inst;
    }

    public function getLoginInstruction()
    {
        return $this->login_instructions;
    }

    public function setActive($a_active)
    {
        $this->active = $a_active;
    }

    public function isActive()
    {
        return $this->active;
    }

    public function enableUserCreation($a_uc)
    {
        $this->create_users = $a_uc;
    }

    public function isUserCreationEnabled()
    {
        return $this->create_users;
    }

    public function enableLocalAuthentication($a_local)
    {
        $this->allow_local = $a_local;
    }

    public function isLocalAuthenticationEnabled()
    {
        return $this->allow_local;
    }

    public function setDefaultRole($a_role)
    {
        $this->default_role = $a_role;
    }

    public function getDefaultRole()
    {
        return $this->default_role;
    }

    /**
     * Save settings
     */
    public function save()
    {
        $this->getStorage()->set('cas_server', $this->getServer());
        $this->getStorage()->set('cas_port', $this->getPort());
        $this->getStorage()->set('cas_uri', $this->getUri());
        $this->getStorage()->set('cas_login_instructions', $this->getLoginInstruction());
        $this->getStorage()->set('cas_active', $this->isActive());
        $this->getStorage()->set('cas_create_users', $this->isUserCreationEnabled());
        $this->getStorage()->set('cas_allow_local', $this->isLocalAuthenticationEnabled());
        $this->getStorage()->set('cas_user_default_role', $this->getDefaultRole());
    }

    /**
     * Read settings
     */
    private function read()
    {
        $this->setServer($this->getStorage()->get('cas_server', $this->server));
        $this->setPort($this->getStorage()->get('cas_port', $this->port));
        $this->setUri($this->getStorage()->get('cas_uri', $this->uri));
        $this->setActive($this->getStorage()->get('cas_active', $this->active));
        $this->setDefaultRole($this->getStorage()->get('cas_user_default_role', $this->default_role));
        $this->setLoginInstruction($this->getStorage()->get('cas_login_instructions', $this->login_instructions));
        $this->enableLocalAuthentication($this->getStorage()->get('cas_allow_local', $this->allow_local));
        $this->enableUserCreation($this->getStorage()->get('cas_create_users', $this->create_users));
    }




    /**
     * Get storage object
     * @return ilSetting
     */
    private function getStorage()
    {
        return $this->storage;
    }
}
