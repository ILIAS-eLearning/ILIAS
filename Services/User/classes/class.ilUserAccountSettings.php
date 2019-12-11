<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * @classDescription user account settings
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesUser
 */
class ilUserAccountSettings
{
    private static $instance = null;

    private $storage = null;
    
    private $lua_enabled = true;
    private $lua_access_filter = false;

    /**
     * Singleton constructor
     * @return
     */
    protected function __construct()
    {
        $this->storage = new ilSetting('user_account');
        $this->read();
    }
    
    /**
     * Singelton get instance
     * @return object ilUserAccountSettings
     */
    public static function getInstance()
    {
        if (self::$instance) {
            return self::$instance;
        }
        return self::$instance = new ilUserAccountSettings();
    }
    
    /**
     * Check if local user administration is enabled
     * @return bool
     */
    public function isLocalUserAdministrationEnabled()
    {
        return (bool) $this->lua_enabled;
    }
    
    /**
     * Enable local user administration
     * @param object $a_status
     * @return
     */
    public function enableLocalUserAdministration($a_status)
    {
        $this->lua_enabled = $a_status;
    }
    
    /**
     * Check if user access is restricted
     * @return
     */
    public function isUserAccessRestricted()
    {
        return (bool) $this->lua_access_filter;
    }
    
    /**
     * En/disable user access
     * @param object $a_status
     * @return
     */
    public function restrictUserAccess($a_status)
    {
        $this->lua_access_filter = $a_status;
    }
    
    /**
     * Update settings
     * @return
     */
    public function update()
    {
        $this->storage->set('lua_enabled', $this->isLocalUserAdministrationEnabled());
        $this->storage->set('lua_access_restricted', $this->isUserAccessRestricted());
    }
    
    /**
     * Read user account settings
     * @return
     */
    private function read()
    {
        $this->enableLocalUserAdministration($this->storage->get('lua_enabled', $this->isLocalUserAdministrationEnabled()));
        $this->restrictUserAccess($this->storage->get('lua_access_restricted', $this->isUserAccessRestricted()));
    }
}
