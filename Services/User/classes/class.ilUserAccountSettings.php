<?php

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
 * @author Alexander Killing <killing@leifos.de>
 */
class ilUserAccountSettings
{
    private static ?ilUserAccountSettings $instance = null;
    private ?ilSetting $storage = null;
    private bool $lua_enabled = true;
    private bool $lua_access_filter = false;

    protected function __construct()
    {
        $this->storage = new ilSetting('user_account');
        $this->read();
    }
    
    public static function getInstance() : self
    {
        if (self::$instance) {
            return self::$instance;
        }
        return self::$instance = new ilUserAccountSettings();
    }
    
    /**
     * Check if local user administration is enabled
     */
    public function isLocalUserAdministrationEnabled() : bool
    {
        return $this->lua_enabled;
    }
    
    /**
     * Enable local user administration
     */
    public function enableLocalUserAdministration(bool $a_status) : void
    {
        $this->lua_enabled = $a_status;
    }
    
    /**
     * Check if user access is restricted
     */
    public function isUserAccessRestricted() : bool
    {
        return $this->lua_access_filter;
    }
    
    /**
     * En/disable user access
     */
    public function restrictUserAccess(bool $a_status) : void
    {
        $this->lua_access_filter = $a_status;
    }
    
    public function update() : void
    {
        $this->storage->set('lua_enabled', $this->isLocalUserAdministrationEnabled());
        $this->storage->set('lua_access_restricted', $this->isUserAccessRestricted());
    }
    
    private function read() : void
    {
        $this->enableLocalUserAdministration($this->storage->get('lua_enabled', $this->isLocalUserAdministrationEnabled()));
        $this->restrictUserAccess($this->storage->get('lua_access_restricted', $this->isUserAccessRestricted()));
    }
}
